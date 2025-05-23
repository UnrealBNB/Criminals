#!/bin/bash

# Criminals Game Docker Setup Script

echo "🎮 Setting up Criminals Game with Docker..."

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p docker/nginx/ssl
mkdir -p docker/mysql
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache

# Copy environment file
if [ ! -f .env ]; then
    echo "📋 Creating .env file..."
    cp .env.example .env

    # Generate app key
    APP_KEY=$(openssl rand -base64 32)
    sed -i "s|APP_KEY=.*|APP_KEY=base64:$APP_KEY|g" .env
fi

# Set permissions
# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 storage
chmod +x bin/console
chmod +x docker-setup.sh

# Build and start containers
echo "🐳 Building Docker containers..."
docker-compose build

echo "🚀 Starting containers..."
docker-compose up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Run migrations
echo "🗄️ Running database migrations..."
docker-compose exec app php bin/console migrate

# Create admin user
echo "👤 Would you like to create an admin user? (y/n)"
read -r create_admin

if [ "$create_admin" = "y" ]; then
    docker-compose exec app php bin/console user:create-admin
fi

# Clear cache
echo "🧹 Clearing cache..."
docker-compose exec app php bin/console cache:clear

echo "✅ Setup complete!"
echo ""
echo "🌐 Access the game at: http://localhost"
echo "📊 PhpMyAdmin available at: http://localhost:8080"
echo ""
echo "📝 Default database credentials:"
echo "   Database: criminals"
echo "   Username: criminals_user"
echo "   Password: secret"
echo ""
echo "🔧 Useful commands:"
echo "   docker-compose logs -f     # View logs"
echo "   docker-compose down        # Stop containers"
echo "   docker-compose restart     # Restart containers"
echo "   docker-compose exec app bash   # Access app container"