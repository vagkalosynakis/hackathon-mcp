## 1. Implementation
- [x] 1.1 Add Doctrine DBAL/ORM dependency in Composer
- [x] 1.2 Configure MySQL connection parameters (host `mysql`, user `usr_local_sandbox176580221027`, password `5GyGpkvv@l`, db `local_sandbox`, port 3306) via env vars with safe defaults
- [x] 1.3 Wire Doctrine connection usage inside the MCP server without top-level helper functions
- [x] 1.4 Add docs for required env vars and Docker network expectations
- [x] 1.5 Add a simple `list_courses` MCP tool that fetches all rows from the `course` table via Doctrine

## 2. Validation
- [ ] 2.1 Run `openspec validate add-doctrine-mysql-connection --strict`

