#!/bin/bash
# Run this script on Render after deployment to execute migrations
# Add this as a "Post Deploy Command" in Render dashboard

echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

if [ $? -eq 0 ]; then
    echo "✓ Migrations completed successfully!"
else
    echo "✗ Migrations failed!"
    exit 1
fi
