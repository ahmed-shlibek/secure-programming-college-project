# syntax=docker/dockerfile:1

# ── Secure Programming College Project ────────────────────────────────
# PHP 8 MVC app served by Apache. Composer provides PSR-4 autoloading
# (App\Controllers, App\Models) and the one runtime dep, vlucas/phpdotenv.
# Document root is public/. Connects to MySQL via PDO.
# Built to run on Render's Docker runtime, which injects a $PORT to bind.
# ──────────────────────────────────────────────────────────────────────

FROM php:8.3-apache

# System packages: git + unzip are required by Composer to fetch/extract
# package archives (the base image ships with neither).
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

# Install the PDO MySQL driver the app needs (app/config/database.php)
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers

# Composer binary — used at build time to generate the autoloader
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Use production-tuned php.ini (disables display_errors, etc.)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Allow the 5 MB contact-form PDF uploads. Defaults are 2M (upload_max_filesize)
# and 8M (post_max_size); post_max_size must exceed upload_max_filesize plus the
# other form fields, so we set 6M / 8M.
RUN printf "upload_max_filesize=6M\npost_max_size=8M\n" > "$PHP_INI_DIR/conf.d/uploads.ini"

# Apache vhost: document root -> public/, listens on ${PORT}
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/ports.conf /etc/apache2/ports.conf
RUN rm -f /etc/apache2/sites-enabled/000-default.conf \
    && a2ensite 000-default

# Copy the application source
COPY . /var/www/html

# Generate the optimized PSR-4 autoloader from composer.json / composer.lock
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Drop files Apache doesn't need to serve
RUN rm -rf /var/www/html/docker /var/www/html/.git /var/www/html/.idea \
    && chown -R www-data:www-data /var/www/html

# Render sets $PORT at runtime; default to 10000 for local `docker run`
ENV PORT=10000
EXPOSE 10000

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]