#!/bin/sh
set -e

# Create .env from .env.example if it doesn't exist
if [ ! -f .env ] && [ -f .env.example ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Run composer install if composer.json is present
if [ -f composer.json ]; then
    echo "Running composer install..."
    composer install --no-interaction --optimize-autoloader
fi

# Wait for PostgreSQL database to be ready (connecting to default 'postgres' database first)
echo "Waiting for database server to start..."
until php -r "
try {
    new PDO('pgsql:host=db;port=5432;dbname=postgres', 'fightclub', 'fightclub');
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    sleep 1
done

echo "Database server is ready."

# Create development database if it doesn't exist
echo "Creating development database if not exists..."
php bin/console doctrine:database:create --if-not-exists --no-interaction

# Run migrations for development database
echo "Running development database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Create test database if it doesn't exist
echo "Creating test database if not exists..."
php bin/console --env=test doctrine:database:create --if-not-exists --no-interaction

# Run migrations for test database
echo "Running test database migrations..."
php bin/console --env=test doctrine:migrations:migrate --no-interaction

echo "Docker container setup completed successfully. Starting php-fpm..."
exec "$@"
