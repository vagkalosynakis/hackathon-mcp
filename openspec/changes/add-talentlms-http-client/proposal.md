# Change: Switch TalentLMS MCP tools to HTTP client (initial get_users)

## Why
We need to stop querying the database directly and instead call the TalentLMS HTTP API with the required version and API key headers so MCP tools work in environments without DB access.

## What Changes
- Add a shared HTTP client configured with base URL `https://plusfe.dev.talentlms.com` and headers `X-API-Version: 2025-01-01`, `X-API-Key: f1TgCRTTNHEz7JrNFDLR2IDj4eUknI`.
- Update the `get_users` MCP tool to fetch users via `{{baseUrl}}/api/v2/users` using that client (no DB access).
- Add error handling for HTTP failures/timeouts and surface clear error messages to MCP clients.
- Document pagination and filtering options from `TalentLMS Public API.postman_collection.json` for `get_users` (e.g., `page[number]`, `page[size]`, `filter[login][eq]`) and update README/project docs to remove DB references.
- Prepare the path for migrating remaining TalentLMS tools from DB reads to HTTP calls.

## Impact
- Affected specs: talentlms
- Affected code: `server.php` (MCP tools), HTTP client utilities (new), README/env guidance if needed

