# Change: Switch TalentLMS MCP tools to HTTP client (initial get_users)

## Why
We need to rely solely on the TalentLMS HTTP API (with required version and API key headers) so MCP tools work consistently in environments where only HTTP access is available.

## What Changes
- Add a shared HTTP client configured with base URL `https://plusfe.dev.talentlms.com` and headers `X-API-Version: 2025-01-01`, `X-API-Key: f1TgCRTTNHEz7JrNFDLR2IDj4eUknI`.
- Update the `get_users` MCP tool to fetch users via `{{baseUrl}}/api/v2/users` and expose explicit MCP arguments for pagination and keyword filtering:
  - `page_number` → `page[number]`
  - `page_size` → `page[size]`
  - `filter_keyword_like` → `filter[keyword][like]` (backend searches across predefined fields)
- Add error handling for HTTP failures/timeouts and surface clear error messages to MCP clients.
- Document the explicit pagination and filtering arguments (mapping to TalentLMS params like `page[number]`, `page[size]`, `filter[keyword][like]`) and update README/project docs to reflect HTTP-only usage.
- Prepare the path for migrating remaining TalentLMS tools to HTTP calls.

## Impact
- Affected specs: talentlms
- Affected code: `server.php` (MCP tools), HTTP client utilities (new), README/env guidance if needed

