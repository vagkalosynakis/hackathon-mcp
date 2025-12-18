#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Mcp\Capability\Attribute\McpResource;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Mcp\Exception\ToolCallException;
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
     *
     * @throws ToolCallException When the HTTP request fails or returns an error
     */
    private function talentLmsGet(string $path, array|string $queryParams = []): array
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
            throw new ToolCallException('Unable to initialize HTTP client.');
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
            throw new ToolCallException('HTTP request failed: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new ToolCallException('TalentLMS API returned HTTP ' . $statusCode . '. Please check your API credentials and base URL.');
        }

        $decoded = json_decode($responseBody, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new ToolCallException('Failed to decode JSON response: ' . json_last_error_msg());
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

    /**
     * Adds two numbers together.
     *
     * @param int $a The first number
     * @param int $b The second number
     * @return int The sum of a and b
     */
    #[McpTool]
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    /**
     * Subtracts the second number from the first.
     *
     * @param int $a The number to subtract from
     * @param int $b The number to subtract
     * @return int The difference between a and b
     */
    #[McpTool]
    public function subtract(int $a, int $b): int
    {
        return $a - $b;
    }

    /**
     * Performs basic arithmetic operations on two numbers.
     * Supports addition, subtraction, multiplication, and division.
     *
     * @param float $a The first operand
     * @param float $b The second operand
     * @param string $operation The operation to perform (add, subtract, multiply, divide)
     * @return float The result of the calculation
     * @throws ToolCallException When division by zero or unknown operation
     */
    #[McpTool(name: 'calculate')]
    public function calculate(float $a, float $b, string $operation): float
    {
        return match ($operation) {
            'add' => $a + $b,
            'subtract' => $a - $b,
            'multiply' => $a * $b,
            'divide' => $b != 0 ? $a / $b : throw new ToolCallException('Division by zero is not allowed'),
            default => throw new ToolCallException('Unknown operation: ' . $operation . '. Valid operations are: add, subtract, multiply, divide'),
        };
    }

    private function buildPageFilterParams(
        ?int $pageNumber,
        ?int $pageSize,
        ?string $filterKeywordLike
    ): array {
        $queryParams = [];

        if ($pageNumber !== null) {
            $queryParams['page']['number'] = $pageNumber;
        }
        if ($pageSize !== null) {
            $queryParams['page']['size'] = $pageSize;
        }
        if ($filterKeywordLike !== null && $filterKeywordLike !== '') {
            $queryParams['filter']['keyword']['like'] = $filterKeywordLike;
        }

        return $queryParams;
    }

    /**
     * Retrieves TalentLMS users with pagination and keyword filtering.
     * Supports searching across user fields including name, email, and branch.
     * Returns user metadata such as last login, role, and associated branch information.
     *
     * @param int|null $pageNumber Page number to retrieve (starts from 1)
     * @param int|null $pageSize Number of items per page (max 100)
     * @param string|null $filterKeywordLike Keyword to filter users by name, email, or other fields
     * @return array TalentLMS users data with pagination metadata
     * @throws ToolCallException When the API request fails
     */
    #[McpTool(name: 'get_users')]
    public function getUsers(
        #[Schema(type: 'integer', minimum: 1, description: 'Page number to retrieve')]
        ?int $pageNumber = null,
        #[Schema(type: 'integer', minimum: 1, maximum: 100, description: 'Number of items per page')]
        ?int $pageSize = null,
        #[Schema(type: 'string', description: 'Keyword to filter users by name, email, or other fields')]
        ?string $filterKeywordLike = null
    ): array {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        return $this->talentLmsGet('api/v2/users', $queryParams);
    }

    /**
     * Retrieves TalentLMS courses with pagination, keyword, and category filtering.
     * Returns course metadata including name, description, category, and enrollment information.
     * Useful for building learning paths and identifying available training content.
     *
     * @param int|null $pageNumber Page number to retrieve (starts from 1)
     * @param int|null $pageSize Number of items per page (max 100)
     * @param string|null $filterKeywordLike Keyword to filter courses by name or description
     * @param string|null $filterCategoryLike Category name to filter courses by
     * @return array TalentLMS courses data with pagination metadata
     * @throws ToolCallException When the API request fails
     */
    #[McpTool(name: 'get_courses')]
    public function getCourses(
        #[Schema(type: 'integer', minimum: 1, description: 'Page number to retrieve')]
        ?int $pageNumber = null,
        #[Schema(type: 'integer', minimum: 1, maximum: 100, description: 'Number of items per page')]
        ?int $pageSize = null,
        #[Schema(type: 'string', description: 'Keyword to filter courses by name or description')]
        ?string $filterKeywordLike = null,
        #[Schema(type: 'string', description: 'Category name to filter courses by')]
        ?string $filterCategoryLike = null
    ): array {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        if ($filterCategoryLike !== null && $filterCategoryLike !== '') {
            $queryParams['filter']['category']['like'] = $filterCategoryLike;
        }
        return $this->talentLmsGet('api/v2/courses', $queryParams);
    }

    /**
     * Retrieves TalentLMS user groups with pagination and keyword filtering.
     * Groups help organize users into teams or cohorts for training management.
     * Returns group metadata including name, description, and member count.
     *
     * @param int|null $pageNumber Page number to retrieve (starts from 1)
     * @param int|null $pageSize Number of items per page (max 100)
     * @param string|null $filterKeywordLike Keyword to filter groups by name
     * @return array TalentLMS groups data with pagination metadata
     * @throws ToolCallException When the API request fails
     */
    #[McpTool(name: 'get_groups')]
    public function getGroups(
        #[Schema(type: 'integer', minimum: 1, description: 'Page number to retrieve')]
        ?int $pageNumber = null,
        #[Schema(type: 'integer', minimum: 1, maximum: 100, description: 'Number of items per page')]
        ?int $pageSize = null,
        #[Schema(type: 'string', description: 'Keyword to filter groups by name')]
        ?string $filterKeywordLike = null
    ): array {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        return $this->talentLmsGet('api/v2/groups', $queryParams);
    }

    /**
     * Retrieves TalentLMS branches with pagination and keyword filtering.
     * Branches represent different departments, locations, or organizational units.
     * Returns branch metadata including name and associated user information.
     *
     * @param int|null $pageNumber Page number to retrieve (starts from 1)
     * @param int|null $pageSize Number of items per page (max 100)
     * @param string|null $filterKeywordLike Keyword to filter branches by name
     * @return array TalentLMS branches data with pagination metadata
     * @throws ToolCallException When the API request fails
     */
    #[McpTool(name: 'get_branches')]
    public function getBranches(
        #[Schema(type: 'integer', minimum: 1, description: 'Page number to retrieve')]
        ?int $pageNumber = null,
        #[Schema(type: 'integer', minimum: 1, maximum: 100, description: 'Number of items per page')]
        ?int $pageSize = null,
        #[Schema(type: 'string', description: 'Keyword to filter branches by name')]
        ?string $filterKeywordLike = null
    ): array {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        return $this->talentLmsGet('api/v2/branches', $queryParams);
    }

    /**
     * Retrieves TalentLMS course categories with pagination and keyword filtering.
     * Categories help organize courses by subject, department, or learning path.
     * Returns category metadata including name and associated course count.
     *
     * @param int|null $pageNumber Page number to retrieve (starts from 1)
     * @param int|null $pageSize Number of items per page (max 100)
     * @param string|null $filterKeywordLike Keyword to filter categories by name
     * @return array TalentLMS categories data with pagination metadata
     * @throws ToolCallException When the API request fails
     */
    #[McpTool(name: 'get_categories')]
    public function getCategories(
        #[Schema(type: 'integer', minimum: 1, description: 'Page number to retrieve')]
        ?int $pageNumber = null,
        #[Schema(type: 'integer', minimum: 1, maximum: 100, description: 'Number of items per page')]
        ?int $pageSize = null,
        #[Schema(type: 'string', description: 'Keyword to filter categories by name')]
        ?string $filterKeywordLike = null
    ): array {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        return $this->talentLmsGet('api/v2/categories', $queryParams);
    }

    /**
     * Retrieves sessions for a specific TalentLMS unit with pagination and keyword filtering.
     * Units are individual lessons or modules within a course.
     * Returns session data including completion status and user progress.
     *
     * @param string $unitId The unique identifier of the unit
     * @param int|null $pageNumber Page number to retrieve (starts from 1)
     * @param int|null $pageSize Number of items per page (max 100)
     * @param string|null $filterKeywordLike Keyword to filter sessions
     * @return array TalentLMS unit sessions data with pagination metadata
     * @throws ToolCallException When the API request fails
     */
    #[McpTool(name: 'get_units')]
    public function getUnits(
        #[Schema(type: 'string', description: 'The unique identifier of the unit')]
        string $unitId,
        #[Schema(type: 'integer', minimum: 1, description: 'Page number to retrieve')]
        ?int $pageNumber = null,
        #[Schema(type: 'integer', minimum: 1, maximum: 100, description: 'Number of items per page')]
        ?int $pageSize = null,
        #[Schema(type: 'string', description: 'Keyword to filter sessions')]
        ?string $filterKeywordLike = null
    ): array {
        $queryParams = $this->buildPageFilterParams($pageNumber, $pageSize, $filterKeywordLike);
        $path = 'api/v2/units/' . rawurlencode($unitId) . '/sessions';
        return $this->talentLmsGet($path, $queryParams);
    }

    /**
     * Provides calculator configuration settings.
     * Returns precision settings and operational parameters for calculator tools.
     *
     * @return array Calculator configuration including precision and negative number handling
     */
    #[McpResource(
        uri: 'config://calculator/settings',
        name: 'calculator_config',
        description: 'Calculator configuration settings',
        mimeType: 'application/json'
    )]
    public function getSettings(): array
    {
        return ['precision' => 2, 'allow_negative' => true];
    }

    /**
     * Retrieves all TalentLMS courses without filtering or pagination.
     * Returns a complete list of available courses.
     * For filtered or paginated results, use get_courses instead.
     *
     * @return array TalentLMS courses data
     * @throws ToolCallException When the API request fails
     */
    #[McpTool(name: 'list_courses')]
    public function listCourses(): array
    {
        return $this->talentLmsGet('api/v2/courses');
    }
}

// Setup discovery caching for production performance
$cache = new \Symfony\Component\Cache\Psr16Cache(
    new \Symfony\Component\Cache\Adapter\FilesystemAdapter('mcp-discovery', 0, __DIR__ . '/var/cache')
);

$server = Server::builder()
    ->setServerInfo('TalentLMS MCP Server', '1.0.0')
    ->setInstructions('This server provides read-only access to TalentLMS data. Use get_users, get_courses, get_groups, get_branches, get_categories, and get_units tools to query TalentLMS API data with pagination and filtering support.')
    ->setDiscovery(
        basePath: __DIR__,
        scanDirs: ['.'],
        excludeDirs: ['vendor', 'var'],
        cache: $cache
    )
    ->build();

$transport = new StdioTransport();
$server->run($transport);

