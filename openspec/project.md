# Project Context

## Commands
All commands should be run inside the docker container `php-mcp` not on the host machine

## Purpose
PHP MCP server for a fintech proof-of-concept demo. Exposes financial domain tools (accounts, transactions, payments, etc.) to MCP-aware clients over HTTP (unauthenticated). Performs actions against a dummy backend API. Security is explicitly out of scope — this is a demo only.

## Backend API
- Base URL: `FINTECH_API_BASE_URL` env var (required; no default)
- All requests are unauthenticated — no auth headers required
- Reference: dummy backend for demo purposes; responses are pass-through JSON
- The system may perform read and write operations against the dummy backend

## Tech Stack
- PHP 8.1+ (container image `webdevops/php-nginx:8.4`)
- Composer-managed dependencies
- `mcp/sdk` ^0.1
- Docker & Docker Compose
- HTTP transport (not stdio) for MCP client connections
- Optional: Node.js + `@modelcontextprotocol/inspector` for interactive testing

## Project Conventions

### Code Style
- `declare(strict_types=1);`, typed params/returns, and match expressions for branching
- Prefer PSR-12 formatting; short, single-purpose methods
- Define MCP tools/resources via attributes (`#[McpTool]`, `#[McpResource]`); PascalCase classes and camelCase methods
- Keep server bootstrap minimal: autoload via Composer, avoid framework dependencies

### Architecture Patterns
- Single MCP server built with `Mcp\Server::builder()` using HTTP transport
- Tool classes grouped by fintech domain (accounts, transactions, payments, etc.)
- Container runtime mounts the repo at `/app`; nginx/php image provides PHP-FPM + web server; outbound HTTP used for dummy backend API access

### Testing Strategy
- Manual sanity: `docker compose exec -T -w /app php-mcp php server.php`
- Interactive validation with MCP Inspector: `npx @modelcontextprotocol/inspector` pointed at the HTTP endpoint
- No automated tests; rely on manual MCP client/inspector verification after changes

### Git Workflow
- Short-lived feature branches merged into main via small, focused commits
- Run Composer commands inside the container; keep changes minimal and spec-driven

## Domain Context
Fintech demo exposing MCP tools for common financial operations:
- Account management (list accounts, get balance, account details)
- Transaction history (list transactions, get transaction details)
- Payments (initiate transfers, check payment status)
- Any additional fintech capabilities added during development

All tools call the dummy backend API and return responses as-is to the MCP client.

## Important Constraints
- Requires Docker & Docker Compose; repo must be mounted at `/app` inside `php-mcp`
- PHP >= 8.1 (composer.json); Composer allows `php-http/discovery` plugin
- HTTP transport (not stdio) — MCP clients connect over HTTP
- **No authentication or security requirements** — demo/PoC only
- Compose joins external network `fintech_backend-network` (name via `FINTECH_NETWORK_NAME`); ensure it exists or adjust env

## External Dependencies
- Composer deps: `mcp/sdk` ^0.1 (plus transitive MCP/JSON Schema, PSR, Symfony utilities)
- Container image: `webdevops/php-nginx:8.4`
- Optional tooling: `@modelcontextprotocol/inspector`, Docker Desktop/CLI, Node.js (for inspector)
