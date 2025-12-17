# Change: Add TalentLMS read-only MCP tools

## Why
We need MCP tools that surface TalentLMS training insights (users, courses, certifications, learning paths, skills) to satisfy the product scenarios while keeping the system strictly read-only.

## What Changes
- Add MCP tools for TalentLMS read operations: `get_users`, `get_courses`, `get_certification`, `get_learner_progress`, `get_learning_path`, `get_skill_content`.
- Each tool must call the TalentLMS HTTP API with the shared headers/version, avoiding local data sources.
- Return the HTTP responses (no local filtering/sorting beyond what the API supports) for the matching resources.
- Keep the server read-only (no mutations) while relying solely on HTTP requests.

## Impact
- Affected specs: talentlms
- Affected code: `server.php` (HTTP-backed MCP tools), docs

