# Deploying to Render with Railway Database

## Overview

This guide covers deploying your Laravel application to **Render (Web)** while using **Railway (Database)**. This is a popular setup because Railway offers excellent persistent databases.

Files included:
- `render.yaml` - Configures Render to ask for your Railway credentials during setup.
- `render-build.sh` - Handles installation and backup folder creation.

---

## Step 1: Set Up Railway Database

1. **Go to** [https://railway.app](https://railway.app)
2. **Click** "New Project" → "Provision MySQL"
3. **Click** on your new MySQL service → **Variables** tab
4. **Keep this tab open**, you will need these values:
   - `MYSQLHOST` (DB_HOST)
   - `MYSQLPORT` (DB_PORT)
   - `MYSQLDATABASE` (DB_DATABASE)
   - `MYSQLUSER` (DB_USERNAME)
   - `MYSQLPASSWORD` (DB_PASSWORD)

> **Important**: Ensure your Railway MySQL service is publicly accessible or that you copy the "Public Networking" host if available.

---

## Step 2: Push to GitHub

Push the updated configuration to your repository:

```bash
git add .
git commit -m "Configure for Railway Database"
git push origin main
```

---

## Step 3: Deploy to Render (Blueprint)

1. **Go to** [https://dashboard.render.com](https://dashboard.render.com)
2. **Click** "New +" → "Blueprint"
3. **Connect** your GitHub repository
4. **Give it a name** (e.g., `inventory-system`)
5. **Click** "Apply Blueprint"

---

## Step 4: Enter Database Credentials

Render will detect the `render.yaml` file and see that it needs database details. It will ask you to fill them in **before** the build starts.

**Fill in the values from Railway:**

| Render Label | Railway Variable |
|--------------|------------------|
| `DB_HOST`    | `MYSQLHOST`      |
| `DB_PORT`    | `MYSQLPORT`      |
| `DB_DATABASE`| `MYSQLDATABASE`  |
| `DB_USERNAME`| `MYSQLUSER`      |
| `DB_PASSWORD`| `MYSQLPASSWORD`  |

**Click "Apply Blueprint"** again to finish.

---

## Step 5: Verify & Backup

Once deployed:

1. **Log in** to your new site.
2. **Go to Admin > Backups**.
3. **Create a Manual Backup** to ensure the connection works.

### How Backups Work with Railway
Even though the database is on Railway, the backup script runs on **Render**.
1. Render connects to Railway via the network.
2. It runs `mysqldump` pulling data from Railway.
3. It saves the backup file to Render's local storage.
4. (Optional) You can download these backups via the web interface.

---

## Troubleshooting

### "Connection Refused"
- Check that `DB_HOST` is correct (it should look like `monorail.proxy.rlwy.net` or similar).
- Ensure `DB_PORT` is correct (Railway often uses ports like `12345`, NOT just `3306`).

### "Migration Failed"
- Check that the Railway database is active.
- Verify user permissions in Railway.

---

## Cost
- **Render (Web + Scheduler)**: Free Tier
- **Railway (Database)**: Trial / Usage-based ($5 credit/month usually covers small apps)
