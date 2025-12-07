# 🐳 Docker Deployment Guide - SubWFour Inventory & Booking System

This guide will walk you through deploying your Laravel application to Render using Docker and MySQL.

---

## 📋 What You'll Need

- ✅ GitHub account
- ✅ Render account (https://render.com)
- ✅ MySQL database (Railway, PlanetScale, or external)
- ✅ Your project code with Docker files (already created!)

---

## 📦 Files Already Created

Your project now has these Docker files:

- ✅ `Dockerfile` - Defines your application container
- ✅ `docker/nginx.conf` - Nginx web server configuration
- ✅ `docker/entrypoint.sh` - Startup script
- ✅ `.dockerignore` - Files to exclude from Docker build

---

## 🗄️ Step 1: Set Up MySQL Database

### Option A: Railway (Recommended - Easiest)

1. Go to https://railway.app
2. Sign up with GitHub
3. Click **"New Project"** → **"Provision MySQL"**
4. Wait for database creation
5. Click MySQL service → **"Connect"** tab
6. Copy these values:
   - `MYSQL_HOST`
   - `MYSQL_PORT` (usually 3306)
   - `MYSQL_DATABASE`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`

**Cost**: Free tier with $5/month credit

### Option B: PlanetScale

1. Go to https://planetscale.com
2. Create database: `subwfour_inventory`
3. Get connection details

**Cost**: Free tier (5GB storage)

### Option C: External MySQL

Use any MySQL provider you have access to.

---

## 📦 Step 2: Push to GitHub

### 2.1 Push to Your Repository

Open terminal in your project folder:

```bash
cd e:\IT12\Inventory-and-Booking-System

# Add all files
git add .

# Commit changes
git commit -m "Restored Docker configuration"

# Push to GitHub
git push origin main
```

---

## 🚀 Step 3: Deploy to Render with Docker

### 3.1 Create Render Account

1. Go to https://render.com
2. Sign up with GitHub
3. Verify email

### 3.2 Create Web Service

1. Click **"New +"** → **"Web Service"**
2. Click **"Connect a repository"**
3. Select: `Inventory-and-Booking-System`
4. Click **"Connect"**

### 3.3 Configure Service

**Basic Settings:**
- **Name**: `subwfour-inventory`
- **Region**: Choose closest (e.g., Singapore)
- **Branch**: `main`
- **Root Directory**: Leave blank
- **Environment**: **Docker** (Crucial Step!)

**Docker Settings:**
- **Dockerfile Path**: `Dockerfile` (default)
- Render will automatically detect your Dockerfile!

**Instance Type:**
- **Free** (for testing - spins down after 15 min)
- **Starter** ($7/month - recommended for production)

### 3.4 Add Environment Variables

Click **"Add Environment Variable"** for each (or use bulk add):

| Key | Value | Example |
|-----|-------|---------|
| `APP_NAME` | `SubWFour Inventory` | |
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | |
| `APP_KEY` | Generate this later | `base64:xxx...` |
| `APP_URL` | Your Render URL | `https://subwfour-inventory.onrender.com` |
| `LOG_CHANNEL` | `stack` | |
| `DB_CONNECTION` | `mysql` | |
| `DB_HOST` | From Step 1 | `containers-us-west-xxx.railway.app` |
| `DB_PORT` | `3306` | |
| `DB_DATABASE` | From Step 1 | `railway` |
| `DB_USERNAME` | From Step 1 | `root` |
| `DB_PASSWORD` | From Step 1 | `your-password` |
| `SESSION_DRIVER` | `file` | |
| `SESSION_LIFETIME` | `120` | |

### 3.5 Deploy!

1. Click **"Create Web Service"**
2. Render will build your Docker image and deploy
3. Wait 5-10 minutes for first deployment

---

## 🔑 Step 4: Generate Application Key

After deployment completes:

1. Go to your service dashboard
2. Click **"Shell"** tab
3. Run:
   ```bash
   php artisan key:generate --show
   ```
4. Copy the generated key
5. Go to **"Environment"** tab
6. Edit `APP_KEY` and paste the key
7. Click **"Save Changes"**
8. Service will automatically redeploy

---

## 👤 Step 5: Create Admin User

### Method 1: Using Render Shell

1. Go to **"Shell"** tab
2. Run:
   ```bash
   php artisan tinker
   ```
3. Then:
   ```php
   \App\Models\User::create([
       'name' => 'Admin',
       'email' => 'admin@subwfour.com',
       'password' => bcrypt('password'),
       'role' => 'admin'
   ]);
   ```
4. Type `exit`

### Method 2: Using MySQL Client

Connect to your MySQL database and run:

```sql
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

**Default password**: `password` (Change immediately!)

---

## ✅ Step 6: Test Your Application

1. Visit your URL: `https://subwfour-inventory.onrender.com`
2. Login with: `admin@subwfour.com` / `password`
3. **Change password immediately**
4. Test features

---

## 🔄 Updating Your App

```bash
# 1. Make changes locally
# 2. Add changes
git add .
# 3. Commit
git commit -m "Update description"
# 4. Push
git push origin main
# 5. Render auto-deploys!
```

**Benefits of Docker:**
- ✅ Consistent environment
- ✅ Fewer "it works on my machine" issues
- ✅ Easier dependency management
