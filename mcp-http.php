<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use Mcp\Server;
use Mcp\Server\Session\FileSessionStore;
use Mcp\Server\Transport\StreamableHttpTransport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

// ── same FintechTools class as server.php ────────────────────────────────────

class FintechTools
{
    private ?\Faker\Generator $fakerInstance = null;

    private function faker(): \Faker\Generator
    {
        if ($this->fakerInstance === null) {
            $this->fakerInstance = \Faker\Factory::create();
        }
        return $this->fakerInstance;
    }

    private function getBaseUrl(): string
    {
        $url = getenv('FINTECH_API_BASE_URL');
        return ($url !== false && trim($url) !== '') ? rtrim($url, '/') : '';
    }

    private function fintechGet(string $path, array $queryParams = []): array
    {
        $baseUrl = $this->getBaseUrl();
        if ($baseUrl === '') {
            return [];
        }

        $url = $baseUrl . '/' . ltrim($path, '/');
        if ($queryParams !== []) {
            $url .= '?' . http_build_query($queryParams);
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return [];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status < 200 || $status >= 300) {
            return [];
        }

        $decoded = json_decode($body, true);
        return (is_array($decoded) && json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
    }

    private function fintechPost(string $path, array $body): array
    {
        $baseUrl = $this->getBaseUrl();
        if ($baseUrl === '') {
            return [];
        }

        $url = $baseUrl . '/' . ltrim($path, '/');
        $json = json_encode($body);

        $ch = curl_init($url);
        if ($ch === false) {
            return [];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
            ],
        ]);

