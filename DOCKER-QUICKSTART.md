# Bankroll Docker Quick Start & Deployment Guide

Get Bankroll running with Docker and FrankenPHP in minutes.

---

## Prerequisites

- Docker Engine 20.10+
- Docker Compose v2.0+
- 512MB+ RAM, 1GB+ disk space

---

## Clone & Setup

```bash
# Clone the repository
git clone https://github.com/devnodesin/bankroll.git
cd bankroll
```

---

## Configure Environment

Edit `docker-compose.yml` and set your APP_KEY:

```yaml
APP_KEY: "base64:YOUR_GENERATED_KEY_HERE"  # Replace with your key
APP_URL: "http://localhost:8000"           # Or your domain
APP_DEBUG: "false"                         # "true" for dev only
CURRENCY_SYMBOL: "₹"                       # Change if needed
```

---


```bash
mkdir -p data/logs
touch data/database.sqlite
chmod 666 data/database.sqlite
```

---


```bash
# Build the Docker image
docker compose build

# Start the containers
docker compose up -d
```
---

## 6. Initialize Database

```bash
# Run migrations
docker compose exec bankroll php artisan migrate --force
# (Optional) Seed categories
docker compose exec bankroll php artisan db:seed --force
```

---


```bash
# Add admin user
docker compose exec bankroll php artisan user:add admin your_password
```


## 8. Access Application
- HTTPS: [https://localhost:8443](https://localhost:8443) (self-signed)

---

```bash
# View logs
docker compose logs -f

# Stop/start/restart
docker compose stop
# ...
docker compose start
docker compose restart

# Remove containers
docker compose down

# Add/list/remove users
docker compose exec bankroll php artisan user:add username password
docker compose exec bankroll php artisan user:list
docker compose exec bankroll php artisan user:remove username

# Backup database
cp data/database.sqlite data/database.sqlite.backup
```

---

## Production & Security Notes

- Set `APP_ENV: "production"` and `APP_DEBUG: "false"` for production
- Use strong passwords and a unique APP_KEY
- Regularly backup `data/database.sqlite`
- Use HTTPS and consider a reverse proxy (Nginx/Caddy)
- Fix permissions if needed:
```bash
docker compose exec -u root bankroll chown -R www-data:www-data storage bootstrap/cache database
docker compose exec -u root bankroll chmod -R 775 storage bootstrap/cache database
```
