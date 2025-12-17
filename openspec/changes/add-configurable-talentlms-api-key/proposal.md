# Change: Make TalentLMS API key configurable via MCP settings

## Why
The TalentLMS API key is hardcoded in `server.php`, which forces every user to share the same secret and blocks secure per-user setup. We need users to supply their own key during MCP configuration.

## What Changes
- Add a MCP settings field that accepts a TalentLMS API key so each user can provide their own key.
- Update the TalentLMS HTTP client/tooling to read the key from MCP settings and remove the hardcoded constant.
- Surface clear errors when the key is missing and document how to configure the setting in README/MCP setup guidance.
- Keep the required `X-API-Version: 2025-01-01` header while sourcing `X-API-Key` from user-provided settings.

## Impact
- Affected specs: talentlms
- Affected code: `server.php` (HTTP client/tool wiring), MCP settings/resource handling, README setup section

