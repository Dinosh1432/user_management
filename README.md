User Management System - PHP + MySQL
-----------------------------------

1. Place this folder in your webserver root (e.g. /var/www/html/user-management or htdocs/user-management).
2. Create 'uploads' folder writable by webserver (already present).
3. Import 'schema.sql' into MySQL (phpMyAdmin or mysql CLI).
4. Edit config.php to set DB credentials.
5. Create an admin user by registering and updating role in DB, or run a small PHP script to insert an admin.
6. Visit register.php or login.php to start.

Security notes:
- Use HTTPS in production.
- Add CSRF tokens, stronger validation, rate-limiting.
