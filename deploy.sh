#!/bin/bash
# Bankroll Docker Deployment Script

set -e

echo "========================================="
echo "  Bankroll Docker Deployment Script"
echo "========================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

print_success "Docker and Docker Compose are installed"

# Create data directory
echo ""
echo "Step 1: Creating data directories..."
mkdir -p data/logs
print_success "Data directories created"

# Create database file if it doesn't exist
if [ ! -f data/database.sqlite ]; then
    echo ""
    echo "Step 2: Creating SQLite database file..."
    touch data/database.sqlite
    chmod 666 data/database.sqlite
    print_success "Database file created"
else
    print_warning "Database file already exists, skipping creation"
fi

# Check if APP_KEY is set in docker-compose.yml
echo ""
echo "Step 3: Checking APP_KEY configuration..."
if grep -q "APP_KEY: \"base64:YOUR_APP_KEY_HERE\"" docker-compose.yml; then
    print_warning "APP_KEY is not configured!"
    echo ""
    echo "To generate an APP_KEY, run one of these commands:"
    echo "  - If you have PHP installed: php src/artisan key:generate --show"
    echo "  - Using Docker: docker run --rm -v \$(pwd)/src:/app -w /app composer:latest php artisan key:generate --show"
    echo ""
    echo "Then update the APP_KEY in docker-compose.yml"
    echo ""
    read -p "Press Enter to continue or Ctrl+C to exit..."
else
    print_success "APP_KEY is configured"
fi

# Build Docker image
echo ""
echo "Step 4: Building Docker image..."
docker-compose build
print_success "Docker image built successfully"

# Start containers
echo ""
echo "Step 5: Starting containers..."
docker-compose up -d
print_success "Containers started"

# Wait for container to be ready
echo ""
echo "Step 6: Waiting for application to be ready..."
sleep 5

# Run migrations
echo ""
echo "Step 7: Running database migrations..."
docker-compose exec -T bankroll php artisan migrate --force
print_success "Migrations completed"

# Check if we should seed
echo ""
read -p "Do you want to seed the database with sample categories? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    docker-compose exec -T bankroll php artisan db:seed --force
    print_success "Database seeded"
fi

# Create admin user
echo ""
read -p "Do you want to create an admin user? (Y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Nn]$ ]]; then
    echo ""
    read -p "Enter username: " username
    read -s -p "Enter password: " password
    echo ""
    docker-compose exec -T bankroll php artisan user:add "$username" "$password"
    print_success "Admin user created"
fi

# Show status
echo ""
echo "========================================="
print_success "Deployment Complete!"
echo "========================================="
echo ""
echo "Application is running at:"
echo "  HTTP:  http://localhost:8000"
echo "  HTTPS: https://localhost:8443"
echo ""
echo "Useful commands:"
echo "  - View logs: docker-compose logs -f"
echo "  - Stop: docker-compose stop"
echo "  - Start: docker-compose start"
echo "  - Restart: docker-compose restart"
echo "  - Remove: docker-compose down"
echo ""
echo "For more information, see DEPLOYMENT.md"
echo ""
