# Deploying to Render with Backup System

## Overview

This guide covers deploying your Laravel Inventory and Booking System to Render.com with the automated backup system fully configured.

---

## Prerequisites

- GitHub account (to connect your repository)
- Render account (free tier available)
- Your Laravel project pushed to GitHub

---

## Step 1: Prepare Your Project for Render

### 1.1 Create Build Script

Create a file named `render-build.sh` in your project root:

```bash
#!/usr/bin/env bash
# exit on error
set -o errexit

composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

Make it executable:
```bash
chmod +x render-build.sh
```

### 1.2 Create Procfile

Create a file named `Procfile` in your project root:

```
web: php artisan serve --host=0.0.0.0 --port=$PORT
```

### 1.3 Update .gitignore

Ensure these are NOT ignored (should be committed):
```
# Remove these from .gitignore if present:
# /vendor
# composer.lock
```

---

## Step 2: Push to GitHub

```bash
git add .
git commit -m "Prepare for Render deployment"
git push origin main
```

---

## Step 3: Create Web Service on Render

1. **Go to** [https://dashboard.render.com](https://dashboard.render.com)
2. **Click** "New +" → "Web Service"
3. **Connect** your GitHub repository
4. **Configure**:
   - **Name**: `inventory-booking-system` (or your choice)
   - **Environment**: `PHP`
   - **Build Command**: `./render-build.sh`
   - **Start Command**: `php artisan serve --host=0.0.0.0 --port=$PORT`
   - **Plan**: Free (or paid for production)

---

## Step 4: Add Environment Variables

In Render dashboard, go to **Environment** tab and add:

```
APP_NAME=SubWFour Inventory System
APP_ENV=production
APP_KEY=<generate with: php artisan key:generate --show>
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

DB_CONNECTION=mysql
DB_HOST=<your-render-mysql-host>
DB_PORT=3306
DB_DATABASE=<your-database-name>
DB_USERNAME=<your-database-user>
DB_PASSWORD=<your-database-password>

SESSION_DRIVER=database
SESSION_LIFETIME=120

LOG_CHANNEL=stack
LOG_LEVEL=error
```

---

## Step 5: Create MySQL Database on Render

1. **Go to** Render Dashboard
2. **Click** "New +" → "MySQL"
3. **Configure**:
   - **Name**: `inventory-db`
   - **Database**: `ims_db`
   - **User**: `ims_user`
   - **Plan**: Free (or paid)
4. **Copy** connection details to your Web Service environment variables

---

## Step 6: Configure Backup System for Render

### 6.1 Update Build Script

Edit `render-build.sh` to include backup directory creation:

```bash
#!/usr/bin/env bash
set -o errexit

composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# Create backup directory
mkdir -p storage/app/backups
chmod -R 775 storage/app/backups
```

### 6.2 Add Cron Job on Render

Render supports cron jobs! Create a new **Cron Job** service:

1. **Go to** Render Dashboard
2. **Click** "New +" → "Cron Job"
3. **Configure**:
   - **Name**: `backup-scheduler`
   - **Environment**: Same as your web service
   - **Command**: `php artisan schedule:run`
   - **Schedule**: `* * * * *` (every minute)
   - **Connect** to same GitHub repository

4. **Add same environment variables** as your web service

**Important**: The cron job will run `php artisan schedule:run` every minute, which will trigger your backup at 2:00 AM automatically.

---

## Step 7: Deploy

1. **Click** "Create Web Service"
2. **Wait** for build to complete (5-10 minutes)
3. **Visit** your app URL: `https://your-app-name.onrender.com`

---

## Step 8: Verify Backup System

### 8.1 Test Manual Backup

SSH into Render (if available) or use Render Shell:

```bash
php artisan backup:database
```

### 8.2 Check Scheduled Tasks

```bash
php artisan schedule:list
```

You should see:
- Daily Database Backup - Runs at 02:00
- Weekly Backup Cleanup - Runs weekly

### 8.3 Verify via Web Interface

1. Log in as Admin
2. Navigate to `/backups`
3. Click "Create Backup Now"
4. Verify backup appears in list

---

## Step 9: Backup Storage Considerations

