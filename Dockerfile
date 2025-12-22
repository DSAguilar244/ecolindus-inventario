# Build stage
FROM php:8.2-fpm as builder

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Copy application files
COPY . .

# Generate autoloader and run post-install scripts
RUN composer dump-autoload && \
    composer run-script post-install-cmd

# Copy Node from official image and build assets
FROM node:18 as assets
WORKDIR /app
COPY --from=builder /app .
RUN npm install && npm run build

# Runtime stage
FROM php:8.2-apache

# Install runtime dependencies
RUN apt-get update && apt-get install -y \
    libpq5 \
    libpng16-16 \
    libjpeg62-turbo \
    libfreetype6 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Enable Apache modules
RUN a2enmod rewrite && a2enmod headers

# Set working directory
WORKDIR /var/www/html

# Copy application from builder
COPY --from=builder /app /var/www/html

# Copy built assets
COPY --from=assets /app/public/build public/build
COPY --from=assets /app/public/hot public/hot 2>/dev/null || true

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Create Apache configuration
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/sites-available/000-default.conf && \
    echo '<VirtualHost *:80>\n\
    ServerName localhost\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:8000/health || exit 1

# Start Apache
CMD ["apache2-foreground"]
