# Hostinger Deployment Guide for Laravel Project

This guide will walk you through deploying your RD Agent Management System to Hostinger as a subdomain.

## Prerequisites
- Hostinger hosting account
- Domain name (for creating subdomain)
- FTP client (FileZilla, Cyberduck, or Hostinger File Manager)
- SSH access (recommended)

## Step 1: Create Subdomain in Hostinger

1. Log in to your Hostinger control panel
2. Go to **Hosting** → **Manage** → **Domains**
3. Click on **Subdomains**
4. Create a new subdomain (e.g., `rdagent.yourdomain.com`)
5. Note the document root path (usually something like `/public_html/rdagent`)

## Step 2: Database Setup

1. In Hostinger control panel, go to **Databases** → **MySQL Databases**
2. Create a new database (e.g., `rdagent_prod`)
3. Create a database user and assign it to the database
4. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

## Step 3: Prepare Your Project for Production

### Update Environment Configuration

Create a production-ready `.env` file:

```env
APP_NAME=RDAGENT
APP_ENV=production
APP_KEY=base64:L+2xxjqBXPTJ1SGkoF0R0U53+LZuRNHVAwGjo4qKv0o=
APP_DEBUG=false
APP_URL=https://rdagent.yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_email@gmail.com"
MAIL_FROM_NAME="RD Agent"

# Remove or comment out development-specific settings
```

### Generate New Application Key
```bash
php artisan key:generate
```

## Step 4: Upload Files to Hostinger

### Method A: Using FTP
1. Connect to your Hostinger FTP using credentials from hosting panel
2. Upload all files to your subdomain's document root
3. **Important**: The `public` folder contents should be in the document root
4. All other files should be one level above (outside web root)

### Method B: Using Git (Recommended)
1. Initialize git in your project: `git init`
2. Add remote: `git remote add production ssh://username@hostinger-server:/path/to/your/subdomain`
3. Push: `git push production main`

### File Structure on Server:
```
/public_html/rdagent/          # Document root (public folder contents)
  ├── index.php
  ├── .htaccess
  ├── css/
  ├── js/
  └── storage/ -> ../storage/app/public

/above_web_root/               # Outside document root
  ├── app/
  ├── bootstrap/
  ├── config/
  ├── database/
  ├── resources/
  ├── routes/
  ├── storage/
  ├── vendor/
  ├── .env
  └── artisan
```

## Step 5: Configure .htaccess for Hostinger

Your existing `.htaccess` is good, but here's an optimized version for Hostinger:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Handle Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]

    # Enable Gzip Compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/x-javascript application/json
    </IfModule>

    # Set caching headers
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType image/jpg "access plus 1 month"
        ExpiresByType image/jpeg "access plus 1 month"
        ExpiresByType image/gif "access plus 1 month"
        ExpiresByType image/png "access plus 1 month"
        ExpiresByType text/css "access plus 1 month"
        ExpiresByType application/pdf "access plus 1 month"
        ExpiresByType text/javascript "access plus 1 month"
        ExpiresByType application/javascript "access plus 1 month"
        ExpiresByType application/x-javascript "access plus 1 month"
        ExpiresByType application/x-shockwave-flash "access plus 1 month"
        ExpiresByType image/x-icon "access plus 1 year"
        ExpiresDefault "access plus 2 days"
    </IfModule>
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

## Step 6: Server Configuration

### Update index.php for Correct Paths
Edit `public/index.php` to point to the correct paths:

```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

### Set Proper Permissions via SSH:
```bash
# Connect to your server via SSH
ssh username@yourdomain.com

# Navigate to your project directory
cd /path/to/your/project

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Step 7: Install Dependencies and Optimize

```bash
# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Install npm dependencies (if needed)
npm install && npm run build

# Generate optimized autoload files
composer dump-autoload -o

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Run migrations
php artisan migrate --force

# Link storage
php artisan storage:link
```

## Step 8: SSL Certificate (HTTPS)

1. In Hostinger control panel, go to **SSL**
2. Enable SSL for your subdomain
3. Your site will automatically redirect to HTTPS

## Step 9: Test Your Application

1. Visit your subdomain: `https://rdagent.yourdomain.com`
2. Test all functionality:
   - User registration/login
   - Database operations
   - File uploads
   - Email functionality

## Step 10: Monitoring and Maintenance

### Create Deployment Script
Create `deploy.sh` for easy future deployments:

```bash
#!/bin/bash
echo "Deploying RD Agent to production..."

# Pull latest changes
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev

# Build assets
npm install && npm run build

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

echo "Deployment completed successfully!"
```

### Set up Cron Jobs
In Hostinger control panel → **Cron Jobs**:
```
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

## Troubleshooting Common Issues

### 500 Internal Server Error
- Check file permissions
- Verify .env configuration
- Check PHP version (Laravel 10 requires PHP 8.1+)

### Database Connection Issues
- Verify database credentials in .env
- Check if database user has proper permissions

### File Permission Issues
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### White Screen of Death
- Check error logs in `storage/logs/laravel.log`
- Run `php artisan optimize:clear`

## Security Considerations

1. **Keep .env secure**: Never commit .env to version control
2. **Regular updates**: Keep Laravel and dependencies updated
3. **Backups**: Set up regular database and file backups
4. **Monitoring**: Monitor error logs regularly

## Support Resources

- Hostinger Support: https://www.hostinger.com/help
- Laravel Documentation: https://laravel.com/docs
- Server Status: Check via Hostinger dashboard

Your RD Agent Management System should now be successfully deployed on Hostinger as a subdomain!
