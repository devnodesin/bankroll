# Bankroll Docker Quick Start Guide

Get Bankroll running with Docker in minutes.

## Prerequisites

- Docker Engine 20.10+
- Docker Compose v2.0+
- 512MB RAM, 1GB disk space

## Quick Start

### 1. Clone and Configure

```bash
git clone https://github.com/devnodesin/bankroll.git
cd bankroll

# Create data directory and database file
mkdir -p data/logs
touch data/database.sqlite
chmod 666 data/database.sqlite  # Linux/Mac only
```

**Optional**: Edit `docker-compose.yml` to customize:
- `APP_KEY` - Generate with `php artisan key:generate --show` or use existing
- `APP_URL` - Change to your domain if deploying to production
- `CURRENCY_SYMBOL` - Default is ₹ (change to $, €, etc. as needed)

### 2. Build and Start

```bash
docker compose build
docker compose up -d
docker compose ps  # Verify container is running
```

### 3. Initialize Database

```bash
docker compose exec bankroll php artisan migrate --force
docker compose exec bankroll php artisan db:seed --force
docker compose exec bankroll php artisan user:add admin your_password
```

### 4. Access Application

Open [http://localhost:8000](http://localhost:8000) and login with your credentials.

## Common Commands

```bash
# Container management
docker compose logs -f          # View logs
docker compose restart          # Restart container
docker compose down             # Stop and remove
docker compose build --no-cache # Rebuild from scratch

# User management
docker compose exec bankroll php artisan user:add username password
docker compose exec bankroll php artisan user:list
docker compose exec bankroll php artisan user:remove username

# Database backup/restore
cp data/database.sqlite data/database.sqlite.backup
cp data/database.sqlite.backup data/database.sqlite && docker compose restart

# Debugging
docker compose ps               # Check status
docker compose exec bankroll bash  # Shell access
docker compose exec bankroll php artisan --version
```

## Production Deployment

### Security Checklist
- [ ] Set `APP_ENV: "production"` and `APP_DEBUG: "false"` in docker-compose.yml
- [ ] Generate unique `APP_KEY`: `php artisan key:generate --show`
- [ ] Use strong passwords for all users
- [ ] Setup regular backups (see below)
- [ ] Keep Docker images updated
- [ ] Monitor logs in `data/logs/`

### Automated Backups
```bash
# Create backup script
cat > backup.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p backups
cp data/database.sqlite backups/database_${DATE}.sqlite
ls -t backups/database_*.sqlite | tail -n +31 | xargs rm -f
EOF
chmod +x backup.sh

# Schedule daily backups (2 AM)
(crontab -l 2>/dev/null; echo "0 2 * * * /path/to/bankroll/backup.sh") | crontab -
```

### Reverse Proxy (Optional)
For production with SSL/domain, use Nginx or Caddy:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

## Troubleshooting

### Container Won't Start
```bash
docker compose logs bankroll  # Check logs
docker compose down && docker compose build --no-cache && docker compose up -d  # Rebuild
```

### Database Permission Issues
```bash
chmod 666 data/database.sqlite
chmod 777 data
docker compose restart
```

### Port Already in Use
Edit `docker-compose.yml` and change `"8000:80"` to `"8080:80"` (or any free port)

### Application Errors
```bash
docker compose exec bankroll cat storage/logs/laravel.log  # View logs
docker compose exec bankroll php artisan cache:clear       # Clear cache
```

## Technical Details

**Stack**: FrankenPHP (PHP 8.3) + Laravel 12 + SQLite + Bootstrap 5.3  
**Ports**: 8000 (HTTP), 8443 (HTTP - use reverse proxy for HTTPS)  
**Storage**: SQLite database in `data/database.sqlite`  
**Logs**: Application logs in `data/logs/`

## Support

- [Main README](README.md)
- [GitHub Issues](https://github.com/devnodesin/bankroll/issues)
- Check logs: `docker compose logs -f`

Built with ❤️ by [Devnodes.in](https://devnodes.in)
