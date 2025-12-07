# 🚀 Render Deployment Guide - SubWFour Inventory & Booking System (MySQL)

This guide will walk you through deploying your Laravel application to Render with MySQL database.

---

## 📋 Prerequisites

Before you start, make sure you have:

- ✅ A GitHub account (free)
- ✅ A Render account (free tier available at https://render.com)
- ✅ A MySQL database provider account (Railway, PlanetScale, or external MySQL)
- ✅ Your project code ready
- ✅ Git installed on your computer

---

## 🔧 Step 1: Prepare Your Project

### 1.1 Create Required Files

First, we need to create some configuration files for Render.

#### Create `render-build.sh` in your project root:

```bash
#!/usr/bin/env bash
# exit on error
set -o errexit

echo "🔧 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🔑 Generating application key..."
php artisan key:generate --force

echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "🧹 Clearing and caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔗 Creating storage link..."
php artisan storage:link || true

echo "✅ Build completed successfully!"
```

#### Create `render.yaml` in your project root:

```yaml
services:
  - type: web
    name: subwfour-inventory
    env: php
    plan: starter
    buildCommand: bash render-build.sh
    startCommand: php artisan serve --host=0.0.0.0 --port=$PORT
    envVars:
      - key: APP_NAME
        value: SubWFour Inventory
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
      - key: APP_KEY
        generateValue: true
      - key: LOG_CHANNEL
        value: stack
      - key: SESSION_DRIVER
        value: file
      - key: SESSION_LIFETIME
        value: 120
      - key: DB_CONNECTION
        value: mysql
      - key: DB_HOST
        sync: false
      - key: DB_PORT
        value: 3306
      - key: DB_DATABASE
        sync: false
      - key: DB_USERNAME
        sync: false
      - key: DB_PASSWORD
        sync: false
```

### 1.2 Update `.gitignore`

Make sure your `.gitignore` includes:

```
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
```

### 1.3 Update `.env.example`

Make sure your `.env.example` has all necessary variables:

```env
APP_NAME="SubWFour Inventory"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-app.onrender.com

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

## 🗄️ Step 2: Set Up MySQL Database

You have several options for MySQL hosting:

### Option A: Railway (Recommended - Easy Setup)

1. Go to https://railway.app
2. Sign up with GitHub
3. Click **"New Project"** → **"Provision MySQL"**
4. Wait for database to be created
5. Click on MySQL service → **"Connect"** tab
6. Copy these values:
   - **MYSQL_HOST**
   - **MYSQL_PORT** (usually 3306)
   - **MYSQL_DATABASE**
   - **MYSQL_USER**
   - **MYSQL_PASSWORD**

**Cost**: Free tier includes $5/month credit (enough for small apps)

### Option B: PlanetScale (Serverless MySQL)

1. Go to https://planetscale.com
2. Sign up and create new database
3. Create database: `subwfour_inventory`
4. Get connection details from dashboard
5. Copy host, username, password

**Cost**: Free tier available (5GB storage, 1 billion row reads/month)

### Option C: External MySQL Provider

Use any MySQL hosting provider:
- Hostinger
- SiteGround  
- DigitalOcean Managed Database
- AWS RDS
- Your own server

Just get the connection details (host, port, database name, username, password).

---

## 📦 Step 3: Push to GitHub

### 3.1 Initialize Git (if not already done)

Open your terminal in the project directory:

```bash
cd e:\IT12\Inventory-and-Booking-System
git init
```

### 3.2 Add All Files

```bash
git add .
git commit -m "Initial commit - Ready for deployment with MySQL"
```

### 3.3 Create GitHub Repository

1. Go to https://github.com
2. Click the **"+"** icon (top right) → **"New repository"**
3. Repository name: `Inventory-and-Booking-System`
4. Description: `SubWFour Inventory and Booking Management System`
5. Choose **Private** (recommended for business apps)
6. **DO NOT** initialize with README, .gitignore, or license
7. Click **"Create repository"**

### 3.4 Push to GitHub

Copy the commands from GitHub (they'll look like this):

```bash
git remote add origin https://github.com/YOUR-USERNAME/Inventory-and-Booking-System.git
git branch -M main
git push -u origin main
```

**Replace `YOUR-USERNAME` with your actual GitHub username!**

---

## 🌐 Step 4: Deploy to Render

### 4.1 Create Render Account

1. Go to https://render.com
2. Click **"Get Started"**
3. Sign up with GitHub (recommended) or email
4. Verify your email if needed

### 4.2 Create Web Service

1. Click **"New +"** → **"Web Service"**
2. Click **"Connect a repository"**
3. If first time: Click **"Configure account"** → Select your GitHub account → **"Install"**
4. Find and select your repository: `Inventory-and-Booking-System`
5. Click **"Connect"**

### 4.3 Configure Web Service

Fill in the deployment settings:

**Basic Settings:**
- **Name**: `subwfour-inventory` (this will be your URL)
- **Region**: Choose closest to your location (e.g., Singapore)
- **Branch**: `main`
- **Root Directory**: Leave blank
- **Runtime**: **PHP**

**Build & Deploy:**
- **Build Command**: 
  ```bash
  bash render-build.sh
  ```
- **Start Command**:
  ```bash
  php artisan serve --host=0.0.0.0 --port=$PORT
  ```

**Instance Type:**
- **Free** (for testing) or **Starter** ($7/month - recommended for production)

### 4.4 Add Environment Variables

Scroll down to **"Environment Variables"** and add these:

Click **"Add Environment Variable"** for each:

| Key | Value |
|-----|-------|
| `APP_NAME` | `SubWFour Inventory` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `base64:GENERATE_THIS_LATER` |
| `APP_URL` | `https://subwfour-inventory.onrender.com` |
| `LOG_CHANNEL` | `stack` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | Your MySQL host from Step 2 |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | Your database name from Step 2 |
| `DB_USERNAME` | Your MySQL username from Step 2 |
| `DB_PASSWORD` | Your MySQL password from Step 2 |
| `SESSION_DRIVER` | `file` |
| `SESSION_LIFETIME` | `120` |

**Note**: Replace `subwfour-inventory` in `APP_URL` with your actual service name!

### 4.5 Generate APP_KEY

After creating the service:

1. Go to your service dashboard
2. Click **"Shell"** tab (on the left)
3. Run this command:
   ```bash
   php artisan key:generate --show
   ```
4. Copy the generated key (looks like: `base64:xxxxxxxxxxxxx`)
5. Go to **"Environment"** tab
6. Find `APP_KEY` and click **"Edit"**
7. Paste the generated key
8. Click **"Save Changes"**

### 4.6 Deploy!

1. Click **"Create Web Service"** at the bottom
2. Render will start building and deploying
3. Watch the logs - deployment takes 5-10 minutes
4. When you see **"Your service is live 🎉"**, it's ready!

---

## ✅ Step 5: Verify Deployment

### 5.1 Access Your Application

1. Click the URL at the top of your service dashboard
   - Example: `https://subwfour-inventory.onrender.com`
2. You should see your login page!

### 5.2 Create First Admin User

You need to create an admin user. You have two options:

#### Option A: Using MySQL Client

1. Connect to your MySQL database using the credentials
2. Run this SQL:

```sql
-- Create admin user
INSERT INTO users (name, email, password, role, created_at, updated_at)
VALUES (
    'Admin',
    'admin@subwfour.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    NOW(),
    NOW()
);
```

#### Option B: Using Render Shell

1. Go to Render Dashboard → Your Service → **Shell**
2. Run:
```bash
php artisan tinker
```
3. Then run:
```php
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@subwfour.com',
    'password' => bcrypt('password'),
    'role' => 'admin'
]);
```
4. Type `exit` to exit tinker

**Default password**: `password`

**IMPORTANT**: Change this password immediately after first login!

### 5.3 Test Your Application

1. Visit your site: `https://your-app.onrender.com`
2. Login with: `admin@subwfour.com` / `password`
3. Change password immediately
4. Test booking portal: `https://your-app.onrender.com/booking`
5. Create test bookings, services, inventory items

---

## 🔄 Step 6: Making Updates

### 6.1 Update Your Code Locally

```bash
# Make your changes
# Test locally with: php artisan serve
```

### 6.2 Push Updates

```bash
git add .
git commit -m "Description of changes"
git push origin main
```

### 6.3 Automatic Deployment

- Render automatically detects the push
- Rebuilds and redeploys your app
- Takes 2-5 minutes
- Zero downtime!

---

## 🛠️ Troubleshooting

### Issue: "Application Key Not Set"

**Solution:**
1. Go to Render Dashboard → Your Service → Shell
2. Run: `php artisan key:generate --show`
3. Copy the key
4. Add to Environment Variables as `APP_KEY`
5. Redeploy

### Issue: "Database Connection Failed"

**Solution:**
1. Verify MySQL credentials in Environment Variables:
   - `DB_HOST`
   - `DB_PORT` (should be 3306)
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
2. Test connection from Render Shell:
   ```bash
   php artisan migrate:status
   ```
3. Check if MySQL server allows external connections
4. Verify firewall/security group settings

### Issue: "SQLSTATE[HY000] [2002] Connection refused"

**Solution:**
1. Your MySQL host might not allow external connections
2. Check if you need to whitelist Render's IP addresses
3. For Railway: Make sure you're using the public host, not localhost
4. For PlanetScale: Ensure SSL is configured if required

### Issue: "500 Internal Server Error"

**Solution:**
1. Check logs: Render Dashboard → Your Service → Logs
2. Look for error messages
3. Common fixes:
   - Set `APP_DEBUG=true` temporarily to see errors
   - Run migrations: `php artisan migrate --force`
   - Clear cache: `php artisan cache:clear`

### Issue: "Storage Link Not Working"

**Solution:**
1. Go to Shell tab
2. Run: `php artisan storage:link`

---

## 📊 Monitoring Your Application

### View Logs

1. Render Dashboard → Your Service → **Logs**
2. See real-time application logs
3. Filter by date/time

### Check Metrics

1. Render Dashboard → Your Service → **Metrics**
2. View CPU, Memory, Request stats

### Database Backups

**Important**: Set up regular backups!

1. Use your built-in backup system at `/backups`
2. Download backups regularly
3. Store them securely
4. Also use your MySQL provider's backup features

---

## 💰 Pricing Estimate

### Render Web Service
- **Free**: Good for testing (spins down after 15 min)
- **Starter**: $7/month (always on, recommended)

### MySQL Database
- **Railway**: Free tier ($5/month credit)
- **PlanetScale**: Free tier (5GB storage)
- **External**: Varies by provider ($5-20/month)

**Total Recommended for Production**: ~$7-15/month

---

## 🎯 Next Steps

1. ✅ Set up MySQL database
2. ✅ Deploy to Render
3. ✅ Test all features
4. ✅ Create admin account
5. ✅ Change default password
6. ✅ Set up regular backups
7. ✅ Share booking portal URL with customers
8. ✅ Train staff on the system

---

## 📞 Support

If you encounter issues:

1. Check Render logs first
2. Review this guide
3. Check MySQL connection from Render Shell
4. Check Render documentation: https://render.com/docs
5. Contact Render support (they're very helpful!)

---

## 🎉 Congratulations!

Your SubWFour Inventory & Booking System is now live on the internet with MySQL!

**Your URLs:**
- Admin System: `https://your-app.onrender.com`
- Booking Portal: `https://your-app.onrender.com/booking`

Share the booking portal URL with your customers and start taking bookings! 🚀
