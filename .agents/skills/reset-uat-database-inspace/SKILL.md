---
name: reset-uat-database-inspace
description: Reset a remote UAT / Inspace database on an Acquia Cloud (Gardens) server to the 8.0.x Inspace snapshot. Downloads the shared Google Drive DB export, scp's it to the target server, then drops and restores the database over SSH with drush. Use when the user wants to reset, restore, reload, or refresh the UAT / Inspace database on a cloud server.
---

# Reset UAT Database (Inspace)

Restores a remote **Acquia Cloud (Gardens)** UAT database to the **8.0.x
Inspace** snapshot. The snapshot is a shared MySQL dump hosted on Google Drive.

**Destructive:** this drops the target database and replaces its contents.
Confirm the target SSH host before running, and back up first if in doubt.

## Prerequisites

- SSH access to the target Acquia Cloud server (key already registered).
- A Google Drive downloader — `gdown` is most reliable for large Drive files:
  `pipx install gdown` (or `pip install gdown`, or `brew install gdown`).

## Inputs

- **Google Drive export (8.0.x Inspace):** file ID `1PszpR_3vnUaHzrr5-co1izVuySgmkFNz`
  (https://drive.google.com/file/d/1PszpR_3vnUaHzrr5-co1izVuySgmkFNz/view)
- **Target SSH host** (example — replace with the actual environment):
  `sitestudiotesting1.sstesting1@sitestudiotesting1sstesting1.ssh.gardens.acquia-sites.com`

Set variables for the session:

```bash
SSH_TARGET="sitestudiotesting1.sstesting1@sitestudiotesting1sstesting1.ssh.gardens.acquia-sites.com"
DUMP_GZ="inspace-uat-8.0.x.sql.gz"
```

## Steps

### 1. Download the export locally

Helper (downloads to `backup/inspace-uat-8.0.x.sql.gz`):

```bash
.agents/skills/reset-uat-database-inspace/download.sh "backup/${DUMP_GZ}"
```

Or directly:

```bash
gdown 1PszpR_3vnUaHzrr5-co1izVuySgmkFNz -O "backup/${DUMP_GZ}"
```

Confirm it is a gzip archive, not an HTML error page:

```bash
file "backup/${DUMP_GZ}"   # expect: gzip compressed data
```

### 2. Copy the dump to the target server's /tmp

```bash
scp "backup/${DUMP_GZ}" "${SSH_TARGET}:/tmp/${DUMP_GZ}"
```

### 3. SSH into the target server

```bash
ssh "${SSH_TARGET}"
```

### 4. Gunzip the dump (on the server)

```bash
gunzip "/tmp/${DUMP_GZ}"        # -> /tmp/inspace-uat-8.0.x.sql
```

### 5. Drop the current database (on the server)

```bash
drush sql-drop -y
```

### 6. Restore from the dump (on the server)

```bash
drush sqlc < /tmp/inspace-uat-8.0.x.sql
```

### 7. Post-restore (on the server)

```bash
drush cr
drush cset cohesion.settings api_url https://api.sitestudio.acquia.com -y
drush updb -y -v
drush cohesion:import -v
drush cohesion:rebuild -v   # rebuild Site Studio styles/templates
drush status
```

Then clean up and exit:

```bash
rm -f /tmp/inspace-uat-8.0.x.sql
exit
```

## Notes

- Steps 4–7 run **on the remote server** (inside the SSH session). Steps 1–2
  run on your local machine.
- If `drush` is not on PATH after SSH, `cd` into the site docroot first (e.g.
  `cd /var/www/html/*/docroot`) or use the site's drush alias.
- Do **not** run `drush config:import` after the restore; the snapshot already
  carries the intended config state.
- Access-denied on the Drive download means the file is not shared with your
  account — request access before retrying.
