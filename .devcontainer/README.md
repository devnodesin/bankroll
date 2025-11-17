# Bankroll DevContainer Configuration

This directory contains the DevContainer configuration for Bankroll, enabling you to develop in a consistent, pre-configured environment using GitHub Codespaces or VS Code with the Remote-Containers extension.

## What's Included

### Base Environment
- **PHP 8.2** - Required for Laravel 12
- **Node.js 20** - For frontend asset compilation
- **Composer** - PHP dependency manager
- **SQLite** - Database (pre-configured)
- **Git & GitHub CLI** - Version control and GitHub integration

### VS Code Extensions
The container automatically installs these extensions:
- **PHP Intelephense** - PHP language support and IntelliSense
- **Laravel Extra Intellisense** - Laravel-specific code completion
- **Laravel Artisan** - Run Artisan commands from VS Code
- **Laravel Blade** - Blade template syntax support
- **ESLint & Prettier** - Code formatting
- **EditorConfig** - Consistent coding styles

### Port Forwarding
- **Port 8000** - Laravel application server
- **Port 5173** - Vite development server (for hot module reload)

## Quick Start

### Using GitHub Codespaces

1. Click the **Code** button on the repository
2. Select **Codespaces** tab
3. Click **Create codespace on main** (or your branch)
4. Wait for the container to build and setup (~2-3 minutes)
5. Once ready, the application will be available at the forwarded port 8000

### Using VS Code with Remote-Containers

1. Install the [Remote-Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)
2. Clone the repository locally
3. Open the folder in VS Code
4. Click the notification to "Reopen in Container" or use Command Palette: `Remote-Containers: Reopen in Container`
5. Wait for the container to build and setup

## What Happens During Setup

The `setup.sh` script automatically:
1. Installs PHP dependencies via Composer
2. Installs Node.js dependencies via npm
3. Creates and configures the `.env` file
4. Creates the SQLite database
5. Runs database migrations and seeders
6. Creates storage symbolic link
7. Sets proper file permissions
8. Creates a default admin user (admin / password123)
9. Builds frontend assets

## Common Commands

Once inside the container:

```bash
# Navigate to the application directory
cd src

# Start the Laravel development server
php artisan serve

# Run tests
php artisan test

# Watch and compile frontend assets
npm run dev

# Run migrations
php artisan migrate

# Create a new user
php artisan user:add <username> <password>

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Default Credentials

After setup, you can log in with:
- **Username**: admin
- **Password**: password123

## Troubleshooting

### Port Already in Use
If port 8000 is already in use, you can specify a different port:
```bash
php artisan serve --port=8080
```

### Database Issues
If you encounter database issues, you can reset the database:
```bash
cd src
rm database/database.sqlite
touch database/database.sqlite
php artisan migrate --seed
```

### Permission Issues
If you encounter permission issues:
```bash
cd src
chmod -R 775 storage bootstrap/cache
chmod 664 database/database.sqlite
```

### Clear All Caches
```bash
cd src
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

## Customization

### Modify PHP Configuration
To add PHP extensions or modify PHP settings, update the `devcontainer.json` file and rebuild the container.

### Add VS Code Extensions
Add extension IDs to the `extensions` array in `devcontainer.json`.

### Modify Setup Process
Edit the `setup.sh` script to customize the initial setup process.

## Resources

- [VS Code Remote Development](https://code.visualstudio.com/docs/remote/remote-overview)
- [GitHub Codespaces Documentation](https://docs.github.com/en/codespaces)
- [DevContainer Specification](https://containers.dev/)
- [Laravel Documentation](https://laravel.com/docs)

## Notes

- The devcontainer mounts the `src` directory for better performance
- Composer and npm packages are cached within the container
- The container runs as the `vscode` user for proper permissions
- All data persists in the container volume, including the SQLite database
