# Docker Setup Testing Guide

This document outlines how to test the Docker setup for Bankroll.

## Prerequisites Verification

Before testing, ensure:

1. **Docker Engine 20.10+** is installed
   ```bash
   docker --version
   ```

2. **Docker Compose v2.0+** is installed
   ```bash
   docker compose version
   ```

3. **Sufficient Resources**:
   - 512MB RAM minimum (1GB recommended)
   - 2GB disk space for image and data
   - Network access for pulling base images

## Pre-Build Validation

Run the validation script to check the setup:

```bash
./validate-docker-setup.sh
```

Expected output:
- All critical checks pass (✓)
- Only warning should be about APP_KEY not being configured (expected)

## Manual Configuration Testing

### 1. Test docker-compose.yml Syntax

```bash
docker compose config
```

Should output the parsed configuration without errors.

### 2. Check Environment Variables

Verify all required environment variables are set in `docker-compose.yml`:

```bash
grep -E "(APP_ENV|APP_KEY|APP_DEBUG|APP_URL|CURRENCY_SYMBOL)" docker-compose.yml
```

Expected variables:
- `APP_ENV: "production"`
- `APP_KEY: "base64:YOUR_APP_KEY_HERE"` (placeholder)
- `APP_DEBUG: "false"`
- `APP_URL: "http://localhost:8000"`
- `CURRENCY_SYMBOL: "₹"`

### 3. Verify Volume Mounts

Check that volumes are properly configured:

```bash
grep -A2 "volumes:" docker-compose.yml
```

Expected mounts:
- `./data/database.sqlite:/var/www/html/database/database.sqlite`
- `./data/logs:/var/www/html/storage/logs`

## Build Testing

### 1. Clean Build Test

Build the image from scratch:

```bash
# Remove any existing build
docker compose down -v
docker rmi bankroll-bankroll 2>/dev/null || true

# Build from scratch
time docker compose build --no-cache
```

Expected:
- Build completes successfully (may take 5-15 minutes)
- Final image size: ~500MB-800MB
- No error messages

### 2. Incremental Build Test

Test that subsequent builds are faster:

```bash
time docker compose build
```

Expected:
- Build completes much faster (cached layers)
- Uses cached steps where possible

## Runtime Testing

### 1. Start Container

```bash
# Create data directory
mkdir -p data/logs
touch data/database.sqlite
chmod 666 data/database.sqlite

# Generate APP_KEY and update docker-compose.yml
# Then start container
docker compose up -d
```

### 2. Check Container Status

```bash
# Check if container is running
docker compose ps

# Check logs for errors
docker compose logs bankroll

# Check health status
docker inspect bankroll | grep -A5 Health
```

Expected:
- Container status: "Up"
- No critical errors in logs
- Health check: "healthy"

### 3. Test Database Access

```bash
# Run migrations
docker compose exec bankroll php artisan migrate --force

# Create test user
docker compose exec bankroll php artisan user:add testuser testpass123

# List users to verify
docker compose exec bankroll php artisan user:list
```

Expected:
- Migrations run successfully
- User created successfully
- User appears in list

### 4. Test Web Access

```bash
# Check if HTTP port is accessible
curl -I http://localhost:8000

# Check if process is listening
docker compose exec bankroll ps aux | grep frankenphp
```

Expected:
- HTTP 200 or 302 response
- FrankenPHP process running as www-data

### 5. Test File Permissions

```bash
# Check critical directory permissions
docker compose exec bankroll ls -la storage
docker compose exec bankroll ls -la bootstrap/cache
docker compose exec bankroll ls -la database
```

Expected:
- All directories owned by www-data:www-data
- Permissions: 775 (drwxrwxr-x)
- database.sqlite: 664 or 666

### 6. Test Volume Mounts

```bash
# Write to database through container
docker compose exec bankroll php artisan db:seed --force

# Check if changes persist in host
ls -lh data/database.sqlite

# Check log mounting
docker compose exec bankroll php artisan about
ls -la data/logs/
```

Expected:
- Database file size increases after seeding
- Logs appear in data/logs/ on host

## Application Testing

### 1. Manual Browser Test