        $responseBody = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false || $status < 200 || $status >= 300) {
            return [];
        }

        $decoded = json_decode($responseBody, true);
        return (is_array($decoded) && json_last_error() === JSON_ERROR_NONE) ? $decoded : [];
    }

    /**
     * Retrieves the current balance for a given bank account.
     *
     * @param string $accountId The unique identifier of the account to query (e.g. "acc_123")
     */
    #[McpTool(name: 'get_balance')]
    public function getBalance(
        #[Schema(type: 'string', description: 'The unique identifier of the account to query (e.g. "acc_123")')]
        string $accountId
    ): array {
        $result = $this->fintechGet('accounts/' . rawurlencode($accountId) . '/balance');

        if ($result !== []) {
            return $result;
        }

        $faker = $this->faker();
        return [
            'accountId' => $accountId,
            'balance'   => $faker->randomFloat(2, 100, 50000),
            'currency'  => $faker->randomElement(['EUR', 'USD', 'GBP', 'CHF']),
            'asOf'      => $faker->dateTimeBetween('-1 minute', 'now')->format('c'),
        ];
    }

    /**
     * Initiates a money transfer from one account to another.
     *
     * @throws ToolCallException When amount is not greater than zero
     */
    #[McpTool(name: 'send_money')]
    public function sendMoney(
        #[Schema(type: 'string', description: 'Source account identifier (e.g. "acc_123")')]
        string $fromAccountId,
        #[Schema(type: 'string', description: 'Destination account identifier (e.g. "acc_456")')]
        string $toAccountId,
        #[Schema(type: 'number', minimum: 0.01, description: 'Amount to transfer; must be greater than zero')]
        float $amount,
        #[Schema(type: 'string', description: 'ISO 4217 currency code (e.g. "EUR", "USD", "GBP")')]
        string $currency,
        #[Schema(type: 'string', description: 'Optional free-text payment reference visible to both parties (e.g. "Rent June")')]
        ?string $reference = null
    ): array {
        if ($amount <= 0) {
            throw new ToolCallException('amount must be greater than zero.');
        }

        $payload = [
            'fromAccountId' => $fromAccountId,
            'toAccountId'   => $toAccountId,
            'amount'        => $amount,
            'currency'      => $currency,
        ];
        if ($reference !== null) {
            $payload['reference'] = $reference;
        }

        $result = $this->fintechPost('transfers', $payload);

        if ($result !== []) {
            return $result;
        }

        $faker = $this->faker();
        return [
            'transferId'    => $faker->uuid(),
            'status'        => $faker->randomElement(['pending', 'completed']),
            'fromAccountId' => $fromAccountId,
            'toAccountId'   => $toAccountId,
            'amount'        => $amount,
            'currency'      => $currency,
            'reference'     => $reference,
            'createdAt'     => $faker->dateTimeBetween('-1 minute', 'now')->format('c'),
        ];
    }

    /**
     * Retrieves a paginated list of past transactions for a specific account.
     *
     * Returns transactions in reverse-chronological order. Supports offset-based
     * pagination and optional date range filtering. Use this tool when the user asks
     * about the history of a specific account, recent payments on an account, or
     * account activity for a known accountId.
     *
     * @param string   $accountId  The account whose transaction history to retrieve (e.g. "a1001")
     * @param int|null $limit      Maximum number of transactions to return (default 20, max 100)
     * @param int|null $offset     Zero-based offset for pagination (default 0)
     * @param string|null $fromDate ISO 8601 date — only transactions on or after this date (e.g. "2024-01-01")
     * @param string|null $toDate   ISO 8601 date — only transactions on or before this date (e.g. "2024-12-31")
     * @return array Paginated result with total, offset, limit, and transactions array
     */
    #[McpTool(name: 'get_account_transactions')]
    public function getAccountTransactions(
        #[Schema(type: 'string', description: 'The account whose transaction history to retrieve (e.g. "a1001")')]
        string $accountId,
        #[Schema(type: 'integer', minimum: 1, maximum: 100, description: 'Maximum number of transactions to return (default 20, max 100)')]
        ?int $limit = 20,
        #[Schema(type: 'integer', minimum: 0, description: 'Zero-based offset for pagination (default 0)')]
        ?int $offset = 0,
        #[Schema(type: 'string', description: 'ISO 8601 date — only return transactions on or after this date (e.g. "2024-01-01")')]
        ?string $fromDate = null,
        #[Schema(type: 'string', description: 'ISO 8601 date — only return transactions on or before this date (e.g. "2024-12-31")')]
        ?string $toDate = null
    ): array {
        $params = [
            'limit'  => $limit ?? 20,
            'offset' => $offset ?? 0,
        ];
        if ($fromDate !== null) {
            $params['fromDate'] = $fromDate;
        }
        if ($toDate !== null) {
            $params['toDate'] = $toDate;
        }

        $result = $this->fintechGet('accounts/' . rawurlencode($accountId) . '/transactions', $params);

        if ($result !== []) {
            return $result;
        }

        $faker = $this->faker();
        $rowCount = min($params['limit'], 10);
        $currencies = ['EUR', 'USD', 'GBP', 'CHF'];
        $descriptions = [
            'debit'  => ['Supermarket purchase', 'Online shopping', 'Restaurant dinner', 'Fuel station', 'Pharmacy', 'Streaming subscription', 'Utility bill', 'Insurance payment', 'Coffee shop', 'Transport ticket'],
            'credit' => ['Salary payment', 'Freelance invoice', 'Refund received', 'Bank interest', 'Transfer received', 'Cashback reward', 'Dividend payment'],
        ];
        $transactions = [];
        for ($i = 0; $i < $rowCount; $i++) {
            $type = $faker->randomElement(['debit', 'credit']);
            $transactions[] = [
                'transactionId' => $faker->uuid(),
                'type'          => $type,
                'amount'        => $faker->randomFloat(2, 1, 5000),
                'currency'      => $faker->randomElement($currencies),
                'description'   => $faker->randomElement($descriptions[$type]),
                'date'          => $faker->dateTimeBetween('-90 days', 'now')->format('Y-m-d'),
            ];
        }
        return [
            'accountId'    => $accountId,
            'total'        => $params['limit'] + $faker->numberBetween(1, 50),
            'offset'       => $params['offset'],
            'limit'        => $params['limit'],
            'transactions' => $transactions,
        ];
    }

    /**
     * Submits a bill payment to a registered biller on behalf of an account.
     *
     * @throws ToolCallException When amount is not greater than zero
     */
    #[McpTool(name: 'pay_bill')]
    public function payBill(
        #[Schema(type: 'string', description: 'The account from which the payment is debited (e.g. "acc_123")')]
        string $accountId,
        #[Schema(type: 'string', description: 'Identifier of the biller to pay (e.g. "biller_electricity_gr")')]
        string $billerId,
        #[Schema(type: 'number', minimum: 0.01, description: 'Payment amount; must be greater than zero')]
        float $amount,
        #[Schema(type: 'string', description: 'ISO 4217 currency code (e.g. "EUR", "USD", "GBP")')]
        string $currency,
        #[Schema(type: 'string', description: 'Optional customer reference or invoice number for the biller (e.g. "INV-2024-05")')]
        ?string $reference = null
    ): array {
        if ($amount <= 0) {
            throw new ToolCallException('amount must be greater than zero.');
        }

        $payload = [
            'accountId' => $accountId,
            'billerId'  => $billerId,
            'amount'    => $amount,
            'currency'  => $currency,
        ];
        if ($reference !== null) {
            $payload['reference'] = $reference;
        }

        $result = $this->fintechPost('bill-payments', $payload);

        if ($result !== []) {
            return $result;
        }

        $faker = $this->faker();
        return [
            'paymentId' => $faker->uuid(),
            'status'    => $faker->randomElement(['pending', 'completed']),
            'accountId' => $accountId,
            'billerId'  => $billerId,
            'amount'    => $amount,
            'currency'  => $currency,
            'reference' => $reference,
            'createdAt' => $faker->dateTimeBetween('-1 minute', 'now')->format('c'),
        ];
    }

    /**
     * Lists bank accounts, optionally filtered by accountId or name.
     *
     * Returns accounts from the fintech backend. Pass no parameters to get all
     * accounts. Pass `accountId` for an exact-match lookup (e.g. "a1001"). Pass
     * `name` for a case-insensitive substring match on the account display name.
     * Returns an empty array when the filter matches nothing.
     *
     * Account IDs follow the a#### format (e.g. "a1001"). Note: bill IDs from
     * get_transactions use b#### (e.g. "b1001"); transaction IDs from
     * get_account_transactions use t#### (e.g. "t0001").
     *
     * Use this tool when the user asks to see their accounts, wants to find a
     * specific account by ID or name, or needs an account ID before calling
     * get_account_transactions or send_money.
     *
     * @param string|null $accountId Exact account ID to look up (e.g. "a1001")
     * @param string|null $name      Case-insensitive substring to match against account name
     * @return array Array of account objects; each has: id (string, e.g. "a1001"),
     *               name (string), accountNumber (IBAN string), balance (float),
     *               currency (ISO 4217 string), lastUpdated (ISO 8601 string)
     */
    #[McpTool(name: 'get_accounts')]
    public function getAccounts(?string $accountId = null, ?string $name = null): array
    {
        $accounts = $this->fintechGet('accounts');

        if ($accounts === []) {
            $accounts = [
                [
                    'id'            => 'a1001',
                    'name'          => 'Main Current Account',
                    'accountNumber' => 'GR16 0110 1250 0000 0001 2300 695',
                    'balance'       => 4821.50,
                    'currency'      => 'EUR',
                    'lastUpdated'   => date('c'),
                ],
                [
                    'id'            => 'a1002',
                    'name'          => 'Savings Account',
                    'accountNumber' => 'GR16 0110 1250 0000 0001 2300 696',
                    'balance'       => 12340.00,
                    'currency'      => 'EUR',
                    'lastUpdated'   => date('c'),
                ],
                [
                    'id'            => 'a1003',
                    'name'          => 'Business Account',
                    'accountNumber' => 'GR16 0110 1250 0000 0001 2300 697',
                    'balance'       => 31500.75,
                    'currency'      => 'EUR',
                    'lastUpdated'   => date('c'),
                ],
            ];
        }

        if ($accountId !== null) {
            return array_values(array_filter($accounts, fn($a) => $a['id'] === $accountId));
        }

        if ($name !== null) {
            return array_values(array_filter($accounts, fn($a) => stripos($a['name'], $name) !== false));
        }

        return $accounts;
    }

    /**
     * Lists all transactions (bills) recorded in the system.
     *
     * Returns the complete list of transactions from the fintech backend. Each
     * transaction represents a bill and includes its unique identifier (format:
     * t####, e.g. "t1001"), the provider/biller name, amount, currency, due date,
     * payment status, category, and structured payment reference (RF code).
     *
     * Use this tool when the user asks to see their bills, pending payments,
     * transaction history, or wants to find a specific bill before paying it.
     *
     * Status values: "paid" or "unpaid".
     * Category values: "electricity", "water", "phone", "internet", "gas".
     *
     * @return array Array of transaction objects; each has: id (string, e.g. "t1001"),
     *               provider (string), amount (float), currency (ISO 4217 string),
     *               dueDate (YYYY-MM-DD string), status ("paid"|"unpaid"),
     *               category (string), rf (string payment reference)
     */
    #[McpTool(name: 'get_transactions')]
    public function getTransactions(): array
    {
        $result = $this->fintechGet('bills');

        if ($result !== []) {
            return $result;
        }

        return [
            [
                'id'       => 't1001',
                'provider' => 'DEH',
                'amount'   => 87.40,
                'currency' => 'EUR',
                'dueDate'  => '2026-06-15',
                'status'   => 'unpaid',
                'category' => 'electricity',
                'rf'       => 'RF12345678901234',
            ],
            [
                'id'       => 't1002',
                'provider' => 'EYDAP',
                'amount'   => 28.60,
                'currency' => 'EUR',
                'dueDate'  => '2026-06-20',
                'status'   => 'unpaid',
                'category' => 'water',
                'rf'       => 'RF98765432109876',
            ],
            [
                'id'       => 't1003',
                'provider' => 'Cosmote',
                'amount'   => 45.00,
                'currency' => 'EUR',
                'dueDate'  => '2026-05-30',
                'status'   => 'paid',
                'category' => 'phone',
                'rf'       => 'RF11223344556677',
            ],
        ];
    }

    /**
     * Lists all contacts stored in the system.
     *
     * Returns the complete list of contacts from the fintech backend. Each contact
     * includes their unique identifier (format: c####, e.g. "c1001"), full name,
     * IBAN account number, and two-letter initials.
     *
     * Use this tool when the user wants to see their contacts, find a recipient for
     * a transfer, or look up an account number before calling send_money.
     *
     * @return array Array of contact objects; each has: id (string, e.g. "c1001"),
     *               name (string), accountNumber (IBAN string), initials (string, 2 chars)
     */
    #[McpTool(name: 'get_contacts')]
    public function getContacts(): array
    {
        $result = $this->fintechGet('contacts');

        if ($result !== []) {
            return $result;
        }

        return [
            [
                'id'            => 'c1001',
                'name'          => 'Nikos Papadopoulos',
                'accountNumber' => 'GR16 0110 1250 0000 0002 1000 001',
                'initials'      => 'NP',
            ],
            [
                'id'            => 'c1002',
                'name'          => 'Maria Georgiou',
                'accountNumber' => 'GR16 0110 1250 0000 0002 1000 002',
                'initials'      => 'MG',
            ],
        ];
    }
}

