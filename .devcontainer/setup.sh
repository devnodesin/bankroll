#!/bin/bash

echo "🚀 Setting up Bankroll development environment..."

# Navigate to the src directory
cd /workspace/src || exit 1

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
npm install

# Setup environment file
if [ ! -f .env ]; then
    echo "🔧 Creating .env file..."
    cp .env.example .env
    php artisan key:generate
fi

# Setup SQLite database
if [ ! -f database/database.sqlite ]; then
    echo "🗄️  Creating SQLite database..."
    touch database/database.sqlite
fi

# Run migrations and seeders
echo "🗄️  Running database migrations..."
php artisan migrate --seed --force

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
chmod 664 database/database.sqlite

# Clear and cache config
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Create default user if not exists
echo "👤 Creating default admin user..."
php artisan user:add admin password123 --email=admin@example.com 2>/dev/null || echo "User may already exist"

# Build frontend assets
echo "🎨 Building frontend assets..."
npm run build

echo ""
echo "✅ Setup complete!"
echo ""
echo "🎯 Quick commands:"
echo "   Start server:    cd src && php artisan serve"
echo "   Run tests:       cd src && php artisan test"
echo "   Watch assets:    cd src && npm run dev"
echo "   Add user:        cd src && php artisan user:add <username> <password>"
echo ""
echo "🌐 Application will be available at http://localhost:8000"
echo "   Default login: admin / password123"
echo ""
