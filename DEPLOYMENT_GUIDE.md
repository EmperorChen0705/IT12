# 🚀 Render Deployment Guide - SubWFour Inventory & Booking System

This guide will walk you through deploying your Laravel application to Render with MySQL database (without Docker).

---

## 📋 Prerequisites

Before you start, make sure you have:

- ✅ A GitHub account (free)
- ✅ A Render account (free tier available at https://render.com)
- ✅ A MySQL database provider account (Railway, PlanetScale, or external MySQL)
- ✅ Your project code ready
- ✅ Git installed on your computer

---

## 🗄️ Step 1: Set Up MySQL Database

You have several options for MySQL hosting:

### Option A: Railway (Recommended - Easiest)

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

## 📦 Step 2: Push to GitHub

### 2.1 Create GitHub Account (if needed)

1. Go to https://github.com
2. Click **"Sign up"**
3. Enter email, create password, choose username
4. Verify email

### 2.2 Create GitHub Repository

1. After logging in, click **"+"** icon (top right)
2. Click **"New repository"**
3. Fill in:
   - **Repository name**: `Inventory-and-Booking-System`
   - **Description**: `SubWFour Inventory and Booking Management System`
   - **Visibility**: Choose **Private** (recommended)
   - **DO NOT** check any boxes (no README, no .gitignore, no license)
4. Click **"Create repository"**

### 2.3 Push Your Code to GitHub

#### Option A: Using Command Line

Open terminal in your project folder:

```bash
cd e:\IT12\Inventory-and-Booking-System

# Initialize git (if not already done)
git init

# Add all files
git add .

# Commit files
git commit -m "Initial commit - Ready for deployment"

# Add your GitHub repository (replace YOUR-USERNAME!)
git remote add origin https://github.com/YOUR-USERNAME/Inventory-and-Booking-System.git

# Push to GitHub
git branch -M main
git push -u origin main
```

**When prompted for credentials:**
- **Username**: Your GitHub username
- **Password**: Use a Personal Access Token (see below)

#### Option B: Using GitHub Desktop (Easier!)

1. Download GitHub Desktop: https://desktop.github.com
2. Install and sign in with your GitHub account
3. Click **"File"** → **"Add local repository"**
4. Browse to: `e:\IT12\Inventory-and-Booking-System`
5. Click **"Publish repository"**
6. Choose **Private**
7. Click **"Publish repository"**

**This is much easier and handles authentication automatically!**

### 2.4 Create Personal Access Token (for Command Line)

If using command line, you need a token:

1. Go to https://github.com/settings/tokens
2. Click **"Generate new token"** → **"Generate new token (classic)"**
3. Name: `Deployment Token`
4. Expiration: `No expiration` or `1 year`
5. Check: ✅ **repo** (full control of private repositories)
6. Click **"Generate token"**
7. **COPY THE TOKEN** (save it somewhere safe!)
8. Use this token as your password when pushing

---

## 🌐 Step 3: Deploy to Render

### 3.1 Create Render Account

1. Go to https://render.com
2. Click **"Get Started"**
3. Sign up with GitHub (recommended) or email
4. Verify your email if needed

### 3.2 Create Web Service

1. In Render Dashboard, click **"New +"** → **"Web Service"**
2. Click **"Connect a repository"**
3. If first time: 
   - Click **"Configure account"**
   - Select your GitHub account
   - Click **"Install"** or **"Configure"**
   - Choose which repositories to allow (select your repository)
4. Find and select: `Inventory-and-Booking-System`
5. Click **"Connect"**

### 3.3 Configure Web Service

Fill in the deployment settings:

**Basic Settings:**
- **Name**: `subwfour-inventory` (this will be in your URL)
- **Region**: Choose closest to your location (e.g., Singapore, Oregon)
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
- **Free** (for testing - spins down after 15 min inactivity)
- **Starter** ($7/month - recommended for production, always on)

### 3.4 Add Environment Variables

Scroll down to **"Environment Variables"** section.

Click **"Add Environment Variable"** for each of these:

| Key | Value | Notes |
|-----|-------|-------|
| `APP_NAME` | `SubWFour Inventory` | Your app name |
| `APP_ENV` | `production` | Production environment |
| `APP_DEBUG` | `false` | Disable debug in production |
| `APP_KEY` | Leave blank for now | Will generate later |
| `APP_URL` | `https://subwfour-inventory.onrender.com` | Replace with your service name |
| `LOG_CHANNEL` | `stack` | Logging channel |
| `DB_CONNECTION` | `mysql` | Database type |
| `DB_HOST` | Paste from Step 1 | MySQL host address |
| `DB_PORT` | `3306` | MySQL port |
| `DB_DATABASE` | Paste from Step 1 | Database name |
| `DB_USERNAME` | Paste from Step 1 | Database username |
| `DB_PASSWORD` | Paste from Step 1 | Database password |
| `SESSION_DRIVER` | `file` | Session storage |
| `SESSION_LIFETIME` | `120` | Session timeout (minutes) |

**Important**: Make sure to replace `subwfour-inventory` in `APP_URL` with your actual service name!

### 3.5 Create Web Service

1. Scroll to bottom
2. Click **"Create Web Service"**
3. Render will start building and deploying
4. Watch the logs - deployment takes 5-10 minutes
5. Wait for **"Your service is live 🎉"** message

---

## 🔑 Step 4: Generate Application Key

After deployment completes:

1. Go to your service dashboard in Render
2. Click **"Shell"** tab (on the left sidebar)
3. Wait for shell to load
4. Run this command:
   ```bash
   php artisan key:generate --show
   ```
5. Copy the generated key (looks like: `base64:xxxxxxxxxxxxx`)
6. Go to **"Environment"** tab
7. Find `APP_KEY` variable
8. Click **"Edit"** (pencil icon)
9. Paste the generated key
10. Click **"Save Changes"**
11. Service will automatically redeploy (takes ~2 minutes)

---

## 👤 Step 5: Create Admin User

You need to create an admin user to access the system.

### Method 1: Using Render Shell (Recommended)

1. Go to Render Dashboard → Your Service → **"Shell"** tab
2. Run:
   ```bash
   php artisan tinker
   ```
3. Then paste this:
   ```php
   \App\Models\User::create([
       'name' => 'Admin',
       'email' => 'admin@subwfour.com',
       'password' => bcrypt('password'),
       'role' => 'admin'
   ]);
   ```
4. Press Enter
5. Type `exit` and press Enter

### Method 2: Using MySQL Client

If you have access to your MySQL database directly:

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

**Default Credentials:**
- **Email**: `admin@subwfour.com`
- **Password**: `password`

**⚠️ IMPORTANT**: Change this password immediately after first login!

---

## ✅ Step 6: Test Your Application

### 6.1 Access Your Application

1. Click the URL at the top of your Render service dashboard
   - Example: `https://subwfour-inventory.onrender.com`
2. You should see your login page!

### 6.2 Login and Change Password

1. Login with: `admin@subwfour.com` / `password`
2. **Immediately change the password**:
   - Go to your profile/settings
   - Update password to something secure

### 6.3 Test Features

1. **Dashboard**: Check if metrics load
2. **Inventory**: Add a test item
3. **Services**: Create a test service
4. **Bookings**: Check booking management
5. **Booking Portal**: Visit `/booking` - test public booking form
6. **Payments**: Test payment creation
7. **Reports**: Check activity logs

---

## 🔄 Step 7: Making Updates

### How to Update Your Live Application

```bash
# 1. Make changes locally
# Edit your code, test locally

# 2. Commit changes
git add .
git commit -m "Description of what you changed"

# 3. Push to GitHub
git push origin main

# 4. Render automatically deploys!
# Watch deployment in Render dashboard
# Takes 2-5 minutes
```

### Automatic Deployment

- ✅ Render detects your GitHub push
- ✅ Automatically rebuilds application
- ✅ Runs migrations
- ✅ Deploys new version
- ✅ Zero downtime!

---

## 🛠️ Troubleshooting

### Issue: "Application Key Not Set"

**Solution:**
1. Go to Render Dashboard → Your Service → **Shell**
2. Run: `php artisan key:generate --show`
3. Copy the key
4. Go to **Environment** tab
5. Edit `APP_KEY` and paste the key
6. Save changes

### Issue: "Database Connection Failed"

**Solution:**
1. Verify MySQL credentials in Environment Variables:
   - Check `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
2. Test connection from Shell:
   ```bash
   php artisan migrate:status
   ```
3. Make sure MySQL server allows external connections
4. For Railway: Use the public host, not localhost
5. For PlanetScale: Ensure SSL is configured if required

### Issue: "500 Internal Server Error"

**Solution:**
1. Check logs: Render Dashboard → Your Service → **Logs**
2. Look for error messages
3. Common fixes:
   - Set `APP_DEBUG=true` temporarily to see detailed errors
   - Run migrations: Go to Shell → `php artisan migrate --force`
   - Clear cache: `php artisan cache:clear`

### Issue: "Storage Link Not Working"

**Solution:**
1. Go to Shell tab
2. Run: `php artisan storage:link`

### Issue: "Page Not Found" or "404 Error"

**Solution:**
1. Clear route cache:
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

### Issue: "Build Failed"

**Solution:**
1. Check build logs in Render
2. Verify `render-build.sh` exists and is executable
3. Check for syntax errors in code
4. Ensure all dependencies are in `composer.json`

---

## 📊 Monitoring Your Application

### View Logs

1. Render Dashboard → Your Service → **"Logs"** tab
2. See real-time application logs
3. Filter by date/time
4. Look for errors or warnings

### Check Metrics

1. Render Dashboard → Your Service → **"Metrics"** tab
2. View:
   - CPU usage
   - Memory usage
   - Request count
   - Response times
   - Bandwidth

### Database Backups

**Important**: Set up regular backups!

1. Use your built-in backup system at `/backups` (admin only)
2. Download backups regularly
3. Store them securely offline
4. Also use your MySQL provider's backup features

---

## 💰 Pricing Estimate

### Render Web Service
- **Free Tier**: $0/month
  - ✅ Good for testing
  - ⚠️ Spins down after 15 minutes of inactivity
  - ⚠️ Takes ~30 seconds to wake up on first request
  - ⚠️ 750 hours/month limit
  
- **Starter Tier**: $7/month
  - ✅ Always on - no spin down
  - ✅ Instant response
  - ✅ Better for production
  - ✅ Recommended for business use

### MySQL Database
- **Railway**: Free tier ($5/month credit)
- **PlanetScale**: Free tier (5GB storage)
- **External Provider**: Varies ($5-20/month)

**Recommended for Production**: ~$7-15/month total

---

## 🔒 Security Best Practices

### After Deployment

1. ✅ Change default admin password immediately
2. ✅ Set `APP_DEBUG=false` in production (already done)
3. ✅ Use strong database passwords
4. ✅ Enable HTTPS (Render provides free SSL automatically)
5. ✅ Regular backups using built-in system
6. ✅ Keep dependencies updated
7. ✅ Review activity logs regularly

### Environment Variables

- ✅ Never commit `.env` file to GitHub
- ✅ Use Render's environment variables for sensitive data
- ✅ Rotate database passwords periodically
- ✅ Use different passwords for different environments

---

## 📱 Accessing Your Application

### Your URLs

- **Admin System**: `https://subwfour-inventory.onrender.com`
- **Login Page**: `https://subwfour-inventory.onrender.com/login`
- **Booking Portal**: `https://subwfour-inventory.onrender.com/booking`
- **Dashboard**: `https://subwfour-inventory.onrender.com/system`

### Share with Users

- **Customers**: Share the booking portal URL (`/booking`)
- **Staff**: Provide login credentials for the admin system
- **Admin**: Use the admin account for full system management

---

## 🎯 Next Steps Checklist

- [ ] Set up MySQL database (Railway/PlanetScale)
- [ ] Create GitHub repository
- [ ] Push code to GitHub
- [ ] Create Render account
- [ ] Deploy web service on Render
- [ ] Add environment variables
- [ ] Generate APP_KEY
- [ ] Create admin user
- [ ] Login and change password
- [ ] Test all features
- [ ] Set up regular backups
- [ ] Share booking portal URL with customers
- [ ] Train staff on the system
- [ ] Monitor logs and metrics

---

## 📞 Support Resources

### If You Need Help

1. **Check Logs First**: Render Dashboard → Logs tab
2. **Review This Guide**: Step-by-step instructions
3. **Render Documentation**: https://render.com/docs
4. **Render Support**: Contact via dashboard (very responsive)
5. **MySQL Provider Support**: Railway/PlanetScale have good docs

### Useful Commands (in Render Shell)

```bash
# Check database connection
php artisan migrate:status

# Run migrations
php artisan migrate --force

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Create user
php artisan tinker

# Check application status
php artisan --version
```

---

## 🎉 Congratulations!

Your SubWFour Inventory & Booking System is now live on the internet!

**What You've Accomplished:**
- ✅ Deployed Laravel application to Render
- ✅ Connected to MySQL database
- ✅ Set up automatic deployments from GitHub
- ✅ Configured environment variables
- ✅ Created admin account
- ✅ Free SSL certificate (HTTPS)
- ✅ Professional hosting

**Your Application is Ready for Production!** 🚀

### Start Using Your System:

1. **Admin System**: Manage inventory, services, bookings, payments
2. **Booking Portal**: Share with customers for online bookings
3. **Reports**: Track activity and generate reports
4. **Backups**: Regular backups for data safety

**Share your booking portal URL with customers and start taking bookings!**

---

## 📝 Notes

- First deployment takes 5-10 minutes
- Subsequent deployments take 2-5 minutes
- Free tier spins down after 15 min - use Starter for production
- Render provides automatic SSL certificates
- Database credentials are stored securely in environment variables
- All traffic is encrypted with HTTPS

**Good luck with your deployment!** 🎊
