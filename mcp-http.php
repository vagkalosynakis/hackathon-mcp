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
     * Retrieves a paginated list of past transactions for a given account.
     */
    #[McpTool(name: 'get_transactions')]
    public function getTransactions(
        #[Schema(type: 'string', description: 'The account whose transaction history to retrieve (e.g. "acc_123")')]
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
        'Available tools: ' .
        'get_balance(accountId) — retrieve current account balance and currency; ' .
        'send_money(fromAccountId, toAccountId, amount, currency, reference?) — initiate a money transfer between accounts; ' .
        'get_transactions(accountId, limit?, offset?, fromDate?, toDate?) — list past transactions with optional pagination and date filtering; ' .
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
