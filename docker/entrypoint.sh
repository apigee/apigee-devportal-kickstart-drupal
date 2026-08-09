#!/usr/bin/env bash
set -eo pipefail

echo "==> Kickstart Developer Portal Container Initializing..."

# Ensure required Drupal directories exist and are writable
mkdir -p /var/www/html/web/sites/default/files
mkdir -p /var/www/html/private
mkdir -p /var/www/html/config

# Fix ownership for www-data if running as root
if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data /var/www/html/web/sites/default/files /var/www/html/private || true
    chmod -R 775 /var/www/html/web/sites/default/files /var/www/html/private || true
    chmod -R +x /var/www/html/vendor/bin /var/www/html/vendor/drush/drush/drush 2>/dev/null || true
fi

# Set up local settings file from template if not already present
if [ ! -f /var/www/html/web/sites/default/settings.local.php ] && [ -f /var/www/html/web/sites/default/settings.local.php.example ]; then
    echo "==> Creating settings.local.php from template for local development..."
    cp /var/www/html/web/sites/default/settings.local.php.example /var/www/html/web/sites/default/settings.local.php
    if [ "$(id -u)" = "0" ]; then
        chown www-data:www-data /var/www/html/web/sites/default/settings.local.php || true
    fi
fi

# Wait for MySQL/MariaDB database server if DB_HOST is configured
if [ -n "$DB_HOST" ]; then
    echo "==> Waiting for MySQL/MariaDB server at ${DB_HOST}:${DB_PORT:-3306}..."
    MAX_TRIES=60
    COUNT=0
    until mysqladmin ping -h "$DB_HOST" -P "${DB_PORT:-3306}" --ssl=0 --silent 2>/dev/null || mysqladmin ping -h "$DB_HOST" -P "${DB_PORT:-3306}" -u root -proot --ssl=0 --silent 2>/dev/null; do
        COUNT=$((COUNT + 1))
        if [ "$COUNT" -ge "$MAX_TRIES" ]; then
            echo "==> Warning: Timed out waiting for database server at ${DB_HOST}:${DB_PORT:-3306} after $((MAX_TRIES * 3))s."
            break
        fi
        sleep 3
    done

    # Ensure database and user exist (handles cases where volume was created prior to env vars)
    if mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u root -proot --ssl=0 -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`; GRANT ALL ON \`${DB_NAME}\`.* TO '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASSWORD}'; FLUSH PRIVILEGES;" 2>/dev/null; then
        echo "==> Verified database '${DB_NAME}' and user '${DB_USER}' exist on ${DB_HOST}."
    fi

    # Verify connection as the application database user
    COUNT=0
    until mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USER" -p"$DB_PASSWORD" --ssl=0 -e "SELECT 1" "$DB_NAME" >/dev/null 2>&1; do
        COUNT=$((COUNT + 1))
        if [ "$COUNT" -ge 20 ]; then
            echo "==> Warning: Could not connect as ${DB_USER} to ${DB_NAME} after 60s. Continuing..."
            break
        fi
        sleep 3
    done
    if [ "$COUNT" -lt 20 ]; then
        echo "==> MySQL/MariaDB database '${DB_NAME}' is ready and accessible!"
    fi
fi

# Optional automated Drupal installation and configuration import for fresh test environments
if [ "${DRUPAL_AUTO_INSTALL:-false}" = "true" ]; then
    echo "==> DRUPAL_AUTO_INSTALL=true. Checking site bootstrap status..."
    if ! vendor/bin/drush status --field=bootstrap 2>/dev/null | grep -iq "Successful"; then
        echo "==> Installing Drupal using apigee_devportal_kickstart profile..."
        vendor/bin/drush site:install apigee_devportal_kickstart \
            --db-url="${DB_DRIVER:-mysql}://${DB_USER}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT:-3306}/${DB_NAME}" \
            --site-name="Apigee Developer Portal Kickstart (Local Dev)" \
            --account-name=admin \
            --account-pass=admin \
            --yes
        echo "==> Drupal site installation complete! Admin login: admin / admin"

        if [ -d "/var/www/html/config" ] && [ "$(ls -A /var/www/html/config 2>/dev/null)" ]; then
            echo "==> Synchronizing configuration from staged config/ YAML files..."
            vendor/bin/drush config:import --yes || echo "==> Notice: Config import completed with notices."
        fi
    else
        echo "==> Drupal site is already installed."
        if [ "${DRUPAL_CONFIG_IMPORT:-false}" = "true" ]; then
            echo "==> Running drush config:import --yes..."
            vendor/bin/drush config:import --yes || echo "==> Notice: Config import completed with notices."
        fi
    fi
    vendor/bin/drush cache:rebuild || true
fi

echo "==> Starting application command: $*"
exec "$@"
