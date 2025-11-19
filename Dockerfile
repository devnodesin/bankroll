# Multi-stage Dockerfile for Bankroll using FrankenPHP
# Stage 1: Builder - Install dependencies and build assets
FROM dunglas/frankenphp:1-php8.3 AS builder

# Install system dependencies and build tools
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN install-php-extensions \
    pdo_sqlite \
    zip \
    mbstring \
    gd \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY src/composer.json src/composer.lock ./

# Install PHP dependencies (no dev dependencies for production)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy package files
COPY src/package.json src/package-lock.json ./

# Install Node.js dependencies
RUN npm ci

# Copy application files
COPY src/ ./

# Generate optimized autoloader
RUN composer dump-autoload --optimize --no-dev

# Build frontend assets
RUN npm run build

# Clean up node_modules to save space
RUN rm -rf node_modules

# Stage 2: Final - FrankenPHP production image
FROM dunglas/frankenphp:1-php8.3

# Install runtime dependencies
RUN apt-get update && apt-get install -y \
    sqlite3 \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN install-php-extensions \
    pdo_sqlite \
    zip \
    mbstring \
    gd \
    opcache

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Set recommended PHP.ini settings for opcache
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.fast_shutdown=1'; \
    } > $PHP_INI_DIR/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www/html

# Copy application from builder stage
COPY --from=builder --chown=www-data:www-data /app ./

# Ensure database directory exists
RUN mkdir -p database

# Create empty database file if it doesn't exist
RUN touch database/database.sqlite

# Set proper permissions
RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    database && \
    chmod -R 775 \
    storage \
    bootstrap/cache \
    database

# Switch to www-data user
USER www-data

# Expose ports
EXPOSE 80 443

# Set FrankenPHP environment variables
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV SERVER_NAME=":80"

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php -r "exit(0);"

# Start FrankenPHP
CMD ["frankenphp", "run"]
