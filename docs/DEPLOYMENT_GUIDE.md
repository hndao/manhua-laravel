# Deployment Guide

## Overview

This guide covers deploying the Manhua Reader Laravel backend to production environments.

## Prerequisites

### Server Requirements

- **PHP**: >= 8.2
- **Web Server**: Nginx or Apache
- **Database**: MySQL 8.0+ or MariaDB 10.3+
- **Memory**: Minimum 512MB RAM (1GB+ recommended)
- **Storage**: Depends on image storage strategy

### Required PHP Extensions

- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML

## Deployment Steps

### 1. Server Setup

#### Install PHP 8.2+

**Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip
```

#### Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### Install MySQL

```bash
sudo apt install mysql-server
sudo mysql_secure_installation
```

### 2. Application Deployment

#### Clone Repository

```bash
cd /var/www
git clone https://github.com/yourusername/manhua-laravel.git
cd manhua-laravel
```

#### Install Dependencies

```bash
composer install --optimize-autoloader --no-dev
```

#### Configure Environment

```bash
cp .env.example .env
nano .env
```

**Production .env Configuration:**
```env
APP_NAME="Manhua Reader"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=manhua_production
DB_USERNAME=manhua_user
DB_PASSWORD=secure_password_here

SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
SESSION_DOMAIN=.yourdomain.com

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### Generate Application Key

```bash
php artisan key:generate
```

#### Run Migrations

```bash
php artisan migrate --force
```

#### Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/manhua-laravel
sudo chmod -R 755 /var/www/manhua-laravel
sudo chmod -R 775 /var/www/manhua-laravel/storage
sudo chmod -R 775 /var/www/manhua-laravel/bootstrap/cache
```

### 3. Web Server Configuration

#### Nginx Configuration

Create `/etc/nginx/sites-available/manhua-api`:

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    root /var/www/manhua-laravel/public;

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
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/manhua-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 4. SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d api.yourdomain.com
```

### 5. Database Setup

#### Create Database and User

```sql
CREATE DATABASE manhua_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'manhua_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON manhua_production.* TO 'manhua_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Import Data (if migrating)

```bash
mysql -u manhua_user -p manhua_production < backup.sql
```

### 6. Redis Setup (Optional but Recommended)

```bash
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### 7. Queue Worker Setup

#### Create Supervisor Configuration

Create `/etc/supervisor/conf.d/manhua-worker.conf`:

```ini
[program:manhua-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/manhua-laravel/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/manhua-laravel/storage/logs/worker.log
stopwaitsecs=3600
```

Start worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start manhua-worker:*
```

### 8. Scheduled Tasks (Cron)

Add to crontab:
```bash
sudo crontab -e -u www-data
```

Add line:
```
* * * * * cd /var/www/manhua-laravel && php artisan schedule:run >> /dev/null 2>&1
```

## Security Hardening

### 1. Environment Security

- Set `APP_DEBUG=false` in production
- Use strong `APP_KEY`
- Secure database credentials
- Use HTTPS only

### 2. File Permissions

```bash
# Application files: read-only for web server
sudo chmod -R 755 /var/www/manhua-laravel

# Storage and cache: writable
sudo chmod -R 775 /var/www/manhua-laravel/storage
sudo chmod -R 775 /var/www/manhua-laravel/bootstrap/cache
```

### 3. Firewall Configuration

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 4. Rate Limiting

Laravel Sanctum includes rate limiting. Configure in `app/Http/Kernel.php`:

```php
'api' => [
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

## Monitoring & Logging

### 1. Application Logs

Location: `/var/www/manhua-laravel/storage/logs/laravel.log`

Monitor with:
```bash
tail -f /var/www/manhua-laravel/storage/logs/laravel.log
```

### 2. Error Tracking

Consider integrating:
- Sentry
- Bugsnag
- Rollbar

### 3. Performance Monitoring

Consider:
- New Relic
- Datadog
- Laravel Telescope (development only)

## Backup Strategy

### 1. Database Backup

Daily backup script:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/manhua"
mkdir -p $BACKUP_DIR

mysqldump -u manhua_user -p'password' manhua_production > $BACKUP_DIR/db_$DATE.sql
gzip $BACKUP_DIR/db_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete
```

Add to crontab:
```bash
0 2 * * * /path/to/backup-script.sh
```

### 2. File Backup

Backup storage directory:
```bash
tar -czf storage_backup_$(date +%Y%m%d).tar.gz /var/www/manhua-laravel/storage
```

## Scaling Considerations

### 1. Database Optimization

- Enable query caching
- Add indexes for frequently queried columns
- Use read replicas for heavy read operations

### 2. Caching Strategy

- Cache API responses
- Cache database queries
- Use Redis for session and cache storage

### 3. Load Balancing

For high traffic:
- Multiple application servers behind load balancer
- Separate database server
- CDN for static assets and images

### 4. Image Storage

Consider using:
- AWS S3
- DigitalOcean Spaces
- Cloudinary
- Local CDN

## Troubleshooting

### Common Issues

**1. 500 Internal Server Error**
- Check Laravel logs: `storage/logs/laravel.log`
- Check web server error logs
- Verify file permissions

**2. Database Connection Error**
- Verify database credentials in `.env`
- Check MySQL is running: `sudo systemctl status mysql`
- Test connection: `mysql -u manhua_user -p`

**3. CORS Errors**
- Update `config/cors.php`
- Add frontend domain to `SANCTUM_STATEFUL_DOMAINS`

**4. Performance Issues**
- Enable caching: `php artisan config:cache`
- Optimize autoloader: `composer dump-autoload --optimize`
- Use Redis for cache and sessions

## Maintenance

### Regular Tasks

**Daily:**
- Monitor error logs
- Check disk space
- Verify backups

**Weekly:**
- Review performance metrics
- Update dependencies (security patches)
- Clean old logs

**Monthly:**
- Database optimization
- Security audit
- Performance review

### Updates

```bash
# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl reload php8.2-fpm
sudo supervisorctl restart manhua-worker:*
```

## Rollback Procedure

If deployment fails:

```bash
# Revert code
git reset --hard HEAD~1

# Rollback migrations
php artisan migrate:rollback

# Clear cache
php artisan config:clear
php artisan cache:clear

# Restore from backup if needed
mysql -u manhua_user -p manhua_production < backup.sql
```

## Support

For deployment issues:
- Check Laravel documentation
- Review server logs
- Contact DevOps team
- Check community forums

