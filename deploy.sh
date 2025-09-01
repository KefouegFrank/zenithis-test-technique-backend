#!/bin/bash

# Trip Management API Deployment Script
# For Proxmox Container Deployment

echo "🚀 Starting Trip Management API Deployment..."

# Update system packages
echo "📦 Updating system packages..."
apt update && apt upgrade -y

# Install required packages
echo "🔧 Installing required packages..."
apt install -y nginx php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd composer git unzip

# Create application directory
echo "📁 Creating application directory..."
mkdir -p /var/www/trip-api
cd /var/www/trip-api

# Clone repository
echo "📥 Cloning repository..."
git clone https://github.com/KefouegFrank/zenithis-test-technique-backend.git .

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev

# Set permissions
echo "🔐 Setting permissions..."
chown -R www-data:www-data /var/www/trip-api
chmod -R 755 /var/www/trip-api
chmod -R 775 /var/www/trip-api/storage
chmod -R 775 /var/www/trip-api/bootstrap/cache

# Copy environment file
echo "⚙️ Setting up environment..."
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear and cache configurations
echo "🧹 Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configure Nginx
echo "🌐 Configuring Nginx..."
cat > /etc/nginx/sites-available/trip-api << 'EOF'
server {
    listen 80;
    server_name _;
    root /var/www/trip-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable site
ln -sf /etc/nginx/sites-available/trip-api /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
nginx -t

# Restart services
echo "🔄 Restarting services..."
systemctl restart nginx
systemctl restart php8.2-fpm
systemctl enable nginx
systemctl enable php8.2-fpm

echo "✅ Deployment completed successfully!"
echo "🌐 Your API should be accessible at: http://$(hostname -I | awk '{print $1}')"
echo "📋 API Documentation: http://$(hostname -I | awk '{print $1}')/api/"
echo "❤️ Health Check: http://$(hostname -I | awk '{print $1}')/api/v1/health"
