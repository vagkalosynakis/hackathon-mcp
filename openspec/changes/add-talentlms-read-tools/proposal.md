# Change: Add TalentLMS read-only MCP tools

## Why
We need MCP tools that surface TalentLMS training insights (users, courses, certifications, learning paths, skills) to satisfy the product scenarios while keeping the system strictly read-only.

## What Changes
- Add MCP tools for TalentLMS read operations: `get_users`, `get_courses`, `get_certification`, `get_learner_progress`, `get_learning_path`, `get_skill_content`.
- Shape responses to cover the audit, certification, branch performance, course progress, learning path, and skill framework scenarios.
- Enforce read-only behavior (no DB writes, no TalentLMS mutations).

## Impact
- Affected specs: talentlms
- Affected code: `server.php` (new MCP tools, HTTP client/config), env docs

