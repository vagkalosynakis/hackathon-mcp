# Change: Add TalentLMS read-only MCP tools

## Why
We need MCP tools that surface TalentLMS training insights (users, courses, certifications, learning paths, skills) to satisfy the product scenarios while keeping the system strictly read-only.

## What Changes
- Add MCP tools for TalentLMS read operations: `get_users`, `get_courses`, `get_certification`, `get_learner_progress`, `get_learning_path`, `get_skill_content`.
- Each tool must query the database (Doctrine, same connection pattern as `listCourses`), no hardcoded/static data.
- Return raw DB rows (no filtering/sorting/joins beyond the single source table unless identical to schema) from the matching tables:
  - `get_users` → `user`
  - `get_courses` → `course`
  - `get_certification` → `user_to_certification`
  - `get_learner_progress` → `course_progress`
  - `get_learning_path` → `learning_path`
  - `get_skill_content` → `skill`
- Keep the server read-only (no mutations) and avoid any HTTP/auth/TalentLMS API usage.

## Impact
- Affected specs: talentlms
- Affected code: `server.php` (DB-backed MCP tools), docs; no outbound HTTP dependencies or auth setup

