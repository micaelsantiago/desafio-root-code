FROM php:8.4-fpm

# Install system dependencies required by PHP extensions and Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    zip \
    unzip \
    git \
    libonig-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions needed by Laravel
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    fileinfo

# Install Composer from its official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Replace the default Nginx site config with ours
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copy and enable the entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
