# Bankroll Docker Quick Start & Deployment Guide

Get Bankroll running with Docker and FrankenPHP in minutes.

---

## Prerequisites

- Docker Engine 20.10+
- Docker Compose v2.0+
- 512MB+ RAM, 1GB+ disk space

---

## Quick Start (5 Steps)

### 1. Clone the Repository

```bash
git clone https://github.com/devnodesin/bankroll.git
cd bankroll
```

### 2. Configure Environment

Edit `docker-compose.yml` and customize these settings:

```yaml
APP_KEY: "base64:YOUR_GENERATED_KEY_HERE"  # Replace with your key
APP_URL: "http://localhost:8000"           # Or your domain
APP_DEBUG: "false"                         # "true" for dev only
CURRENCY_SYMBOL: "₹"                       # Change if needed (₹, $, €, etc.)
```

**Note**: Generate APP_KEY with: `php artisan key:generate --show` (requires PHP locally) or use the existing key for testing.

### 3. Prepare Data Directory

```bash
# Create data directories
mkdir -p data/logs

# Create empty database file
touch data/database.sqlite

# Set permissions (Linux/Mac)
chmod 666 data/database.sqlite

# Windows: No chmod needed, file permissions are handled automatically
```

### 4. Build and Start

```bash
# Build the Docker image
docker compose build

# Start the container
docker compose up -d

# Verify container is running
docker compose ps
```

### 5. Initialize Database

```bash
# Run migrations
docker compose exec bankroll php artisan migrate --force

# Seed default categories (recommended)
docker compose exec bankroll php artisan db:seed --force

# Create admin user
docker compose exec bankroll php artisan user:add admin your_password
```

### 6. Access Application

Open your browser and navigate to:
- **HTTP**: [http://localhost:8000](http://localhost:8000)
- Login with credentials created in step 5

---

## Common Commands

### Container Management

```bash
# View logs
docker compose logs -f bankroll

# Stop container
docker compose stop

# Start container
docker compose start

# Restart container
docker compose restart

# Remove containers and network
docker compose down

# Rebuild and restart
docker compose down
docker compose build
docker compose up -d
```

### User Management

```bash
# Add a new user
docker compose exec bankroll php artisan user:add username password

# List all users
docker compose exec bankroll php artisan user:list

# Remove a user
docker compose exec bankroll php artisan user:remove username
```

### Database Operations

```bash
# Fresh migration (resets database!)
docker compose exec bankroll php artisan migrate:fresh --seed --force

# Backup database
cp data/database.sqlite data/database.sqlite.backup

# Restore database
cp data/database.sqlite.backup data/database.sqlite
docker compose restart
```

### Debugging

```bash
# Check container status
docker compose ps

# View real-time logs
docker compose logs -f

# Execute commands in container
docker compose exec bankroll bash

# Check PHP version
docker compose exec bankroll php --version

# Check Laravel version
docker compose exec bankroll php artisan --version
```

---

## Production Deployment

### Security Checklist

- [ ] Set `APP_ENV: "production"` in docker-compose.yml
- [ ] Set `APP_DEBUG: "false"` in docker-compose.yml
- [ ] Generate and use a unique `APP_KEY`
- [ ] Use strong passwords for all users
- [ ] Regularly backup `data/database.sqlite`
- [ ] Keep Docker images updated
- [ ] Monitor logs in `data/logs/`

### Reverse Proxy Setup (Optional)

For production with a domain name, use a reverse proxy like Nginx or Caddy:

**Nginx Example:**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Backups

Create automated backups:

```bash
# Create backup script
cat > backup.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
cp data/database.sqlite backups/database_${DATE}.sqlite
# Keep only last 30 backups
ls -t backups/database_*.sqlite | tail -n +31 | xargs rm -f
EOF

chmod +x backup.sh

# Add to crontab (daily at 2 AM)
crontab -e
# Add: 0 2 * * * /path/to/bankroll/backup.sh
```

---

## Troubleshooting

### Container Won't Start

```bash
# Check logs
docker compose logs bankroll

# Check permissions
docker compose exec -u root bankroll chown -R www-data:www-data /bankroll/storage /bankroll/bootstrap/cache /bankroll/data

# Rebuild from scratch
docker compose down
docker system prune -a
docker compose build --no-cache
docker compose up -d
```

### Database Permission Issues

```bash
# Fix database file permissions
chmod 666 data/database.sqlite
chmod 777 data

# Or reset from container
docker compose exec -u root bankroll chown -R www-data:www-data /bankroll/data
docker compose exec -u root bankroll chmod -R 775 /bankroll/data
```

### Port Already in Use

Change ports in `docker-compose.yml`:

```yaml
ports:
  - "8080:80"   # Use 8080 instead of 8000
  - "8444:443"  # Use 8444 instead of 8443
```

### Application Returns 500 Error

```bash
# Check Laravel logs
docker compose exec bankroll cat storage/logs/laravel.log

# Clear cache
docker compose exec bankroll php artisan cache:clear
docker compose exec bankroll php artisan config:clear
docker compose exec bankroll php artisan view:clear
```

---

## Technical Details

### Stack Information

- **Base Image**: dunglas/frankenphp:1-php8.3
- **Web Server**: FrankenPHP (PHP application server)
- **PHP Version**: 8.3.27
- **Database**: SQLite
- **Frontend**: Laravel Blade + Bootstrap 5.3
- **Build Type**: Multi-stage (optimized production build)

### Container Structure

```
/bankroll/               # Application root
├── app/                 # Laravel app
├── public/              # Web root
├── storage/             # Cache, logs, sessions
├── bootstrap/cache/     # Framework cache
└── data/                # Mounted volume (database)
    ├── database.sqlite  # SQLite database
    └── logs/            # Application logs
```

### Ports

- **8000**: HTTP (mapped to container port 80)
- **8443**: Alternative HTTP (mapped to container port 443)

**Note**: Both ports serve HTTP. For production HTTPS, use a reverse proxy with SSL termination.

---

## Support

For issues or questions:
- Check the [main README](README.md)
- Review container logs: `docker compose logs -f`
- Visit: [https://devnodes.in](https://devnodes.in)

Built with ❤️ by Devnodes.in
