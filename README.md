# Mini Social

A PHP MVC mini social network for the Web Systems and Technologies final project. It includes session authentication, profiles, a chronological feed, image posts, comments and comment hearts, post favorites, mentions and notifications, follower-only/private/public post visibility, pinned profile posts, sharing, and post/user search.

## Requirements

- PHP 8.1+ with MySQLi and Fileinfo enabled
- MySQL 8.0+ or MariaDB 10.4+
- XAMPP, or an equivalent PHP/MySQL environment

## Run with XAMPP

1. Place this folder at `C:\xampp\htdocs\mini-social`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin` and import `sql/social_app.sql`.
4. Visit `http://localhost/mini-social/`.

The default local database connection is host `127.0.0.1`, database `social_app`, user `root`, and an empty password. Set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables to override it.

The SQL file includes three demo accounts, posts, comments, and likes. It also creates the follower, favorite, comment-heart, and notification tables and safely adds post visibility/pinning columns to an existing database. Re-import it to upgrade an earlier version without deleting existing data. Each demo account uses the password `commonplace123`.

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
  uploads/       User-uploaded profile and post images
  index.php      Redirect for the previous public URL
sql/
  social_app.sql MySQL schema, upgrade, and sample data
  ERD.md         Mermaid entity relationship diagram
```

Uploaded images are limited to 5 MB and accepted only when their detected MIME type is JPEG, PNG, WEBP, or GIF. The application uses prepared statements, escaped template output, CSRF tokens for state-changing requests, password hashing, and ownership checks for post and comment edits/deletes.