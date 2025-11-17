# Installation Guide

Follow these steps to set up the Bankroll Laravel application:

```bash
cd src

# Install Dependencies

composer install
npm install
npm run build
```

## Configure Environment File


```bash
# Copy the example environment file and customize it.
cp .env.example .env

# Generate Application Key
php artisan key:generate

# Run Database Migrations
php artisan migrate

# Run Seeders (Optional)
php artisan db:seed

# Start Development Server

php artisan serve
```
