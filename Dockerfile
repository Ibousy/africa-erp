FROM php:8.4-cli

ARG CACHEBUST=1

# System dependencies + PHP extensions MySQL
RUN apt-get update && apt-get install -y \
    git curl unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev \
    libjpeg-dev libfreetype6-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring xml zip opcache gd bcmath intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Node.js 20 (obligatoire pour Vite 8 / Tailwind 4)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && node --version && npm --version

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Installer les dépendances PHP (cache layer)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Installer les dépendances Node (cache layer)
COPY package.json package-lock.json ./
RUN npm ci

# Copier le reste du projet
COPY . .

# Build des assets Vite
RUN npm run build

# Répertoires Laravel
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8000

CMD ["/start.sh"]
