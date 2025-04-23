FROM serversideup/php:8.3-fpm-nginx

USER root

## Install Node
RUN apt-get update && \
    apt-get install -y curl && \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

COPY --chown=www-data:www-data . /var/www/html

# Drop back to our unprivileged user
USER www-data

## Install packages
RUN composer install --quiet --no-dev --no-scripts --no-interaction --no-progress --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Install node dependencies
RUN npm set progress=false && \
    npm config set depth 0 && \
    npm install && \
    npm run build && \
    rm -rf node_modules