1. Open browser: `http://localhost:8000`
2. Verify login page loads
3. Login with created test user
4. Check dashboard loads
5. Try importing a sample transaction file
6. Try exporting data

### 2. Performance Test

```bash
# Check resource usage
docker stats bankroll --no-stream

# Check response time
time curl -s http://localhost:8000 > /dev/null
```

Expected:
- Memory usage: 100-300MB
- CPU usage: Low (< 5% idle)
- Response time: < 1 second

## Integration Testing

### 1. Stop and Start Test

```bash
# Stop container
docker compose stop

# Verify stopped
docker compose ps

# Start container
docker compose start

# Verify running and data persists
docker compose exec bankroll php artisan user:list
```

Expected:
- Container stops and starts cleanly
- Data persists between restarts
- Users still exist after restart

### 2. Restart Test

```bash
docker compose restart

# Wait a few seconds
sleep 5

# Check if still working
curl -I http://localhost:8000
```

Expected:
- Container restarts successfully
- Application responds after restart

### 3. Update Test

```bash
# Simulate code update
docker compose down
docker compose build
docker compose up -d

# Run any new migrations
docker compose exec bankroll php artisan migrate --force
```

Expected:
- Rebuild succeeds
- Container starts with new image
- Data persists through update

## Cleanup Testing

### 1. Clean Shutdown

```bash
# Stop and remove containers
docker compose down

# Verify cleanup
docker compose ps -a
```

Expected:
- No bankroll containers running
- Data files still exist in ./data/

### 2. Complete Cleanup

```bash
# Remove everything including volumes
docker compose down -v

# Remove image
docker rmi bankroll-bankroll

# Remove data directory
rm -rf data/
```

Expected:
- All containers removed
- Images removed
- Data directory removed

## Automated Testing Script

Save as `test-docker.sh`:

```bash
#!/bin/bash
set -e

echo "Running Docker setup tests..."

# Validation
./validate-docker-setup.sh || exit 1

# Build test
echo "Testing build..."
docker compose build

# Start test
echo "Starting container..."
mkdir -p data/logs
touch data/database.sqlite
chmod 666 data/database.sqlite
docker compose up -d

# Wait for container
sleep 10

# Health check
echo "Checking health..."
docker compose ps | grep -q "Up" || exit 1

# Database test
echo "Testing database..."
docker compose exec -T bankroll php artisan migrate --force
docker compose exec -T bankroll php artisan user:add testuser testpass123
docker compose exec -T bankroll php artisan user:list | grep -q testuser || exit 1

# Web test
echo "Testing web access..."
curl -f http://localhost:8000 || exit 1

echo "All tests passed!"
```

Run with:
```bash
chmod +x test-docker.sh
./test-docker.sh
```

## Troubleshooting Tests

If tests fail, check:

1. **Build failures**: Check Docker daemon is running, network access
2. **Container won't start**: Check logs with `docker compose logs`
3. **Database errors**: Check permissions on data/database.sqlite
4. **Web access fails**: Check if port 8000 is available
5. **Performance issues**: Check available system resources

## Test Checklist

- [ ] Validation script passes
- [ ] docker-compose.yml syntax is valid
- [ ] Docker build completes successfully
- [ ] Container starts and reaches healthy state
- [ ] Database migrations run successfully
- [ ] Can create users via artisan command
- [ ] Web interface is accessible
- [ ] File permissions are correct
- [ ] Volume mounts work correctly
- [ ] Data persists between restarts
- [ ] Container stops and starts cleanly
- [ ] Cleanup works correctly

## Success Criteria

The Docker setup is considered successful if:

1. ✅ Build completes without errors
2. ✅ Container starts and stays running
3. ✅ Health check passes
4. ✅ Database operations work
5. ✅ Web interface is accessible
6. ✅ Volume mounts preserve data
7. ✅ Performance is acceptable (< 300MB RAM)
8. ✅ Container survives restart

## Reporting Issues

If you encounter issues, provide:

1. Output of `./validate-docker-setup.sh`
2. Docker and Docker Compose versions
3. Build logs: `docker compose build 2>&1 | tee build.log`
4. Container logs: `docker compose logs > container.log`
5. System info: `docker info`

Built with ❤️ by [Devnodes.in](https://devnodes.in)
