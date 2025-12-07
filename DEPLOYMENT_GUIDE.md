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

### 2.1 Initialize Git

```bash
cd e:\IT12\Inventory-and-Booking-System
git init
```

### 2.2 Add and Commit Files

```bash
git add .
git commit -m "Initial commit with Docker configuration"
```

### 2.3 Create GitHub Repository

1. Go to https://github.com
2. Click **"+"** → **"New repository"**
3. Name: `Inventory-and-Booking-System`
4. Make it **Private**
5. **Don't** check any boxes
6. Click **"Create repository"**

### 2.4 Push to GitHub

```bash
# Add your repository (replace YOUR-USERNAME!)
git remote add origin https://github.com/YOUR-USERNAME/Inventory-and-Booking-System.git

# Push code
git branch -M main
git push -u origin main
```

**Need help with GitHub?** Use GitHub Desktop (easier):
1. Download: https://desktop.github.com
2. Add local repository
3. Publish to GitHub

---

## 🚀 Step 3: Deploy to Render with Docker

### 3.1 Create Render Account

1. Go to https://render.com
2. Sign up with GitHub
3. Verify email

### 3.2 Create Web Service

1. Click **"New +"** → **"Web Service"**
2. Click **"Connect a repository"**
3. Authorize Render to access your GitHub
4. Select: `Inventory-and-Booking-System`
5. Click **"Connect"**

### 3.3 Configure Service

**Basic Settings:**
- **Name**: `subwfour-inventory`
- **Region**: Choose closest (e.g., Singapore)
- **Branch**: `main`
- **Root Directory**: Leave blank
- **Environment**: **Docker**

**Docker Settings:**
- **Dockerfile Path**: `Dockerfile` (default)
- Render will automatically detect your Dockerfile!

**Instance Type:**
- **Free** (for testing - spins down after 15 min)
- **Starter** ($7/month - recommended for production)

### 3.4 Add Environment Variables

Click **"Add Environment Variable"** for each:

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
2. Render will:
   - Build your Docker image
   - Deploy the container
   - Start your application
3. Wait 5-10 minutes for first deployment
4. Watch the logs for progress

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

**Default password**: `password`

**⚠️ IMPORTANT**: Change this password immediately after first login!

---

## ✅ Step 6: Test Your Application

1. Visit your URL: `https://subwfour-inventory.onrender.com`
2. You should see the login page
3. Login with: `admin@subwfour.com` / `password`
4. **Change password immediately**
5. Test features:
   - Create bookings
   - Add inventory items
   - Test booking portal: `/booking`

---

## 🔄 Step 7: Making Updates

### Update Process

```bash
# 1. Make changes locally
# Edit your code

# 2. Test locally (optional)
docker build -t subwfour-test .
docker run -p 8080:8080 subwfour-test

# 3. Commit and push
git add .
git commit -m "Description of changes"
git push origin main

# 4. Render auto-deploys!
# Watch deployment in Render dashboard
```

### Automatic Deployment

- ✅ Render detects GitHub push
- ✅ Rebuilds Docker image
- ✅ Deploys new container
- ✅ Zero downtime deployment
- ✅ Takes 3-7 minutes

---

## 🛠️ Troubleshooting

### Issue: "Build Failed"

**Check Render Logs:**
1. Go to service dashboard
2. Click **"Logs"** tab
3. Look for error messages

**Common fixes:**
- Verify Dockerfile syntax
- Check if all files are committed to GitHub
- Ensure `docker/` folder is included

### Issue: "Application Error 503"

**Solution:**
1. Check if container is running: Logs tab
2. Verify environment variables are set
3. Check database connection:
   ```bash
   # In Shell tab
   php artisan migrate:status
   ```

### Issue: "Database Connection Failed"

**Solution:**
1. Verify all DB_* environment variables
2. Test connection from Shell:
   ```bash
   php artisan tinker
   DB::connection()->getPdo();
   ```
