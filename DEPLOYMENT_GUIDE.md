# 🚀 Trip Management API - Deployment Guide

## 📋 **Prerequisites**

- Proxmox access with Frank01 container (10.0.0.113)
- SSH access to the container
- Domain name (optional, for production)

## 🎯 **Deployment Steps**

### **Step 1: Access Proxmox Container**

1. **SSH into Frank01 container:**
   ```bash
   ssh root@10.0.0.113
   # Password: Frank01
   ```

2. **Verify container setup:**
   ```bash
   # Check if it's Ubuntu/Debian
   cat /etc/os-release
   
   # Update system
   apt update && apt upgrade -y
   ```

### **Step 2: Install Required Software**

```bash
# Install Nginx, PHP, MySQL, and other dependencies
apt install -y nginx php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd composer git unzip

# Install MySQL (if not already installed)
apt install -y mysql-server

# Secure MySQL installation
mysql_secure_installation
```

### **Step 3: Setup Database**

```bash
# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE trip_management;
CREATE USER 'trip_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON trip_management.* TO 'trip_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### **Step 4: Deploy Application**

```bash
# Create application directory
mkdir -p /var/www/trip-api
cd /var/www/trip-api

# Clone repository
git clone https://github.com/KefouegFrank/zenithis-test-technique-backend.git .

# Install dependencies
composer install --optimize-autoloader --no-dev

# Set permissions
chown -R www-data:www-data /var/www/trip-api
chmod -R 755 /var/www/trip-api
chmod -R 775 /var/www/trip-api/storage
chmod -R 775 /var/www/trip-api/bootstrap/cache
```

### **Step 5: Configure Environment**

```bash
# Copy environment file
cp .env.example .env

# Edit environment file
nano .env
```

**Update these values in .env:**
```env
APP_NAME="Trip Management API"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://10.0.0.113

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=trip_management
DB_USERNAME=trip_user
DB_PASSWORD=secure_password_here

JWT_SECRET=your_jwt_secret_here
```

### **Step 6: Initialize Application**

```bash
# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Run migrations
php artisan migrate --force

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 7: Configure Nginx**

```bash
# Create Nginx configuration
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

# Test configuration
nginx -t

# Restart services
systemctl restart nginx
systemctl restart php8.2-fpm
systemctl enable nginx
systemctl enable php8.2-fpm
```

## 🧪 **Testing Deployment**

### **Test API Endpoints:**

1. **Health Check:**
   ```bash
   curl http://10.0.0.113/api/v1/health
   ```

2. **API Documentation:**
   ```bash
   curl http://10.0.0.113/api/
   ```

3. **User Registration:**
   ```bash
   curl -X POST http://10.0.0.113/api/v1/auth/register \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Test User",
       "email": "test@example.com",
       "password": "password123",
       "password_confirmation": "password123",
       "phone": "+1234567890",
       "address": "123 Test Street"
     }'
   ```

## 🔧 **Troubleshooting**

### **Common Issues:**

1. **Permission Errors:**
   ```bash
   chown -R www-data:www-data /var/www/trip-api
   chmod -R 775 /var/www/trip-api/storage
   chmod -R 775 /var/www/trip-api/bootstrap/cache
   ```

2. **Database Connection:**
   ```bash
   # Check MySQL status
   systemctl status mysql
   
   # Test connection
   mysql -u trip_user -p trip_management
   ```

3. **Nginx Issues:**
   ```bash
   # Check Nginx status
   systemctl status nginx
   
   # Check error logs
   tail -f /var/log/nginx/error.log
   ```

4. **PHP Issues:**
   ```bash
   # Check PHP-FPM status
   systemctl status php8.2-fpm
   
   # Check PHP error logs
   tail -f /var/log/php8.2-fpm.log
   ```

## 📊 **Monitoring**

### **Check Application Status:**
```bash
# Check services
systemctl status nginx
systemctl status php8.2-fpm
systemctl status mysql

# Check application logs
tail -f /var/www/trip-api/storage/logs/laravel.log

# Check Nginx access logs
tail -f /var/log/nginx/access.log
```

## 🎯 **Success Indicators**

✅ **API Health Check:** `http://10.0.0.113/api/v1/health` returns 200  
✅ **User Registration:** Can create new users  
✅ **JWT Authentication:** Tokens are generated and validated  
✅ **Database:** Migrations run successfully  
✅ **Nginx:** Serves PHP files correctly  

## 🚀 **Next Steps**

After successful deployment:
1. **Test all API endpoints** using Postman
2. **Set up SSL certificate** (Let's Encrypt)
3. **Configure domain name** (if available)
4. **Set up monitoring** and logging
5. **Create frontend application**

## 📞 **Support**

If you encounter issues:
1. Check the logs mentioned above
2. Verify all services are running
3. Test database connectivity
4. Ensure proper file permissions

Your API should be accessible at: **http://10.0.0.113**
