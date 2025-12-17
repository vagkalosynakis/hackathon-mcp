#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mcp\Capability\Attribute\McpResource;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception as DbalException;
use RuntimeException;

class CalculatorElements
{
    private const TALENTLMS_BASE_URL = 'https://plusfe.dev.talentlms.com';
    private const TALENTLMS_API_VERSION = '2025-01-01';
    private const TALENTLMS_API_KEY = 'f1TgCRTTNHEz7JrNFDLR2IDj4eUknI';

    private function buildTalentLmsUrl(string $path): string
    {
        return rtrim(self::TALENTLMS_BASE_URL, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Perform a GET request to the TalentLMS API with required headers.
     */
    private function talentLmsGet(string $path, array|string $queryParams = []): array|string
    {
        $queryString = $this->buildQueryString($queryParams);
        $url = $this->buildTalentLmsUrl($path) . ($queryString !== '' ? '?' . $queryString : '');
        $headers = [
            'Accept: application/json',
            'X-API-Version: ' . self::TALENTLMS_API_VERSION,
            'X-API-Key: ' . self::TALENTLMS_API_KEY,
        ];

        $ch = curl_init($url);
        if ($ch === false) {
            return 'Error: Unable to initialize HTTP client.';
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return 'Error: HTTP request failed - ' . $error;
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode < 200 || $statusCode >= 300) {
            return 'Error: TalentLMS responded with HTTP ' . $statusCode . '.';
        }

        $decoded = json_decode($responseBody, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return 'Error: Failed to decode JSON response - ' . json_last_error_msg();
        }

        return $decoded;
    }

    private function buildQueryString(array|string $queryParams): string
    {
        if (is_string($queryParams)) {
            return trim($queryParams);
        }

        if ($queryParams === []) {
            return '';
        }

        return http_build_query($queryParams);
    }

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
    public function getUsers(
        #[Schema(type: 'number')] int|float|null $pageNumber = null,
        #[Schema(type: 'number')] int|float|null $pageSize = null,
        #[Schema(type: 'string')] string|null $filterKeywordLike = null
    ): array|string {
        $queryParams = [];

        if ($pageNumber !== null) {
            $queryParams['page']['number'] = (int)$pageNumber;
        }
        if ($pageSize !== null) {
            $queryParams['page']['size'] = (int)$pageSize;
        }
        if ($filterKeywordLike !== null && $filterKeywordLike !== '') {
            $queryParams['filter']['keyword']['like'] = $filterKeywordLike;
        }

        return $this->talentLmsGet('api/v2/users', $queryParams);
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

