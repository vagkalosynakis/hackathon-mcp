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

    /**
     * Fetch all rows from an allowed table using a fresh read-only DB connection.
     */
    private function fetchAllFrom(string $table): array|string
    {
        $tableSql = match ($table) {
            'user' => '`user`',
            'course' => '`course`',
            'user_to_certification' => '`user_to_certification`',
            'course_progress' => '`course_progress`',
            'learning_path' => '`learning_path`',
            'skill' => '`skill`',
            default => throw new RuntimeException("Table '{$table}' is not allowed for read access."),
        };

        try {
            $connection = $this->createDoctrineConnection();
            return $connection->fetchAllAssociative("SELECT * FROM {$tableSql}");
        } catch (DbalException|RuntimeException $exception) {
            return 'Error: ' . $exception->getMessage();
        }
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

    #[McpTool(name: 'get_users')]
    public function getUsers(): array|string
    {
        return $this->fetchAllFrom('user');
    }

    #[McpTool(name: 'get_courses')]
    public function getCourses(): array|string
    {
        return $this->fetchAllFrom('course');
    }

    #[McpTool(name: 'get_certification')]
    public function getCertification(): array|string
    {
        return $this->fetchAllFrom('user_to_certification');
    }

    #[McpTool(name: 'get_learner_progress')]
    public function getLearnerProgress(): array|string
    {
        return $this->fetchAllFrom('course_progress');
    }

    #[McpTool(name: 'get_learning_path')]
    public function getLearningPath(): array|string
    {
        return $this->fetchAllFrom('learning_path');
    }

    #[McpTool(name: 'get_skill_content')]
    public function getSkillContent(): array|string
    {
        return $this->fetchAllFrom('skill');
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