3. Check MySQL server allows external connections
4. Verify credentials are correct

### Issue: "Permission Denied"

**Solution:**
Already handled in Dockerfile, but if issues persist:
```bash
# In Shell tab
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

### Issue: "Storage Link Not Working"

**Solution:**
```bash
# In Shell tab
php artisan storage:link
```

---

## 📊 Monitoring

### View Logs

1. Render Dashboard → Your Service → **Logs**
2. Real-time application logs
3. Filter by time/severity

### Check Metrics

1. Render Dashboard → Your Service → **Metrics**
2. View:
   - CPU usage
   - Memory usage
   - Request count
   - Response times

### Health Checks

Render automatically monitors your service:
- ✅ HTTP health checks
- ✅ Auto-restart on failure
- ✅ Email notifications

---

## 🐳 Docker Benefits

### Why Docker?

✅ **Consistency**: Same environment everywhere
✅ **Isolation**: Dependencies contained
✅ **Portability**: Easy to move between hosts
✅ **Scalability**: Easy to scale up/down
✅ **Version Control**: Docker images are versioned

### Your Docker Setup

- **Base**: PHP 8.2 with FPM
- **Web Server**: Nginx
- **Database**: MySQL (external)
- **Port**: 8080
- **Auto-start**: Migrations, caching, storage link

---

## 💰 Pricing

### Render Web Service
- **Free**: $0/month (spins down after 15 min inactivity)
- **Starter**: $7/month (always on, recommended)

### MySQL Database
- **Railway**: Free tier ($5/month credit)
- **PlanetScale**: Free tier (5GB)
- **External**: Varies ($5-20/month)

**Recommended for Production**: ~$7-15/month total

---

## 🔒 Security Best Practices

### After Deployment

1. ✅ Change default admin password
2. ✅ Set `APP_DEBUG=false` in production
3. ✅ Use strong database passwords
4. ✅ Enable HTTPS (Render provides free SSL)
5. ✅ Regular backups using built-in system
6. ✅ Keep dependencies updated

### Environment Variables

- ✅ Never commit `.env` to GitHub
- ✅ Use Render's environment variables
- ✅ Rotate database passwords periodically

---

## 📱 Accessing Your Application

### URLs

- **Admin System**: `https://subwfour-inventory.onrender.com`
- **Booking Portal**: `https://subwfour-inventory.onrender.com/booking`
- **Login**: `https://subwfour-inventory.onrender.com/login`

### Share with Users

- **Customers**: Share booking portal URL
- **Staff**: Provide login credentials
- **Admin**: Use admin account for management

---

## 🎯 Next Steps

1. ✅ Deploy to Render with Docker
2. ✅ Set up MySQL database
3. ✅ Configure environment variables
4. ✅ Generate APP_KEY
5. ✅ Create admin user
6. ✅ Test all features
7. ✅ Change default password
8. ✅ Set up regular backups
9. ✅ Train staff on system
10. ✅ Share booking portal with customers

---

## 📞 Support

### If You Need Help

1. **Check Logs**: Render Dashboard → Logs
2. **Review This Guide**: Step-by-step instructions
3. **Render Docs**: https://render.com/docs/docker
4. **Render Support**: Very responsive and helpful

### Common Commands

```bash
# View logs
# (In Render Dashboard → Logs tab)

# Access shell
# (In Render Dashboard → Shell tab)

# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear

# Check database
php artisan migrate:status

# Create user
php artisan tinker
```

---

## 🎉 Congratulations!

Your SubWFour Inventory & Booking System is now live with Docker!

**Benefits of Your Setup:**
- ✅ Dockerized for consistency
- ✅ Auto-deployment from GitHub
- ✅ MySQL database
- ✅ Free SSL certificate
- ✅ Auto-scaling capability
- ✅ Professional hosting

**Your application is ready for production!** 🚀

Share your booking portal URL with customers and start taking bookings!
