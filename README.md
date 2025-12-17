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

### TalentLMS HTTP tools (read-only)
The server calls the TalentLMS HTTP API using:
- Base URL: `https://plusfe.dev.talentlms.com`
- Headers: `X-API-Version: 2025-01-01`, `X-API-Key: f1TgCRTTNHEz7JrNFDLR2IDj4eUknI`

Implemented so far:
- `get_users(page_number?, page_size?, filter_keyword_like?)` → `{{baseUrl}}/api/v2/users`
  - Explicit params:
    - `page_number` → `page[number]`
    - `page_size` → `page[size]`
    - `filter_keyword_like` → `filter[keyword][like]` (searches across predefined fields)
  - Returns the TalentLMS JSON response including `_links` and `_meta` pagination blocks
- `get_courses()` → `{{baseUrl}}/api/v2/courses`

Exposed (HTTP-only, in-progress wiring):
- `get_certification()`
- `get_learner_progress()`
- `get_learning_path()`
- `get_skill_content()`
- `list_courses()`

Reference for pagination/filtering and examples: `TalentLMS Public API.postman_collection.json` (see “Get all users” request and pagination section).

## Test with MCP Inspector
```bash
npx @modelcontextprotocol/inspector docker compose exec -T -w /app php-mcp php server.php
```
The inspector should show tools `add`, `subtract`, `calculate`, `get_users`, `get_courses`, `get_certification`, `get_learner_progress`, `get_learning_path`, `get_skill_content`, `list_courses`, and resource `config://calculator/settings`. Some TalentLMS tools currently return an informative HTTP-not-implemented message while endpoints are being wired.

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