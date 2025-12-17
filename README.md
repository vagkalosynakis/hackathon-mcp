# hackathon-mcp

TalentLMS MCP server - A Model Context Protocol server providing read-only access to TalentLMS data for AI assistants. Built with PHP and following MCP best practices for production-ready deployments.

## Prerequisites
- Docker & Docker Compose
- Node.js (only for MCP Inspector testing)
- Optional: Claude Desktop (or any MCP-aware client)

## Features

- **Read-only TalentLMS API Access**: Query users, courses, groups, branches, categories, and units
- **Pagination Support**: Efficiently handle large datasets with configurable page sizes
- **Keyword & Category Filtering**: Search and filter results across all endpoints
- **Production-Ready Error Handling**: Uses MCP `ToolCallException` for AI-friendly error messages
- **Discovery Caching**: Fast server startup with filesystem-based cache
- **Full Schema Validation**: Parameter constraints with min/max values and type checking
- **Comprehensive Documentation**: Detailed docblocks for all tools and resources

## Quickstart (Docker)

```bash
# From repo root
docker compose up -d

# Install PHP dependencies inside the container
docker compose exec -T -w /app php-mcp composer install

# Create cache directory for discovery performance
mkdir -p var/cache

# Sanity-check the server (runs until stopped)
docker compose exec -T -w /app php-mcp php server.php
```

## Available MCP Tools

All tools support pagination and keyword filtering where applicable. Parameters use JSON Schema validation with min/max constraints.

### User Management
- **`get_users(pageNumber?, pageSize?, filterKeywordLike?)`** - Retrieves TalentLMS users with metadata (last login, role, branch)
  - `pageNumber` (integer, min: 1): Page to retrieve
  - `pageSize` (integer, min: 1, max: 100): Items per page
  - `filterKeywordLike` (string): Filter by name, email, or other fields

### Course Management
- **`get_courses(pageNumber?, pageSize?, filterKeywordLike?, filterCategoryLike?)`** - Retrieves courses with enrollment information
  - Supports both keyword and category filtering
  - Returns course metadata for learning path planning

- **`list_courses()`** - Simplified endpoint returning all courses without pagination

### Organizational Structure
- **`get_groups(pageNumber?, pageSize?, filterKeywordLike?)`** - User groups/teams for training management
- **`get_branches(pageNumber?, pageSize?, filterKeywordLike?)`** - Departments/locations/organizational units
- **`get_categories(pageNumber?, pageSize?, filterKeywordLike?)`** - Course categories by subject or learning path

### Course Content
- **`get_units(unitId, pageNumber?, pageSize?, filterKeywordLike?)`** - Sessions for a specific course unit
  - `unitId` (string, required): Unique identifier of the unit
  - Returns session data with completion status and progress

### Resources
- **`config://calculator/settings`** - Calculator configuration (demo resource)

## TalentLMS API Configuration

The server requires three environment variables for TalentLMS API access:

- `MCP_BEARER_TOKEN`: TalentLMS API key (used in `X-API-Key` header)
- `TALENTLMS_BASE_URL`: TalentLMS instance base URL
- `TALENTLMS_API_VERSION`: API version header value (e.g., `2025-01-01`)

## Test with MCP Inspector

```bash
npx @modelcontextprotocol/inspector docker compose exec -T -w /app php-mcp php server.php
```

The inspector will show all available tools with their full documentation:
- Math tools: `add`, `subtract`, `calculate`
- TalentLMS tools: `get_users`, `get_courses`, `get_groups`, `get_branches`, `get_categories`, `get_units`, `list_courses`
- Resources: `config://calculator/settings`

All tools include:
- Detailed descriptions from docblocks
- Parameter validation with JSON Schema
- Type constraints and ranges
- AI-friendly error messages

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
        "-e",
        "MCP_BEARER_TOKEN",
        "-e",
        "TALENTLMS_BASE_URL",
        "-e",
        "TALENTLMS_API_VERSION",
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

## Architecture & Performance

### Production Optimizations
- **Discovery Caching**: MCP element discovery results are cached in `var/cache/` for faster server startup
- **Type Safety**: Strict PHP type hints with schema validation
- **Error Handling**: All exceptions use MCP-specific exception types for better AI integration
- **Read-Only Design**: No write operations possible, ensuring data safety

### Dependencies
- `mcp/sdk` (^0.1): Official PHP MCP SDK
- `symfony/cache` (^8.0): Discovery caching for production performance
- `psr/simple-cache` (^3.0): PSR-16 cache interface (required by symfony/cache)
- PHP 8.1+ with cURL support

### Directory Structure
```
hackathon-mcp/
├── server.php              # Main MCP server implementation
├── composer.json           # PHP dependencies
├── docker-compose.yml      # Docker configuration
├── var/cache/             # Discovery cache (gitignored)
└── vendor/                # Composer dependencies (gitignored)
```

## Troubleshooting

**Missing environment variables**: Ensure all three env vars are set before starting the server:
```bash
export MCP_BEARER_TOKEN="your-api-key"
export TALENTLMS_BASE_URL="https://your-instance.talentlms.com"
export TALENTLMS_API_VERSION="2025-01-01"
```

**Clear discovery cache**: If tools aren't appearing after code changes:
```bash
rm -rf var/cache/*
```

**API authentication errors**: Verify your API credentials in TalentLMS admin panel