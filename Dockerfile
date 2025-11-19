# Multi-stage Dockerfile for Bankroll using FrankenPHP
# Stage 1: Builder - Install dependencies and build assets
FROM dunglas/frankenphp:1-php8.3-alpine AS builder

# Install system dependencies and build tools
RUN apk add --no-cache \
    nodejs \
    npm \
    git \
    unzip \
    libzip-dev \
    oniguruma-dev \
    && install-php-extensions \
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
FROM dunglas/frankenphp:1-php8.3-alpine

# Install runtime dependencies and PHP extensions
RUN apk add --no-cache \
    libzip \
    oniguruma \
    && install-php-extensions \
    pdo_sqlite \
    zip \
    mbstring \
    gd \
    opcache

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Set recommended PHP.ini settings
RUN echo "opcache.enable=1" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=256" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.interned_strings_buffer=16" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=20000" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=0" >> $PHP_INI_DIR/conf.d/opcache.ini

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

# Set www-data as the user
USER www-data

# Expose port 80 and 443 (FrankenPHP handles both)
EXPOSE 80 443

# Set FrankenPHP as the default server
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV SERVER_NAME=":80"

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD ["php", "-r", "exit(0);"]

# Start FrankenPHP
ENTRYPOINT ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
