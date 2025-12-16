# Project Context

## Commands
All commands should be run inside the docker container `php-mcp` not on the host machine

## Purpose
Simple PHP MCP calculator server that exposes basic math tools and a settings resource for MCP-aware clients. Optimized for quick local/demo use via Docker Compose and stdio transport.

## Tech Stack
- PHP 8.1+ (container image `webdevops/php-nginx:8.4`)
- Composer-managed dependencies
- `mcp/sdk` ^0.1
- Docker & Docker Compose
- Optional: Node.js + `@modelcontextprotocol/inspector` for interactive testing

## Project Conventions

### Code Style
- `declare(strict_types=1);`, typed params/returns, and match expressions for branching
- Prefer PSR-12 formatting; short, single-purpose methods
- Define MCP tools/resources via attributes (`#[McpTool]`, `#[McpResource]`); PascalCase classes and camelCase methods
- Keep server bootstrap minimal: autoload via Composer, avoid framework dependencies

### Architecture Patterns
- Single MCP server built with `Mcp\Server::builder()` using `StdioTransport`
- `CalculatorElements` hosts all MCP tools/resources; discovery rooted at repo path
- Container runtime mounts the repo at `/app`; nginx/php image provides PHP-FPM + web server (HTTP not used today)

### Testing Strategy
- Manual sanity: `docker compose exec -T -w /app php-mcp php server.php`
- Interactive validation with MCP Inspector: `npx @modelcontextprotocol/inspector docker compose exec -T -w /app php-mcp php server.php` (expect tools `add`, `subtract`, `calculate` and resource `config://calculator/settings`)
- No automated tests yet; rely on manual MCP client/inspector verification after changes

### Git Workflow
- No custom workflow documented; default to short-lived feature branches merged into main via small, focused commits
- Run Composer commands inside the container; keep changes minimal and spec-driven

## Domain Context
- Exposed MCP tools: `add(int,int)`, `subtract(int,int)`, `calculate(float,float,operation)` supporting add/subtract/multiply/divide with simple error strings
- MCP resource: `config://calculator/settings` returns calculator settings JSON (e.g., precision, allow_negative)
- Intended for integration with MCP-aware clients (e.g., Claude Desktop) over stdio

## Important Constraints
- Requires Docker & Docker Compose; repo must be mounted at `/app` inside `php-mcp`
- PHP >= 8.1 (composer.json); Composer allows `php-http/discovery` plugin
- For MCP clients, use absolute `docker-compose.yml` path when configuring the command; service communicates over stdio (not HTTP)
- Compose joins external network `talentlms_backend-network` (name via `TALENTLMS_NETWORK_NAME`); ensure it exists or adjust env

## External Dependencies
- Composer deps: `mcp/sdk` ^0.1 (plus transitive MCP/JSON Schema, PSR, Symfony utilities)
- Container image: `webdevops/php-nginx:8.4`
- Optional tooling: `@modelcontextprotocol/inspector`, Docker Desktop/CLI, Node.js (for inspector)
