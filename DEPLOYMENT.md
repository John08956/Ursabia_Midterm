# InfinityFree Deployment

1. Create an InfinityFree account and add a domain/subdomain in the control panel.
2. Open **MySQL Databases** and create a database. Copy the exact MySQL hostname, database name, username, and password shown there. The database name and username are usually prefixed; do not use `localhost` unless the panel explicitly shows it.
3. Open phpMyAdmin for that database and import `database.sql`.
4. Copy `Backend/config.example.php` to `Backend/config.php`, then replace the four example values with the InfinityFree MySQL details. Keep `config.php` private; it is intentionally ignored by Git.
5. Upload the project contents to the domain's `htdocs` directory using the control panel file manager or FTP. Keep `Backend`, `classes`, `modals`, and `pages` in their current relative locations.
6. Visit the site's `/pages/register.php` to create the first account, then log in. Use HTTPS so session cookies are marked secure.

The app uses PDO MySQL, password hashing, PHP sessions, and InnoDB transactions. No Composer packages or background services are required. If your hosting account has different PHP/MySQL capabilities, check the InfinityFree control panel before uploading. Keep a local backup of the database before importing changes to an existing site.