// ── Build the MCP server (same config as server.php) ─────────────────────────

$cache = new \Symfony\Component\Cache\Psr16Cache(
    new \Symfony\Component\Cache\Adapter\FilesystemAdapter('mcp-discovery', 0, __DIR__ . '/var/cache')
);

$sessionStore = new FileSessionStore(__DIR__ . '/var/sessions', 3600);

$server = Server::builder()
    ->setSession($sessionStore)
    ->setServerInfo('Fintech MCP Server', '1.0.0')
    ->setInstructions(
        'This server provides fintech tools for a banking demo. ' .
        'ID formats: accounts use a#### (e.g. a1001), bills use b#### (e.g. b1001), account transactions use t#### (e.g. t0001), contacts use c#### (e.g. c1001). ' .
        'Available tools: ' .
        'get_accounts(accountId?, name?) — list bank accounts; filter by exact accountId or case-insensitive name substring; returns all when no filter supplied; ' .
        'get_transactions() — list all bills from /bills with id (b####), provider, amount, currency, dueDate, status (paid|unpaid), category, and rf reference; ' .
        'get_contacts() — list all contacts with id, name, IBAN accountNumber, and initials; ' .
        'get_balance(accountId) — retrieve current account balance and currency; ' .
        'get_account_transactions(accountId, limit?, offset?, fromDate?, toDate?) — list paginated transactions (t####) for a specific account from /transactions; ' .
        'send_money(fromAccountId, toAccountId, amount, currency, reference?) — initiate a money transfer between accounts; ' .
        'pay_bill(accountId, billerId, amount, currency, reference?) — submit a bill payment to a registered biller. ' .
        'All tools fall back to demo data when the backend is unavailable.'
    )
    ->setDiscovery(
        basePath: __DIR__,
        scanDirs: ['.'],
        excludeDirs: ['vendor', 'var'],
        cache: $cache
    )
    ->build();

// ── Build a PSR-7 ServerRequest from the current HTTP request ─────────────────

$psr17 = new Psr17Factory();
$creator = new ServerRequestCreator($psr17, $psr17, $psr17, $psr17);
$request = $creator->fromGlobals();

// ── Run through the HTTP transport and emit the PSR-7 response ───────────────

$transport = new StreamableHttpTransport($request, $psr17, $psr17);
$response = $server->run($transport);

// Emit status line + headers
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header("$name: $value", false);
    }
}

// Emit body (supports both regular and streaming/SSE responses)
$body = $response->getBody();
if ($body->isReadable()) {
    // CallbackStream writes directly; regular streams need echo
    $contents = $body->getContents();
    if ($contents !== '') {
        echo $contents;
    } else {
        // SSE / streaming: the body writes itself when cast to string or iterated
        echo (string) $body;
    }
}
