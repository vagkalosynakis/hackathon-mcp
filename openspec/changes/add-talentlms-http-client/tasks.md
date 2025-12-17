## 1. Implementation
- [ ] 1.1 Create a reusable HTTP client configured with `https://plusfe.dev.talentlms.com` and required headers (`X-API-Version`, `X-API-Key`).
- [ ] 1.2 Refactor the `get_users` MCP tool to call `{{baseUrl}}/api/v2/users` via the HTTP client and return the response payload (no DB access).
- [ ] 1.3 Add clear error handling for HTTP failures/timeouts and propagate actionable messages to MCP clients.
- [ ] 1.4 Document configuration/usage notes if needed (e.g., README or env guidance).

## 2. Validation
- [ ] 2.1 Run `openspec validate add-talentlms-http-client --strict`

