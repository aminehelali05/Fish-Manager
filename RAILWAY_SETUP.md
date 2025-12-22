# Railway setup for Fish-Manager

Follow these steps to configure the Railway service so your app can connect to the DB and import the provided SQL.

1) Service environment variables (set these in Railway → Service → Variables or via Railway CLI)

Key     | Value
--------|-------------------------------
DB_HOST | `mysql.railway.internal`
DB_USER | `root`
DB_PASS | `CuTdhwVTfeNucsgXYbOHVWYvbqJvmsgZ`
DB_NAME | `railway`
IMPORT_TOKEN | choose a long random secret (used by `import.php`)

Notes:
- Do NOT commit secrets into version control. Use Railway Service Variables.
- `DB_NAME` above is `railway` (Railway default database name). If you prefer `fish_manager` adjust accordingly and import SQL using that database name.

2) Use Railway CLI (optional) — quick commands

# Install & login
npm i -g railway
railway login
railway link   # link to your project

# Set the variables (example)
railway variables set DB_HOST mysql.railway.internal
railway variables set DB_USER root
railway variables set DB_PASS CuTdhwVTfeNucsgXYbOHVWYvbqJvmsgZ
railway variables set DB_NAME railway
railway variables set IMPORT_TOKEN <your-long-secret-here>

3) Import `database.sql` using the included `import.php` (recommended)

- Ensure `database.sql` is present in repo (it is).
- Set `IMPORT_TOKEN` as a service variable to a long value.
- Visit (or curl) the importer endpoint in your deployed service:

  https://<your-service>.railway.app/import.php?token=<your-long-secret>

- Expected response: `Import OK`.
- After successful import, **delete** `import.php` from the project and the server for security and `git rm` it from the repo.

4) Notes about Docker / Apache
- The `Dockerfile` in this repo was tuned to avoid multiple MPM issues and enables `mod_rewrite`.
- Railway injects `PORT` at runtime — the Dockerfile replaces `Listen 80` with `Listen ${PORT:-80}` at container start, so nothing else is needed.

If you prefer, I can run the import for you if you grant me temporary access (or run the Railway CLI commands locally); otherwise follow the steps above and paste the importer output here.