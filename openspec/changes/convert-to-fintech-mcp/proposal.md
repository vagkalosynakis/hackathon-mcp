# Change: Convert MCP server from LMS context to Fintech context

## Why
The project has pivoted from a TalentLMS integration to a fintech proof-of-concept demo. The existing codebase contains only LMS and calculator tools — all of which must be replaced with fintech-domain tools that an AI client can use to interact with a dummy financial backend.

## What Changes
- **BREAKING** Remove all TalentLMS-specific tools (`get_users`, `get_courses`, `get_groups`, `get_branches`, `get_categories`, `get_units`, `list_courses`) and the calculator tools (`add`, `subtract`, `calculate`)
- **BREAKING** Remove all TalentLMS HTTP client helpers (`talentLmsGet`, `buildTalentLmsUrl`, `getApiToken`, `getTalentLmsBaseUrl`, `getTalentLmsApiVersion`) and their env-var dependencies (`MCP_BEARER_TOKEN`, `TALENTLMS_BASE_URL`, `TALENTLMS_API_VERSION`)
- **BREAKING** Remove the `config://calculator/settings` MCP resource
- Replace the single `CalculatorElements` class with a `FintechTools` class exposing four fintech MCP tools
- Add a generic unauthenticated HTTP client helper that calls `FINTECH_API_BASE_URL`
- Update `server.php` bootstrap: server name/version, instructions, and (future) transport to reflect the fintech context
- Retire `TalentLMS Public API.postman_collection.json`, `database.md`, and `product.md` as they are LMS artefacts (tracked in tasks — do not delete without confirmation)

## Fintech Tools Exposed
| Tool | Description |
|---|---|
| `get_balance` | Returns the current balance for a given account |
| `send_money` | Initiates a money transfer between two accounts |
| `get_transactions` | Lists past transactions for an account with optional pagination and date filters |
| `pay_bill` | Submits a bill payment for a given biller and amount |

All tools use fully typed PHP 8.1+ signatures, rich `#[Schema]` annotations, and clear docblocks so AI clients can auto-discover their purpose and parameters without additional documentation.

## Impact
- Affected specs: `fintech-tools` (new), `mcp-server-bootstrap` (new)
- Affected code: `server.php` (full rewrite), `index.php` (minor), `docker-compose.yml` (env vars), `composer.json` (no changes needed)
- LMS artefacts to retire: `TalentLMS Public API.postman_collection.json`, `database.md`, `product.md` (confirm before deleting)
