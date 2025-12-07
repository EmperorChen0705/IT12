# Database Backup System Guide

## Overview

Your Laravel Inventory and Booking System now has an automated local backup system that creates daily database backups and stores them securely on your server.

## Features

✅ **Automated Daily Backups** - Runs every day at 2:00 AM  
✅ **Manual Backup Creation** - Create backups anytime via web interface or command line  
✅ **7-Day Retention** - Automatically deletes backups older than 7 days  
✅ **Web Management Interface** - View, download, delete, and restore backups  
✅ **Secure Storage** - Backups stored in `storage/app/backups/`  

---

## Accessing the Backup System

### Via Web Interface

1. Log in as **Admin**
2. Click **Backups** in the sidebar navigation
3. You'll see a list of all available backups

### Via Command Line

```bash
# Create a backup manually
php artisan backup:database

# Create a backup and clean old ones
php artisan backup:database --clean

# Clean old backups only
php artisan backup:clean

# View scheduled tasks
php artisan schedule:list
```

---

## Creating Manual Backups

### Method 1: Web Interface

1. Navigate to **Backups** page
2. Click **"Create Backup Now"** button
3. Wait for confirmation message
4. New backup will appear in the list

### Method 2: Command Line

```bash
cd /path/to/your/project
php artisan backup:database
```

**When to create manual backups:**
- Before major system updates
- Before database migrations
- Before bulk data imports/exports
- Before testing new features

---

## Downloading Backups

### Via Web Interface

1. Go to **Backups** page
2. Find the backup you want
3. Click the **Download** button (green icon)
4. File will download to your computer

### Via FTP/SFTP

1. Connect to your server via FTP client (FileZilla, WinSCP, etc.)
2. Navigate to: `storage/app/backups/`
3. Download the `.sql` files you need

### Via File Manager (cPanel/Hosting Panel)

1. Log into your hosting control panel
2. Open File Manager
3. Navigate to: `public_html/storage/app/backups/`
4. Select and download files

---

## Restoring from Backup

> **⚠️ WARNING**: Restoring will replace your current database with the backup data. This cannot be undone!

### Method 1: Web Interface (Recommended)

1. Go to **Backups** page
2. Find the backup to restore
3. Click the **Restore** button (yellow icon)
4. Read the warning carefully
5. Click **"Restore Database"** to confirm
6. Wait for confirmation message

### Method 2: Command Line

```bash
# Get database credentials from .env file
mysql -u your_username -p your_database < storage/app/backups/backup-ims_db-2025-12-07-14-39-33.sql
```

---

## Deployment Setup

### For Production Servers

The backup system runs automatically via Laravel's scheduler. You need to add ONE cron job to your server:

#### Linux/Unix Servers

1. SSH into your server
2. Edit crontab:
   ```bash
   crontab -e
   ```
3. Add this line:
   ```
   * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
   ```
4. Save and exit

#### cPanel/Shared Hosting

1. Log into cPanel
2. Go to **Cron Jobs**
3. Add new cron job:
   - **Minute**: `*`
   - **Hour**: `*`
   - **Day**: `*`
   - **Month**: `*`
   - **Weekday**: `*`
   - **Command**: `cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1`
4. Click **Add New Cron Job**

#### Verify Scheduler is Running

```bash
php artisan schedule:list
```

You should see:
- `Daily Database Backup` - Runs at 02:00 daily
- `Weekly Backup Cleanup` - Runs weekly on Sunday

---

## Storage Management

### Check Storage Usage

- **Web Interface**: View total storage on Backups page
- **Command Line**: 
  ```bash
  du -sh storage/app/backups/
  ```

### Manual Cleanup

If you need to free up space immediately:

1. **Via Web**: Click Delete button (red icon) on old backups
2. **Via Command**: `php artisan backup:clean`

### Adjust Retention Period

Edit `config/backup.php`:
```php
'retention_days' => 7,  // Change to desired number of days
```

---

## Uploading Backups to Cloud Storage

While the system stores backups locally, you can manually upload them to cloud storage for extra safety:

### Google Drive

1. Download backup from web interface
2. Upload to Google Drive folder
3. Organize by date

### Dropbox

1. Download backup file
2. Upload to Dropbox
3. Enable version history for extra protection

### Automated Cloud Upload (Optional)

You can use tools like:
- **rclone** - Sync backups to cloud storage
- **Dropbox CLI** - Automated Dropbox uploads
- **Google Drive CLI** - Automated Google Drive uploads

---

## Troubleshooting

### Backup Creation Fails

**Error**: "Backup failed! Please check your database configuration."

**Solution**:
1. Verify database credentials in `.env` file
2. Ensure `mysqldump` is installed on your server
3. Check database connection: `php artisan db:show`

### Permission Errors

**Error**: "Failed to create backup directory"

**Solution**:
```bash
chmod -R 775 storage/app/backups
chown -R www-data:www-data storage/app/backups
```

### Scheduler Not Running

**Problem**: Backups not created automatically

**Solution**:
1. Verify cron job is added: `crontab -l`
2. Check Laravel logs: `storage/logs/laravel.log`
3. Test manually: `php artisan schedule:run`

### Restore Fails

**Error**: "Restore failed!"

**Solution**:
1. Verify backup file exists and is not corrupted
2. Check database user has sufficient permissions
3. Try restoring via command line for more detailed error messages

### Large Database Backups

**Problem**: Backup takes too long or times out

**Solution**:
1. Increase PHP `max_execution_time` in `php.ini`
2. Run backups via command line instead of web interface
3. Consider excluding large tables from backup (edit `config/backup.php`)

---

## Security Best Practices

1. **Restrict Access**: Only admins can access backup system
2. **Download Regularly**: Keep local copies of important backups
3. **Test Restores**: Periodically test restore process
4. **Monitor Storage**: Ensure server has enough disk space
5. **Secure Backups**: Backup files contain sensitive data - protect them

---

## Configuration Reference

### config/backup.php

```php
'path' => storage_path('app/backups'),  // Where backups are stored
'connection' => env('DB_CONNECTION'),    // Database connection to backup
'retention_days' => 7,                   // How long to keep backups
'filename_format' => 'backup-{database}-{date}-{time}.sql',  // Naming pattern
'excluded_tables' => [],                 // Tables to skip
'compress' => false,                     // Enable compression (requires gzip)
```

---

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Run diagnostics: `php artisan about`
3. Test database connection: `php artisan db:show`
