#!/bin/bash
echo "🚀 Deploying RD Agent Management System to Production"
echo "======================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to check if command succeeded
check_success() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ Success: $1${NC}"
    else
        echo -e "${RED}✗ Failed: $1${NC}"
        exit 1
    fi
}

echo -e "${YELLOW}Step 1: Pulling latest changes...${NC}"
git pull origin main
check_success "Git pull completed"

echo -e "${YELLOW}Step 2: Installing Composer dependencies...${NC}"
composer install --optimize-autoloader --no-dev
check_success "Composer dependencies installed"

echo -e "${YELLOW}Step 3: Installing NPM dependencies...${NC}"
npm install --silent
check_success "NPM dependencies installed"

echo -e "${YELLOW}Step 4: Building assets...${NC}"
npm run build --silent
check_success "Assets built"

echo -e "${YELLOW}Step 5: Optimizing autoloader...${NC}"
composer dump-autoload -o
check_success "Autoloader optimized"

echo -e "${YELLOW}Step 6: Caching configuration...${NC}"
php artisan config:cache
check_success "Configuration cached"

echo -e "${YELLOW}Step 7: Caching routes...${NC}"
php artisan route:cache
check_success "Routes cached"

echo -e "${YELLOW}Step 8: Caching views...${NC}"
php artisan view:cache
check_success "Views cached"

echo -e "${YELLOW}Step 9: Running migrations...${NC}"
php artisan migrate --force
check_success "Migrations completed"

echo -e "${YELLOW}Step 10: Linking storage...${NC}"
php artisan storage:link
check_success "Storage linked"

echo -e "${YELLOW}Step 11: Clearing cache...${NC}"
php artisan optimize:clear
check_success "Cache cleared"

echo -e "${YELLOW}Step 12: Setting permissions...${NC}"
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
check_success "Permissions set"

echo -e "${GREEN}======================================================${NC}"
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${GREEN}Your RD Agent Management System is now live!${NC}"
echo -e "${GREEN}======================================================${NC}"

# Optional: Restart PHP-FPM if needed
# echo -e "${YELLOW}Restarting PHP-FPM...${NC}"
# sudo systemctl restart php8.1-fpm

echo ""
echo "Next steps:"
echo "1. Test your application at your subdomain URL"
echo "2. Check error logs if any issues occur"
echo "3. Monitor performance and functionality"
