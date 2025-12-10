# System Backup Documentation

This document outlines the backup strategies implemented for the **Inventory and Booking System**. The system utilizes a dual-layer backup approach:
1.  **Platform Backups (Railway)**: Automated database backups managed by the hosting provider.
2.  **Application Backups (Local)**: Custom backup utility for generating and downloading SQL dumps via the application interface.

---

## 1. Railway Platform Backups (Database Layer)

If your database is hosted on Railway (e.g., PostgreSQL or MySQL plugin), the platform handles the primary disaster recovery strategy.

*   **What it covers**: The database data only.
*   **Frequency**: Automated by Railway (typically daily or continuous depending on the plan).
*   **Access**:
    1.  Log in to the [Railway Dashboard](https://railway.app/).
    2.  Select your Project.
    3.  Click on the Database Service (Postgres/MySQL).
    4.  Navigate to the **Backups** tab.
*   **Restoration**: You can restore directly from the Railway dashboard to a specific point in time or a specific backup snapshot.
*   **Significance**: This is your *source of truth* and the most reliable backup for the database data.

---

## 2. Local Application Backups (Custom Implementation)

The system includes a built-in backup utility allowing administrators to create, download, and manage database snapshots directly from the application's Admin Panel.

### How it Works
*   **Method**: Uses `mysqldump` to export the currently connected database into a `.sql` file.
*   **Storage Location**: `storage/app/backups/`.
*   **Naming Convention**: `backup-{database}-{date}-{time}.sql`.
*   **Retention**: Keeps backups for **7 days** (configurable in `config/backup.php`).

### > [!WARNING] Important Note on Cloud Deployment
> If you deploy this application to cloud services like **Railway** or **Render** without a persistent volume (disk):
> *   The "Local" backups stored in `storage/app/backups` are **EPHEMERAL**.
> *   **They will be lost** if the application deploys, restarts, or crashes.
> *   **Best Practice**: Always **DOWNLOAD** the backup to your local machine immediately after creating it via the web interface. Do not rely on the file staying on the server.

### Managing Backups via Web Interface
**Access Permission**: `Admin` role only.
**URL**: `/backups`

1.  **Create Backup**:
    *   Click the **"Create Backup"** button.
    *   The system will generate a fresh SQL dump.
2.  **Download**:
    *   Click the **"Download"** icon/button next to a backup file.
    *   Save the `.sql` file to your secure local storage/cloud drive.
3.  **Restore**:
    *   Clicking **"Restore"** will overwrite the *current* active database with the data from the selected backup file.
    *   *Caution*: This action is irreversible.
4.  **Delete**:
    *   Remove old backup files to free up space.

### Managing Backups via Command Line (CLI)
You can also generate backups via the terminal (useful for cron jobs):
```bash
php artisan backup:database
```
Options:
*   `--clean`: Automatically deletes backups older than the retention period (7 days).

### Configuration
The backup settings are defined in `config/backup.php`:
```php
return [
    'path' => storage_path('app/backups'),
    'retention_days' => 7,
    'filename_format' => 'backup-{database}-{date}-{time}.sql',
    // ...
];
```

---

## Summary Recommendation
1.  **Rely on Railway's automated backups** for critical disaster recovery (e.g., database corruption).
2.  **Use the Local Backup tool** for:
    *   Creating "Checkpoints" before deploying new features or making major changes.
    *   Exporting data to migrate to a local development environment.
    *   **Always download the generated file immediately.**
