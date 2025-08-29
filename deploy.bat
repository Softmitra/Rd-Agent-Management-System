@echo off
echo 🚀 Deploying RD Agent Management System to Production
echo ======================================================

echo Step 1: Pulling latest changes...
git pull origin main
if %errorlevel% neq 0 (
    echo ✗ Failed: Git pull
    exit /b 1
)
echo ✓ Success: Git pull completed

echo Step 2: Installing Composer dependencies...
composer install --optimize-autoloader --no-dev
if %errorlevel% neq 0 (
    echo ✗ Failed: Composer dependencies
    exit /b 1
)
echo ✓ Success: Composer dependencies installed

echo Step 3: Installing NPM dependencies...
npm install --silent
if %errorlevel% neq 0 (
    echo ✗ Failed: NPM dependencies
    exit /b 1
)
echo ✓ Success: NPM dependencies installed

echo Step 4: Building assets...
npm run build --silent
if %errorlevel% neq 0 (
    echo ✗ Failed: Assets build
    exit /b 1
)
echo ✓ Success: Assets built

echo Step 5: Optimizing autoloader...
composer dump-autoload -o
if %errorlevel% neq 0 (
    echo ✗ Failed: Autoloader optimization
    exit /b 1
)
echo ✓ Success: Autoloader optimized

echo Step 6: Caching configuration...
php artisan config:cache
if %errorlevel% neq 0 (
    echo ✗ Failed: Configuration cache
    exit /b 1
)
echo ✓ Success: Configuration cached

echo Step 7: Caching routes...
php artisan route:cache
if %errorlevel% neq 0 (
    echo ✗ Failed: Routes cache
    exit /b 1
)
echo ✓ Success: Routes cached

echo Step 8: Caching views...
php artisan view:cache
if %errorlevel% neq 0 (
    echo ✗ Failed: Views cache
    exit /b 1
)
echo ✓ Success: Views cached

echo Step 9: Running migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo ✗ Failed: Migrations
    exit /b 1
)
echo ✓ Success: Migrations completed

echo Step 10: Linking storage...
php artisan storage:link
if %errorlevel% neq 0 (
    echo ✗ Failed: Storage link
    exit /b 1
)
echo ✓ Success: Storage linked

echo Step 11: Clearing cache...
php artisan optimize:clear
if %errorlevel% neq 0 (
    echo ✗ Failed: Cache clear
    exit /b 1
)
echo ✓ Success: Cache cleared

echo ======================================================
echo 🎉 Deployment completed successfully!
echo Your RD Agent Management System is now live!
echo ======================================================

echo.
echo Next steps:
echo 1. Test your application at your subdomain URL
echo 2. Check error logs if any issues occur
echo 3. Monitor performance and functionality

pause
