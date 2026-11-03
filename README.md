# Mini Social

A PHP MVC mini social network for the Web Systems and Technologies final project. It includes session authentication, profiles, a chronological feed, in-app reposts, nested comment conversations and comment hearts, post favorites, mentions and activity notifications, follower-only/private/public post visibility, pinned profile posts, post/user search, administrator-managed site branding, warnings, and temporary/permanent account bans.

## Requirements

- PHP 8.1+ with MySQLi and Fileinfo enabled
- MySQL 8.0+ or MariaDB 10.4+
- XAMPP, or an equivalent PHP/MySQL environment

## Run with XAMPP

1. Place this folder at `C:\xampp\htdocs\mini-social`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. In phpMyAdmin, create a database named `mvxb_db`, select it, then import `sql/mvxb_db.sql`. The SQL file does not create or select databases; the active database must be selected before importing. Hosting providers such as InfinityFree require creating databases through their control panel.
4. Visit `http://localhost/mini-social/`.

The default local database connection is host `127.0.0.1`, database `mvxb_db`, user `root`, and an empty password. On InfinityFree, use the MySQL host, database name, username, and password shown in the hosting control panel. Set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables to override the local defaults.

The SQL file includes demo accounts, posts, comments, and likes. It also creates the follower, favorite, comment-heart, site settings, and notification tables, and safely upgrades post visibility/pinning, reposts, nested replies, moderation status, notification details, and event types in an existing database. Re-import it to upgrade an earlier version without deleting existing data. Each demo account uses the password `commonplace123`.

The administrator uses username `admin` and password `MiniAdmin2026!`. The single SQL file also adds the admin role to an existing `users` table without deleting its data.

## Project Structure

```text
app/
  controllers/   Request handling, validation, authorization
  models/        MySQLi data access
  views/         PHP presentation templates
  helpers.php    Shared escaping, CSRF, sessions, uploads, rendering
config/
  database.php   MySQLi connection
index.php        Root front controller
public/
  assets/        Responsive CSS
uploads/       User-uploaded profile, post, and admin branding images
  index.php      Redirect for the previous public URL
sql/
  mvxb_db.sql     MySQL schema, upgrade, and sample data
  ERD.md         Mermaid entity relationship diagram
```

Uploaded images are limited to 5 MB and accepted only when their detected MIME type is JPEG, PNG, WEBP, or GIF. The application uses prepared statements, escaped template output, CSRF tokens for state-changing requests, password hashing, and ownership checks for post and comment edits/deletes.