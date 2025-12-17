#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mcp\Capability\Attribute\McpResource;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;
use RuntimeException;

class CalculatorElements
{

    private function buildTalentLmsUrl(string $path): string
    {
        return $this->getTalentLmsBaseUrl() . '/' . ltrim($path, '/');
    }

    private function getApiToken(): string
    {
        $token = getenv('MCP_BEARER_TOKEN');
        if ($token === false || $token === '') {
            throw new RuntimeException('Missing MCP_BEARER_TOKEN environment variable.');
        }

        return $token;
    }

    private function getTalentLmsBaseUrl(): string
    {
        $baseUrl = getenv('TALENTLMS_BASE_URL');
        if ($baseUrl === false || trim($baseUrl) === '') {
            throw new RuntimeException('Missing TALENTLMS_BASE_URL environment variable.');
        }

        return rtrim($baseUrl, '/');
    }

    private function getTalentLmsApiVersion(): string
    {
        $apiVersion = getenv('TALENTLMS_API_VERSION');
        if ($apiVersion === false || trim($apiVersion) === '') {
            throw new RuntimeException('Missing TALENTLMS_API_VERSION environment variable.');
        }

        return $apiVersion;
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
            'X-API-Version: ' . $this->getTalentLmsApiVersion(),
            'X-API-Key: ' . $this->getApiToken(),
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

    private function buildPageFilterParams(
        int|float|null $pageNumber,
        int|float|null $pageSize,
        ?string $filterKeywordLike
    ): array {
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

        return $queryParams;
    }

    #[McpTool(name: 'get_users')]
    public function getUsers(
        #[Schema(type: 'number')] int|float|null $pageNumber = null,
        #[Schema(type: 'number')] int|float|null $pageSize = null,
        #[Schema(type: 'string')] string|null $filterKeywordLike = null
    ): array|string {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        return $this->talentLmsGet('api/v2/users', $queryParams);
    }

    #[McpTool(name: 'get_courses')]
    public function getCourses(
        #[Schema(type: 'number')] int|float|null $pageNumber = null,
        #[Schema(type: 'number')] int|float|null $pageSize = null,
        #[Schema(type: 'string')] string|null $filterKeywordLike = null,
        #[Schema(type: 'string')] string|null $filterCategoryLike = null
    ): array|string {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        if ($filterCategoryLike !== null && $filterCategoryLike !== '') {
            $queryParams['filter']['category']['like'] = $filterCategoryLike;
        }
        return $this->talentLmsGet('api/v2/courses', $queryParams);
    }

    #[McpTool(name: 'get_groups')]
    public function getGroups(
        #[Schema(type: 'number')] int|float|null $pageNumber = null,
        #[Schema(type: 'number')] int|float|null $pageSize = null,
        #[Schema(type: 'string')] string|null $filterKeywordLike = null
    ): array|string {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        return $this->talentLmsGet('api/v2/groups', $queryParams);
    }

    #[McpTool(name: 'get_branches')]
    public function getBranches(
        #[Schema(type: 'number')] int|float|null $pageNumber = null,
        #[Schema(type: 'number')] int|float|null $pageSize = null,
        #[Schema(type: 'string')] string|null $filterKeywordLike = null
    ): array|string {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        return $this->talentLmsGet('api/v2/branches', $queryParams);
    }

    #[McpTool(name: 'get_categories')]
    public function getCategories(
        #[Schema(type: 'number')] int|float|null $pageNumber = null,
        #[Schema(type: 'number')] int|float|null $pageSize = null,
        #[Schema(type: 'string')] string|null $filterKeywordLike = null
    ): array|string {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        return $this->talentLmsGet('api/v2/categories', $queryParams);
    }

    #[McpTool(name: 'get_units')]
    public function getUnits(
        #[Schema(type: 'string')] string $unitId,
        #[Schema(type: 'number')] int|float|null $pageNumber = null,
        #[Schema(type: 'number')] int|float|null $pageSize = null,
        #[Schema(type: 'string')] string|null $filterKeywordLike = null
    ): array|string {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        $path = 'api/v2/units/' . rawurlencode($unitId) . '/sessions';
        return $this->talentLmsGet($path, $queryParams);
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
        return $this->talentLmsGet('api/v2/courses');
    }
}

$server = Server::builder()
    ->setServerInfo('Calculator Server', '1.0.0')
    ->setDiscovery(__DIR__, ['.'])
    ->build();

$transport = new StdioTransport();
$server->run($transport);

