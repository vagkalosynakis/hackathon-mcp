#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;

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

    /**
     * Perform an unauthenticated GET request to the fintech backend.
     * Returns an empty array when the backend URL is not configured.
     */
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

    /**
     * Perform an unauthenticated POST request with a JSON body to the fintech backend.
     * Returns an empty array when the backend URL is not configured.
     */
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
     * Returns the available balance, currency (ISO 4217), and the timestamp the
     * balance was last updated. Use this tool whenever the user asks about their
     * account balance, available funds, or how much money they have.
     *
     * @param string $accountId The unique identifier of the account to query (e.g. "acc_123")
     * @return array Account balance details including balance, currency, and asOf timestamp
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

        // DUMMY — returned when backend is unavailable
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
     * Submits a transfer request and returns a confirmation with a transfer ID and
     * status. Use this tool when the user wants to send money, make a transfer, or
     * pay someone directly from their account.
     *
     * @param string $fromAccountId Source account identifier (e.g. "acc_123")
     * @param string $toAccountId   Destination account identifier (e.g. "acc_456")
     * @param float  $amount        Amount to transfer; must be greater than zero
     * @param string $currency      ISO 4217 currency code (e.g. "EUR", "USD", "GBP")
     * @param string|null $reference Optional free-text reference visible to both parties (e.g. "Rent June")
     * @return array Transfer confirmation including transferId, status, and timestamp
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

        // DUMMY — returned when backend is unavailable
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
     *
     * Returns transactions in reverse-chronological order. Supports offset-based
     * pagination and optional date range filtering. Use this tool when the user asks
     * about their transaction history, recent payments, or account activity.
     *
     * @param string   $accountId  The account whose transaction history to retrieve (e.g. "acc_123")
     * @param int|null $limit      Maximum number of transactions to return (default 20, max 100)
     * @param int|null $offset     Zero-based offset for pagination (default 0)
     * @param string|null $fromDate ISO 8601 date — only transactions on or after this date (e.g. "2024-01-01")
     * @param string|null $toDate   ISO 8601 date — only transactions on or before this date (e.g. "2024-12-31")
     * @return array Paginated result with total, offset, limit, and transactions array
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

        // DUMMY — returned when backend is unavailable
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
     * Sends a payment to a biller (e.g. utility company, telecom provider, insurance)
     * and returns a confirmation with a payment ID and status. Use this tool when the
     * user wants to pay a bill, settle an invoice, or make a scheduled payment to a service provider.
     *
     * @param string $accountId  The account from which the payment is debited (e.g. "acc_123")
     * @param string $billerId   Identifier of the biller to pay (e.g. "biller_electricity_gr")
     * @param float  $amount     Payment amount; must be greater than zero
     * @param string $currency   ISO 4217 currency code (e.g. "EUR", "USD", "GBP")
     * @param string|null $reference Optional customer reference or invoice number for the biller (e.g. "INV-2024-05")
     * @return array Payment confirmation including paymentId, status, and timestamp
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

        // DUMMY — returned when backend is unavailable
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

$cache = new \Symfony\Component\Cache\Psr16Cache(
    new \Symfony\Component\Cache\Adapter\FilesystemAdapter('mcp-discovery', 0, __DIR__ . '/var/cache')
);

$server = Server::builder()
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

$transport = new StdioTransport();
$server->run($transport);
