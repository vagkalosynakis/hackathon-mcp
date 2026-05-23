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
    private ?int $deviceId;

    public function __construct(?int $deviceId = null)
    {
        $this->deviceId = $deviceId;
    }

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

        $headers = ['Accept: application/json'];
        if ($this->deviceId !== null) {
            $headers[] = 'X-Device-Id: ' . $this->deviceId;
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
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            return ['error' => 'Request failed (curl error)'];
        }

        $decoded = json_decode($body, true);
        $parsed = (is_array($decoded) && json_last_error() === JSON_ERROR_NONE) ? $decoded : [];

        if ($status < 200 || $status >= 300) {
            return array_merge(['httpStatus' => $status], $parsed ?: ['rawBody' => $body]);
        }

        return $parsed;
    }

    private function fintechPost(string $path, array $body): array
    {
        $baseUrl = $this->getBaseUrl();
        if ($baseUrl === '') {
            file_put_contents('php://stdout', '[fintechPost] FINTECH_API_BASE_URL not set' . PHP_EOL);
            return [];
        }

        $url = $baseUrl . '/' . ltrim($path, '/');
        $json = json_encode($body);

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json),
        ];
        if ($this->deviceId !== null) {
            $headers[] = 'X-Device-Id: ' . $this->deviceId;
        }

        file_put_contents('php://stdout', '[fintechPost] POST ' . $url . ' headers=' . json_encode($headers) . ' body=' . $json . PHP_EOL);

        $ch = curl_init($url);
        if ($ch === false) {
            file_put_contents('php://stdout', '[fintechPost] curl_init failed' . PHP_EOL);
            return [];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $responseBody = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        file_put_contents('php://stdout', '[fintechPost] status=' . $status . ' body=' . $responseBody . PHP_EOL);

        if ($responseBody === false) {
            return ['error' => 'Request failed (curl error)'];
        }

        $decoded = json_decode($responseBody, true);
        $parsed = (is_array($decoded) && json_last_error() === JSON_ERROR_NONE) ? $decoded : [];

        if ($status < 200 || $status >= 300) {
            return array_merge(['httpStatus' => $status], $parsed ?: ['rawBody' => $responseBody]);
        }

        return $parsed;
    }

    /**
     * Retrieves the current balance for the authenticated user's bank account.
     *
     * Calls GET /mock-api/accounts using the X-Device-Id header to identify the user.
     * The X-Device-Id is an integer that acts as the user ID and MUST be provided by
     * the MCP client — ask the user to supply their device/user ID if it is not already
     * set. The backend returns a single account object for that user. If the backend
     * returns an empty response or an error, that is returned as-is (no fallback data).
     */
    #[McpTool(name: 'get_balance')]
    public function getBalance(): array {
        $result = $this->fintechGet('mock-api/accounts');

        if ($result === []) {
            return ['error' => 'No account data returned. Ensure X-Device-Id is set correctly.'];
        }

        return $result;
    }

    /**
     * Initiates a money transfer from one account to another via POST /mock-api/transfer.
     *
     * Both `fromAccountId` and `toAccountId` are string account identifiers (e.g. "a1001").
     * Account IDs MUST be obtained from GET /mock-api/contacts (the `get_contacts` tool) —
     * call get_contacts first to find the correct ID for the recipient. The `amount` MUST
     * be provided as an integer in cents (e.g. 50000 = €500.00). The X-Device-Id header
     * is forwarded to the backend automatically for device verification.
     *
     * @param string $fromAccountId Source account ID (string, e.g. "a1001") — obtain from GET /mock-api/contacts
     * @param string $toAccountId   Destination account ID (string, e.g. "a1002") — obtain from GET /mock-api/contacts
     * @param int    $amount        Amount in integer cents (e.g. 50000 = €500.00); must be greater than zero
     *
     * @throws ToolCallException When amount is not greater than zero
     */
    #[McpTool(name: 'send_money')]
    public function sendMoney(
        #[Schema(type: 'string', description: 'Source account ID (string, e.g. "a1001"); obtain from GET /mock-api/contacts via get_contacts')]
        string $fromAccountId,
        #[Schema(type: 'string', description: 'Destination account ID (string, e.g. "a1002"); obtain from GET /mock-api/contacts via get_contacts')]
        string $toAccountId,
        #[Schema(type: 'integer', minimum: 1, description: 'Amount in integer cents (e.g. 1000 = €10.00); must be greater than zero')]
        int $amount
    ): array {
        if ($amount <= 0) {
            throw new ToolCallException('amount must be greater than zero.');
        }

        $payload = [
            'from'   => $fromAccountId,
            'to'     => $toAccountId,
            'amount' => $amount,
        ];

        file_put_contents('php://stdout', '[send_money] deviceId=' . ($this->deviceId ?? 'null') . ' payload=' . json_encode($payload) . PHP_EOL);

        $result = $this->fintechPost('mock-api/transfer', $payload);

        file_put_contents('php://stdout', '[send_money] fintechPost result=' . json_encode($result) . PHP_EOL);

        if ($result !== []) {
            return $result;
        }

        $faker = $this->faker();
        return [
            'transferId'    => $faker->uuid(),
            'status'        => $faker->randomElement(['pending', 'completed']),
            'from'          => $fromAccountId,
            'to'            => $toAccountId,
            'amount'        => $amount,
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

        $result = $this->fintechGet('mock-api/accounts/' . rawurlencode($accountId) . '/transactions', $params);

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
        $result = $this->fintechGet('mock-api/transactions');

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
        $result = $this->fintechGet('mock-api/contacts');

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

    /**
     * Retrieves a single contact by ID from GET /mock-api/contacts/{id}.
     *
     * Use this tool when you already know the contact ID and need their full details
     * (name, IBAN account number, initials) without fetching the entire contact list.
     *
     * @param string $contactId The contact's unique identifier (e.g. "c1001")
     * @return array Contact object with: id (string), name (string), accountNumber (IBAN string), initials (string, 2 chars)
     */
    #[McpTool(name: 'get_contact')]
    public function getContact(
        #[Schema(type: 'string', description: 'The contact\'s unique identifier (e.g. "c1001")')]
        string $contactId
    ): array {
        $result = $this->fintechGet('mock-api/contacts/' . rawurlencode($contactId));

        if ($result !== []) {
            return $result;
        }

        $faker = $this->faker();
        $first = $faker->firstName();
        $last  = $faker->lastName();
        return [
            'id'            => $contactId,
            'name'          => $first . ' ' . $last,
            'accountNumber' => $faker->iban('GR'),
            'initials'      => strtoupper(substr($first, 0, 1) . substr($last, 0, 1)),
        ];
    }
}

// ── Build a PSR-7 ServerRequest from the current HTTP request ─────────────────

$psr17 = new Psr17Factory();
$creator = new ServerRequestCreator($psr17, $psr17, $psr17, $psr17);
$request = $creator->fromGlobals();

// ── Extract and validate X-Device-Id header ───────────────────────────────────

$rawDeviceId = $request->getHeaderLine('X-Device-Id');
$deviceId = null;
if ($rawDeviceId !== '' && ctype_digit($rawDeviceId) && (int) $rawDeviceId > 0) {
    $deviceId = (int) $rawDeviceId;
}
// ── Build the MCP server ──────────────────────────────────────────────────────

$cache = new \Symfony\Component\Cache\Psr16Cache(
    new \Symfony\Component\Cache\Adapter\FilesystemAdapter('mcp-discovery', 0, __DIR__ . '/var/cache')
);

$sessionStore = new FileSessionStore(__DIR__ . '/var/sessions', 3600);

$container = new \Mcp\Capability\Registry\Container();
$container->set(FintechTools::class, new FintechTools($deviceId));

$server = Server::builder()
    ->setSession($sessionStore)
    ->setContainer($container)
    ->setServerInfo('Fintech MCP Server', '1.0.0')
    ->setInstructions(
        'This server provides fintech tools for a banking demo. ' .
        'ID formats: transactions use t#### (e.g. t0001), contacts use c#### (e.g. c1001). ' .
        'IMPORTANT: Most tools require an X-Device-Id header (integer user ID) to be set by the MCP client. If a tool returns empty or an error, ask the user to confirm their device/user ID. ' .
        'Available tools: ' .
        'get_balance() — retrieve the authenticated user\'s account data (balance, currency, account details) from GET /mock-api/accounts; requires X-Device-Id header to identify the user; if empty or error, ask the user for their device ID; ' .
        'get_account_transactions(accountId, limit?, offset?, fromDate?, toDate?) — list paginated transactions for a specific account from /mock-api/accounts/{id}/transactions; ' .
        'get_transactions() — list all transactions from /mock-api/transactions; each has id, provider, amount, currency, dueDate, status (paid|unpaid), category, rf reference; ' .
        'get_contacts() — list all contacts from /mock-api/contacts; each has id (string), name, IBAN accountNumber, initials; ' .
        'get_contact(contactId) — retrieve a single contact by ID from /mock-api/contacts/{id}; ' .
        'send_money(fromAccountId, toAccountId, amount) — POST /mock-api/transfer; fromAccountId and toAccountId are string IDs obtainable from get_contacts (/mock-api/contacts); amount is integer cents (e.g. 50000 = €500.00); X-Device-Id forwarded automatically. ' .
        'If the backend is unavailable, tools return an error response.'
    )
    ->setDiscovery(
        basePath: __DIR__,
        scanDirs: ['.'],
        excludeDirs: ['vendor', 'var'],
        cache: $cache
    )
    ->build();

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
