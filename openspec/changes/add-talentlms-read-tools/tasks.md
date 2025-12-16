## 1. Implementation
- [ ] 1.1 Add HTTP client/config for TalentLMS API (base URL, API key, read-only)
- [ ] 1.2 Implement MCP tools: `get_users`, `get_courses`, `get_certification`, `get_learner_progress`, `get_learning_path`, `get_skill_content`
- [ ] 1.3 Format responses to satisfy product scenarios with clear errors/timeouts
- [ ] 1.4 Enforce read-only behavior (no DB writes, no TalentLMS mutations) with guardrails
- [ ] 1.5 Update docs/env hints (e.g., `project.md`/`README`) if needed

## 2. Validation
- [ ] 2.1 Run `openspec validate add-talentlms-read-tools --strict`
- [ ] 2.2 Manually exercise tools via MCP inspector/client

