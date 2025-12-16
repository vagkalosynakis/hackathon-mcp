## 1. Implementation
- [ ] 1.1 Add Doctrine DBAL/ORM dependency in Composer
- [ ] 1.2 Configure MySQL connection parameters (host `mysql`, user `usr_local_sandbox176580221027`, password `5GyGpkvv@l`, db `local_sandbox`, port 3306) via env vars with safe defaults
- [ ] 1.3 Wire Doctrine bootstrap/connection in the MCP server and expose it for future data access
- [ ] 1.4 Add docs for required env vars and Docker network expectations

## 2. Validation
- [ ] 2.1 Run `openspec validate add-doctrine-mysql-connection --strict`

