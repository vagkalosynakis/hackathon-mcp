# Tasks: Convert to Fintech MCP

## 1. Clean up LMS artefacts from server.php
- [x] 1.1 Delete the `CalculatorElements` class entirely
- [x] 1.2 Remove all TalentLMS HTTP client methods and env-var helpers
- [x] 1.3 Remove the `config://calculator/settings` MCP resource method

## 2. Implement FintechTools class
- [x] 2.1 Create `FintechTools` class with `declare(strict_types=1)` and full PSR-12 formatting
- [x] 2.2 Add private `fintechGet(string $path, array $queryParams = []): array` helper (unauthenticated curl GET to `FINTECH_API_BASE_URL`)
- [x] 2.3 Add private `fintechPost(string $path, array $body): array` helper (unauthenticated curl POST with JSON body)
- [x] 2.4 Implement `get_balance` tool — accepts `accountId: string`, returns dummy balance payload
- [x] 2.5 Implement `send_money` tool — accepts `fromAccountId: string`, `toAccountId: string`, `amount: float`, `currency: string`, `reference?: string`; returns dummy transfer confirmation
- [x] 2.6 Implement `get_transactions` tool — accepts `accountId: string`, `limit?: int`, `offset?: int`, `fromDate?: string`, `toDate?: string`; returns dummy paginated transaction list
- [x] 2.7 Implement `pay_bill` tool — accepts `accountId: string`, `billerId: string`, `amount: float`, `currency: string`, `reference?: string`; returns dummy payment confirmation

## 3. Update server.php bootstrap
- [x] 3.1 Change server name to `Fintech MCP Server` and bump version to `1.0.0`
- [x] 3.2 Update `setInstructions()` to describe the four fintech tools
- [x] 3.3 Ensure discovery picks up `FintechTools` (same basePath/scanDirs pattern)
- [x] 3.4 Keep `StdioTransport` for now (HTTP transport is a separate change: `update-mcp-http-transport`)

## 4. Update supporting files
- [x] 4.1 Update `docker-compose.yml`: add `FINTECH_API_BASE_URL` env var, remove TalentLMS env vars
- [x] 4.2 Update `index.php`: replace the hello-world stub with a minimal status response identifying the server as `Fintech MCP`
- [x] 4.3 Confirm with user before deleting `TalentLMS Public API.postman_collection.json`, `database.md`, and `product.md` — all three deleted with user confirmation

## 5. Validate
- [x] 5.1 Run `docker compose exec -T -w /app php-mcp php server.php` and confirm it starts without errors
- [x] 5.2 Verify all four tools appear in MCP Inspector output with correct parameter schemas
- [x] 5.3 Confirm no TalentLMS or calculator references remain in `server.php`
