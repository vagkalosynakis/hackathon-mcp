#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mcp\Capability\Attribute\McpResource;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DbalException;
use RuntimeException;

class CalculatorElements
{
    private function buildDbParams(): array
    {
        $host = getenv('DB_HOST') ?: 'mysql';
        $dbName = getenv('DB_NAME') ?: 'local_sandbox';
        $user = getenv('DB_USER') ?: 'usr_local_sandbox176580221027';
        $password = getenv('DB_PASSWORD') ?: '5GyGpkvv@l';
        $portRaw = getenv('DB_PORT') ?: '3306';

        if ($host === '' || $dbName === '' || $user === '' || $password === '') {
            throw new RuntimeException('Database configuration is incomplete (host, name, user, or password missing).');
        }

        if (!ctype_digit($portRaw)) {
            throw new RuntimeException("Invalid DB_PORT value '{$portRaw}'. It must be an integer.");
        }

        $port = (int)$portRaw;
        if ($port <= 0 || $port > 65535) {
            throw new RuntimeException("Invalid DB_PORT value '{$portRaw}'. It must be between 1 and 65535.");
        }

        return [
            'driver' => 'pdo_mysql',
            'host' => $host,
            'port' => $port,
            'dbname' => $dbName,
            'user' => $user,
            'password' => $password,
            'charset' => 'utf8mb4',
        ];
    }

    private function createDoctrineConnection(): Connection
    {
        $params = $this->buildDbParams();
        return DriverManager::getConnection($params);
    }

    #[McpTool]
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    #[McpTool]
    public function subtract(int $a, int $b): int
    {
        return $a - $b;
    }

    #[McpTool(name: 'calculate')]
    public function calculate(float $a, float $b, string $operation): float|string
    {
        return match ($operation) {
            'add' => $a + $b,
            'subtract' => $a - $b,
            'multiply' => $a * $b,
            'divide' => $b != 0 ? $a / $b : 'Error: Division by zero',
            default => 'Error: Unknown operation',
        };
    }

    #[McpResource(
        uri: 'config://calculator/settings',
        name: 'calculator_config',
        mimeType: 'application/json'
    )]
    public function getSettings(): array
    {
        return ['precision' => 2, 'allow_negative' => true];
    }

    #[McpTool(name: 'list_courses')]
    public function listCourses(): array|string
    {
        try {
            $connection = $this->createDoctrineConnection();
            return $connection->fetchAllAssociative('SELECT * FROM course');
        } catch (DbalException|RuntimeException $exception) {
            return 'Error: ' . $exception->getMessage();
        }
    }
}

$server = Server::builder()
    ->setServerInfo('Calculator Server', '1.0.0')
    ->setDiscovery(__DIR__, ['.'])
    ->build();

$transport = new StdioTransport();
$server->run($transport);

