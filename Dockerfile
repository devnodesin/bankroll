# Multi-stage Dockerfile for Bankroll using FrankenPHP
# Optimized for production with minimal image size and security best practices

# Stage 1: Builder - Install dependencies and build assets
FROM dunglas/frankenphp:1-php8.3 AS builder

# Install system dependencies and Node.js for building frontend assets
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*  # Clean up to reduce layer size

# Install required PHP extensions for Laravel and SQLite
RUN install-php-extensions \
    pdo_sqlite \
    zip \
    mbstring \
    gd \
    opcache

# Copy Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy dependency files first for better layer caching
COPY src/composer.json src/composer.lock ./

# Install PHP dependencies (production only, no dev packages)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy Node.js package files
COPY src/package.json src/package-lock.json ./

# Install Node.js dependencies (clean install for reproducibility)
RUN npm ci

# Copy all application files
COPY src/ ./

# Generate optimized autoloader for production
RUN composer dump-autoload --optimize --no-dev

# Build frontend assets (Vite)
RUN npm run build

# Remove Node.js dependencies to reduce final image size
RUN rm -rf node_modules

# Stage 2: Final - Minimal production image
FROM dunglas/frankenphp:1-php8.3

# Install runtime dependencies (SQLite CLI for debugging)
RUN apt-get update && apt-get install -y \
    sqlite3 \
    && rm -rf /var/lib/apt/lists/*  # Clean up package lists

# Install the same PHP extensions as builder
RUN install-php-extensions \
    pdo_sqlite \
    zip \
    mbstring \
    gd \
    opcache

# Use production PHP configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Configure OPcache for optimal performance
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.fast_shutdown=1'; \
    } > $PHP_INI_DIR/conf.d/opcache.ini

WORKDIR /bankroll

# Copy built application from builder stage with correct ownership
COPY --from=builder --chown=www-data:www-data /app ./

# Copy Caddyfile for FrankenPHP web server configuration
COPY Caddyfile /etc/caddy/Caddyfile

# Create required directories
RUN mkdir -p data /data/caddy

# Create empty database file (will be mounted as volume in production)
RUN touch data/database.sqlite

# Set permissions for writable directories
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    data \
    /data/caddy && \
    chmod -R 775 \
    storage \
    bootstrap/cache \
    data \
    /data/caddy

# Run as non-root user for security
USER www-data

# Expose HTTP and HTTPS ports
EXPOSE 80 443

# Health check to verify container is running properly
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php -r "exit(0);"

# Start FrankenPHP with Caddyfile
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
