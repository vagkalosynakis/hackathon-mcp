# Change: Switch TalentLMS MCP tools to HTTP client (initial get_users)

## Why
We need to rely solely on the TalentLMS HTTP API (with required version and API key headers) so MCP tools work consistently in environments where only HTTP access is available.

## What Changes
- Add a shared HTTP client configured with base URL from `TALENTLMS_BASE_URL` (required) and headers `X-API-Version` from `TALENTLMS_API_VERSION` (required) plus `X-API-Key` sourced from a bearer token.
- Source the TalentLMS bearer token from the environment variable `MCP_BEARER_TOKEN` and throw a clear runtime error when it is missing, e.g.:
  - `private function getApiToken(): string { $token = getenv('MCP_BEARER_TOKEN'); if (!$token) { throw new RuntimeException('Missing MCP_BEARER_TOKEN environment variable.'); } return $token; }`
- Update the `get_users` MCP tool to fetch users via `{{baseUrl}}/api/v2/users` and expose explicit MCP arguments for pagination and keyword filtering:
  - `page_number` → `page[number]`
  - `page_size` → `page[size]`
  - `filter_keyword_like` → `filter[keyword][like]`
- Add error handling for HTTP failures/timeouts and surface clear error messages to MCP clients.
- Document the explicit pagination and filtering arguments (mapping to TalentLMS params like `page[number]`, `page[size]`, `filter[keyword][like]`) and update README/project docs to reflect HTTP-only usage, including a concrete MCP configuration example:
  - Command: `docker`
  - Args: `compose -f /Users/ekalosynakis/projects/hackathon-mcp/docker-compose.yml exec -T -e MCP_BEARER_TOKEN -e TALENTLMS_BASE_URL -e TALENTLMS_API_VERSION -w /app php-mcp php server.php`
- Prepare the path for migrating remaining TalentLMS tools to HTTP calls.

## Impact
- Affected specs: talentlms
- Affected code: `server.php` (MCP tools), HTTP client utilities (new), README/env guidance if needed

