# Multi-stage build for Laravel with Vite

# Stage 1: PHP Builder
FROM php:8.2-fpm as php-builder

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
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Copy application
COPY . .

# Generate autoloader
RUN composer dump-autoload && composer run-script post-install-cmd

# Stage 2: Node/Vite Builder
FROM node:18 as node-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install

COPY . .
RUN npm run build

# Stage 3: Production Runtime
FROM php:8.2-apache

# Install runtime dependencies
RUN apt-get update && apt-get install -y \
    libpq5 \
    libpng16-16 \
    libjpeg62-turbo \
    libfreetype6 \
    curl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Enable Apache modules
RUN a2enmod rewrite headers

WORKDIR /var/www/html

# Copy PHP application from builder
COPY --from=php-builder /app .

# Copy built assets from Node builder
COPY --from=node-builder /app/public/build ./public/build

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 storage bootstrap/cache

# Configure Apache VirtualHost
RUN cat > /etc/apache2/sites-available/000-default.conf << 'EOF'
<VirtualHost *:80>
    ServerName localhost
    ServerAdmin admin@localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Disable default and enable our config
RUN a2dissite 000-default || true && a2ensite 000-default

EXPOSE 80

CMD ["apache2-foreground"]
