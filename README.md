# hackathon-mcp

Simple PHP MCP calculator server running inside Docker.

## Prerequisites
- Docker & Docker Compose
- Node.js (only for MCP Inspector testing)
- Optional: Claude Desktop (or any MCP-aware client)

## Quickstart (Docker)
```bash
# From repo root
docker compose up -d

# Install PHP deps inside the container
docker compose exec -T -w /app php-mcp composer install

# Sanity-check the server (runs until stopped)
docker compose exec -T -w /app php-mcp php server.php
```

### Verify the MySQL connection only
Run this to validate env/credentials without launching the MCP server:
```bash
docker compose exec -T -w /app php-mcp php -r "require 'vendor/autoload.php'; require 'server.php'; CalculatorElements::setConnection(createDoctrineConnection()); echo \"MySQL connection OK\\n\";"
```

## Database configuration
- `DB_HOST` (default: `mysql`)
- `DB_PORT` (default: `3306`)
- `DB_NAME` (default: `local_sandbox`)
- `DB_USER` (default: `usr_local_sandbox176580221027`)
- `DB_PASSWORD` (default: `5GyGpkvv@l`)

The MySQL service is expected to be reachable on the external Docker network `talentlms_backend-network` (configurable via `TALENTLMS_NETWORK_NAME`). Startup fails fast with an actionable error if credentials or connectivity are invalid.

## Test with MCP Inspector
```bash
npx @modelcontextprotocol/inspector docker compose exec -T -w /app php-mcp php server.php
```
The inspector should show tools `add`, `calculate`, and resource `config://calculator/settings`.

## Add to an AI tool (e.g., Claude Desktop)
Use an absolute Compose file path so the AI tool can find it even when launched elsewhere.

`~/Library/Application Support/Claude/claude_desktop_config.json`
```json
{
  "mcpServers": {
    "hackathon-mcp": {
      "command": "docker",
      "args": [
        "compose",
        "-f",
        "/Users/ekalosynakis/projects/hackathon-mcp/docker-compose.yml",
        "exec",
        "-T",
        "-w",
        "/app",
        "php-mcp",
        "php",
        "server.php"
      ]
    }
  }
}
```
Then restart/reload your AI client’s MCP servers. If you see “missing compose configuration file,” double-check the absolute `-f` path points to this repo’s `docker-compose.yml`.

## Notes
- The project depends on `mcp/sdk`; Composer config already allows needed plugins.
- Containers mount the repo at `/app` and expose port `8080:80` (from `docker-compose.yml`).