# Design: Convert to Fintech MCP

## Context
The server currently hosts two unrelated concerns in one class: a calculator and a TalentLMS HTTP client. The fintech pivot requires a clean slate — a single domain class (`FintechTools`) that exposes four well-described MCP tools backed by an unauthenticated HTTP client against a dummy backend.

## Goals / Non-Goals
- **Goals:**
  - Full replacement of LMS/calculator domain with fintech domain
  - All four tools fully typed with `#[Schema]` annotations rich enough for AI auto-discovery
  - Dummy backend responses (hardcoded or passthrough) sufficient for a live demo
  - Keep implementation surface minimal (single file, single class)
- **Non-Goals:**
  - Authentication or security (explicitly out of scope for this PoC)
  - Real financial backend integration
  - HTTP transport migration (tracked separately in `update-mcp-http-transport`)
  - Database integration

## Decisions

### Single class, single file
Keep everything in `server.php` for now. The codebase is small and splitting into multiple files adds indirection with no current benefit. Revisit if tools exceed ~8 methods.

### Two HTTP helpers: fintechGet + fintechPost
`get_balance` and `get_transactions` are read operations (GET). `send_money` and `pay_bill` mutate state (POST). Two private helpers keeps curl boilerplate centralised without over-abstracting.

### Dummy responses for PoC
Tools will attempt to call `FINTECH_API_BASE_URL`. If the env var is missing or the backend is unavailable, tools return hardcoded dummy payloads rather than throwing — this keeps demos resilient. Real passthrough is enabled when the backend is reachable.

### StdioTransport retained
The `update-mcp-http-transport` change exists but has no proposal or tasks yet. This change does not touch transport; the two can land independently.

### LMS artefact deletion deferred
`TalentLMS Public API.postman_collection.json`, `database.md`, and `product.md` are large files not referenced by any active code after this change. Deletion is tracked as a task requiring user confirmation to avoid accidental loss.

## Risks / Trade-offs
- Hardcoded dummy data may mislead future developers into thinking the backend is real → Mitigate: add a clear `// DUMMY` comment on each hardcoded return
- Single-class approach limits parallel development → Acceptable for a two-person PoC hackathon

## Open Questions
- Should `get_transactions` support cursor-based pagination or offset/limit? (Assumed offset/limit for simplicity)
- Should `send_money` and `pay_bill` be idempotent (i.e., accept an idempotency key)? (Deferred — not needed for PoC)
