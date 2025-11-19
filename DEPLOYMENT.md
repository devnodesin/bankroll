# Bankroll - Docker Deployment Guide

This guide explains how to deploy Bankroll using Docker and FrankenPHP.

## Prerequisites

- Docker Engine 20.10 or later
- Docker Compose v2.0 or later
- At least 512MB of available RAM
- 1GB of available disk space

## Quick Start

### 1. Generate Application Key

Before deploying, you need to generate a unique application key:

```bash
# Using PHP locally (if available)
php src/artisan key:generate --show

# OR using Docker temporarily
docker run --rm -v $(pwd)/src:/app -w /app composer:latest php artisan key:generate --show
```

Copy the generated key (e.g., `base64:random_string_here`).

### 2. Configure Environment Variables

Edit the `docker-compose.yml` file and update the following:

```yaml
environment:
  APP_KEY: "base64:YOUR_GENERATED_KEY_HERE"  # Replace with generated key
  APP_URL: "http://your-domain.com"          # Replace with your domain
  APP_DEBUG: "false"                         # Keep false for production
  CURRENCY_SYMBOL: "₹"                       # Change if needed (e.g., "$", "€")
```

### 3. Prepare Data Directory

Create the data directory for database and logs:

```bash
mkdir -p data/logs
```

### 4. Initialize Database

Create an empty SQLite database file:

```bash
touch data/database.sqlite
chmod 666 data/database.sqlite
```

### 5. Build and Start Containers

```bash
# Build the Docker image
docker-compose build

# Start the container
docker-compose up -d
```

### 6. Run Database Migrations

```bash
# Run migrations to set up the database schema
docker-compose exec bankroll php artisan migrate --force

# (Optional) Seed with sample categories
docker-compose exec bankroll php artisan db:seed --force
```

### 7. Create Admin User

```bash
# Create your first user
docker-compose exec bankroll php artisan user:add admin your_password
```

### 8. Access the Application

Open your browser and navigate to:
- HTTP: `http://localhost:8000`
- HTTPS: `https://localhost:8443` (self-signed certificate)

Login with the credentials you created in step 7.

## Production Deployment

### Environment Configuration

For production, ensure these settings in `docker-compose.yml`:

```yaml
environment:
  APP_ENV: "production"
  APP_DEBUG: "false"
  LOG_LEVEL: "error"
```

### Security Considerations

1. **Generate a strong APP_KEY**: Never use default or example keys
2. **Use HTTPS**: Configure a proper SSL certificate for production
3. **Strong passwords**: Use strong passwords for user accounts
4. **File permissions**: Ensure the SQLite database file has appropriate permissions
5. **Regular backups**: Backup the `data/database.sqlite` file regularly

### Reverse Proxy Setup (Recommended)

For production, it's recommended to run Bankroll behind a reverse proxy (like Nginx or Caddy) for SSL termination and additional security.

Example Nginx configuration:

```nginx
server {
    listen 80;
    server_name bankroll.example.com;
    
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## Container Management

### View Logs

```bash
# View all logs
docker-compose logs

# Follow logs in real-time
docker-compose logs -f

# View only Bankroll logs
docker-compose logs -f bankroll
```

### Stop Container

```bash
# Stop the container
docker-compose stop

# Stop and remove containers
docker-compose down
```

### Restart Container

```bash
docker-compose restart
```

### Update Application

```bash
# Pull latest changes
git pull

# Rebuild and restart
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Run any new migrations
docker-compose exec bankroll php artisan migrate --force
```

## User Management

```bash
# Add a new user
docker-compose exec bankroll php artisan user:add username password

# Add user with email
docker-compose exec bankroll php artisan user:add username password --email=user@example.com

# Remove a user
docker-compose exec bankroll php artisan user:remove username

# List all users
docker-compose exec bankroll php artisan user:list
```

## Maintenance

### Backup Database

```bash
# Create a backup
cp data/database.sqlite data/database.sqlite.backup.$(date +%Y%m%d_%H%M%S)

# Or use docker cp
docker cp bankroll:/var/www/html/database/database.sqlite ./backup-$(date +%Y%m%d_%H%M%S).sqlite
```

### Restore Database

```bash
# Stop the container
docker-compose stop

# Restore from backup
cp data/database.sqlite.backup.YYYYMMDD_HHMMSS data/database.sqlite

# Start the container
docker-compose start
```

### Clear Cache

```bash
# Clear all caches
docker-compose exec bankroll php artisan cache:clear
docker-compose exec bankroll php artisan config:clear
docker-compose exec bankroll php artisan view:clear
```

### Optimize for Production

```bash
# Optimize the application
docker-compose exec bankroll php artisan optimize
```

## Troubleshooting

### Container won't start

1. Check logs: `docker-compose logs bankroll`
2. Verify APP_KEY is set in docker-compose.yml
3. Ensure data directory exists and has proper permissions

### Database errors

1. Check database file exists: `ls -la data/database.sqlite`
2. Verify permissions: `chmod 666 data/database.sqlite`
3. Run migrations: `docker-compose exec bankroll php artisan migrate --force`

### Permission denied errors

```bash
# Fix storage permissions
docker-compose exec -u root bankroll chown -R www-data:www-data storage bootstrap/cache database
docker-compose exec -u root bankroll chmod -R 775 storage bootstrap/cache database
```

### Cannot access application

1. Check if container is running: `docker-compose ps`
2. Verify port bindings: `docker-compose port bankroll 80`
3. Check firewall settings
4. Review logs: `docker-compose logs bankroll`

## Docker Image Details

### FrankenPHP

This deployment uses [FrankenPHP](https://frankenphp.dev/), a modern PHP application server built on top of Caddy:

- Built-in HTTPS support with automatic certificates
- HTTP/2 and HTTP/3 support
- High performance with worker mode
- Zero configuration for most Laravel applications

### Build Process

The Dockerfile uses a multi-stage build:

1. **Builder Stage**: Installs dependencies, compiles assets
   - PHP 8.3 with required extensions
   - Composer for PHP dependencies
   - Node.js for frontend asset compilation
   
2. **Final Stage**: Production-ready image
   - FrankenPHP application server
   - Only runtime dependencies
   - Optimized PHP settings
   - Proper file permissions

### Resource Usage

- **Memory**: ~100-200MB RAM typical usage
- **CPU**: Low usage, scales with concurrent requests
- **Disk**: ~150MB for Docker image, plus your database size

## Advanced Configuration

### Custom PHP Settings

Create a custom PHP configuration file:

```bash
# Create custom-php.ini
cat > custom-php.ini << 'EOF'
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 60
EOF
```

Update `docker-compose.yml` to mount it:

```yaml
volumes:
  - ./custom-php.ini:/usr/local/etc/php/conf.d/custom.ini:ro
```

### Environment-Specific Configurations

Create multiple compose files:

- `docker-compose.yml` - Base configuration
- `docker-compose.prod.yml` - Production overrides
- `docker-compose.dev.yml` - Development overrides

Use with: `docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d`

## Support

For issues and questions:
- GitHub Issues: https://github.com/devnodesin/bankroll/issues
- Documentation: https://github.com/devnodesin/bankroll

Built with ❤️ by [Devnodes.in](https://devnodes.in)