### Option A: Local Storage (Render Disk)

**Pros**: Simple, no extra setup
**Cons**: Limited storage, may be lost on redeploy

Render provides persistent disk storage. To enable:

1. Go to your Web Service settings
2. Add a **Disk** under "Disks" section:
   - **Name**: `backups`
   - **Mount Path**: `/opt/render/project/src/storage/app/backups`
   - **Size**: 1GB (or more)

### Option B: Cloud Storage (Recommended for Production)

For production, consider storing backups in cloud storage:

**AWS S3**:
1. Create S3 bucket
2. Add to `.env`:
   ```
   AWS_ACCESS_KEY_ID=your-key
   AWS_SECRET_ACCESS_KEY=your-secret
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=your-backup-bucket
   ```

**Google Cloud Storage** or **Dropbox** can also be configured.

---

## Step 10: Monitoring Backups

### Check Backup Logs

In Render Dashboard:
1. Go to your Cron Job service
2. Click "Logs"
3. Verify backups are running at 2:00 AM

### Download Backups

**Via Web Interface**:
1. Log in as Admin
2. Go to Backups page
3. Click Download on any backup

**Via Render Shell** (if available):
1. Open Shell in Render Dashboard
2. Navigate to: `cd storage/app/backups`
3. Download files via SFTP or Render's file browser

---

## Troubleshooting

### Backups Not Running

**Check Cron Job Service**:
- Ensure cron job service is running
- Check logs for errors
- Verify environment variables match web service

**Test Manually**:
```bash
php artisan schedule:run
```

### Permission Errors

```bash
chmod -R 775 storage/app/backups
chown -R www-data:www-data storage/app/backups
```

### Database Connection Issues

- Verify MySQL service is running
- Check DB credentials in environment variables
- Ensure web service can connect to database

---

## Production Checklist

Before going live:

- [ ] Environment variables configured
- [ ] Database created and migrated
- [ ] Backup cron job running
- [ ] Test manual backup creation
- [ ] Test backup download
- [ ] Test backup restore
- [ ] Verify scheduled tasks with `php artisan schedule:list`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure custom domain (optional)
- [ ] Set up SSL certificate (Render provides free SSL)

---

## Render-Specific Features

### Auto-Deploy

Render automatically deploys when you push to GitHub:
```bash
git push origin main
# Render automatically rebuilds and deploys
```

### Zero-Downtime Deploys

Render provides zero-downtime deployments on paid plans.

### Health Checks

Render automatically monitors your app at `/up` endpoint (Laravel 11+).

### Logs

View real-time logs in Render Dashboard under "Logs" tab.

---

## Cost Considerations

### Free Tier
- Web Service: Free (with limitations)
- MySQL: Free (1GB storage)
- Cron Job: Free
- **Total**: $0/month

### Paid Tier (Recommended for Production)
- Web Service: $7/month (512MB RAM)
- MySQL: $7/month (1GB storage)
- Persistent Disk: $0.25/GB/month
- **Total**: ~$15-20/month

---

## Backup Best Practices on Render

1. **Enable Persistent Disk** for backup storage
2. **Download weekly backups** to local storage
3. **Consider cloud storage** for critical production data
4. **Monitor cron job logs** to ensure backups run
5. **Test restore process** before going live

---

## Support

- **Render Docs**: https://render.com/docs
- **Laravel Docs**: https://laravel.com/docs
- **Backup System Guide**: See `BACKUP_GUIDE.md`

---

## Quick Deploy Commands

```bash
# Initial setup
git add .
git commit -m "Deploy to Render"
git push origin main

# After changes
git add .
git commit -m "Update application"
git push origin main
# Render auto-deploys!

# Manual backup (via Render Shell)
php artisan backup:database

# Check scheduler
php artisan schedule:list
```

---

## Summary

Your backup system will work seamlessly on Render:
- ✅ Cron job runs `schedule:run` every minute
- ✅ Backup executes at 2:00 AM daily
- ✅ Cleanup runs weekly
- ✅ Web interface accessible at `/backups`
- ✅ Zero maintenance required

The system is production-ready and will protect your client's data automatically! 🚀
