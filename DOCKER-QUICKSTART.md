# Docker Quick Start Guide

Get Bankroll running with Docker in under 5 minutes!

## Prerequisites

- Docker Engine 20.10+
- Docker Compose v2.0+

## Quick Start (Automated)

Use the deployment script for automated setup:

```bash
# Clone the repository
git clone https://github.com/devnodesin/bankroll.git
cd bankroll

# Run deployment script
./deploy.sh
```

The script will:
1. Create necessary directories
2. Create the SQLite database file
3. Build the Docker image
4. Start the containers
5. Run migrations
6. Optionally seed the database
7. Optionally create an admin user

## Quick Start (Manual)

### 1. Generate Application Key

```bash
# Using PHP locally (if available)
php src/artisan key:generate --show

# OR using Docker
docker run --rm -v $(pwd)/src:/app -w /app composer:latest php artisan key:generate --show
```

Copy the generated key (looks like `base64:xxxxxxxxxxxxx`).

### 2. Update Configuration

Edit `docker-compose.yml` and replace `YOUR_APP_KEY_HERE` with your generated key:

```yaml
APP_KEY: "base64:your_actual_key_here"
```

### 3. Prepare Data Directory

```bash
mkdir -p data/logs
touch data/database.sqlite
chmod 666 data/database.sqlite
```

### 4. Build and Start

```bash
docker compose up -d
```

### 5. Initialize Database

```bash
# Run migrations
docker compose exec bankroll php artisan migrate --force

# (Optional) Seed categories
docker compose exec bankroll php artisan db:seed --force
```

### 6. Create User

```bash
docker compose exec bankroll php artisan user:add admin your_password
```

### 7. Access Application

Open your browser: `http://localhost:8000`

## Common Commands

```bash
# View logs
docker compose logs -f

# Stop application
docker compose stop

# Start application
docker compose start

# Restart application
docker compose restart

# Stop and remove containers
docker compose down

# Add new user
docker compose exec bankroll php artisan user:add username password

# List users
docker compose exec bankroll php artisan user:list

# Backup database
cp data/database.sqlite data/database.sqlite.backup
```

## Customization

Edit `docker-compose.yml` to customize:

- **Port**: Change `8000:80` to use different port
- **Currency**: Change `CURRENCY_SYMBOL` value
- **Domain**: Update `APP_URL` for your domain
- **Debug**: Set `APP_DEBUG: "true"` for debugging (not recommended for production)

## Troubleshooting

### Container won't start

```bash
# Check logs
docker compose logs bankroll

# Verify APP_KEY is set
grep APP_KEY docker-compose.yml
```

### Permission errors

```bash
# Fix permissions
docker compose exec -u root bankroll chown -R www-data:www-data storage bootstrap/cache database
docker compose exec -u root bankroll chmod -R 775 storage bootstrap/cache database
```

### Database errors

```bash
# Verify database file exists
ls -la data/database.sqlite

# Fix permissions
chmod 666 data/database.sqlite

# Re-run migrations
docker compose exec bankroll php artisan migrate --force
```

### Port already in use

Edit `docker-compose.yml` and change the ports:

```yaml
ports:
  - "8080:80"  # Changed from 8000:80
  - "8443:443"
```

## Need Help?

- Full Documentation: [DEPLOYMENT.md](DEPLOYMENT.md)
- Issues: https://github.com/devnodesin/bankroll/issues
- Repository: https://github.com/devnodesin/bankroll

Built with ❤️ by [Devnodes.in](https://devnodes.in)
