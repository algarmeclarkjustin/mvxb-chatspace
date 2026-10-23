CREATE DATABASE IF NOT EXISTS social_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE social_app;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(24) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(80) NOT NULL,
    bio VARCHAR(240) NOT NULL DEFAULT '',
    profile_image VARCHAR(255) NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    is_permanently_banned TINYINT(1) NOT NULL DEFAULT 0,
    banned_until DATETIME NULL,
    ban_reason VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @role_column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'role'
);
SET @role_migration = IF(
    @role_column_exists = 0,
    'ALTER TABLE users ADD COLUMN role ENUM(''user'', ''admin'') NOT NULL DEFAULT ''user''',
    'SELECT 1'
);
PREPARE ensure_user_role FROM @role_migration;
EXECUTE ensure_user_role;
DEALLOCATE PREPARE ensure_user_role;

SET @permanent_ban_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_permanently_banned'
);
SET @permanent_ban_migration = IF(@permanent_ban_exists = 0,
    'ALTER TABLE users ADD COLUMN is_permanently_banned TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE ensure_permanent_ban FROM @permanent_ban_migration;
EXECUTE ensure_permanent_ban;
DEALLOCATE PREPARE ensure_permanent_ban;

SET @banned_until_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'banned_until'
);
SET @banned_until_migration = IF(@banned_until_exists = 0,
    'ALTER TABLE users ADD COLUMN banned_until DATETIME NULL', 'SELECT 1');
PREPARE ensure_banned_until FROM @banned_until_migration;
EXECUTE ensure_banned_until;
DEALLOCATE PREPARE ensure_banned_until;

SET @ban_reason_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'ban_reason'
);
SET @ban_reason_migration = IF(@ban_reason_exists = 0,
    'ALTER TABLE users ADD COLUMN ban_reason VARCHAR(500) NULL', 'SELECT 1');
PREPARE ensure_ban_reason FROM @ban_reason_migration;
EXECUTE ensure_ban_reason;
DEALLOCATE PREPARE ensure_ban_reason;

CREATE TABLE IF NOT EXISTS posts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255) NULL,
    visibility ENUM('public', 'followers', 'private') NOT NULL DEFAULT 'public',
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    shared_post_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_posts_created_at (created_at),
    KEY idx_posts_user_id (user_id),
    KEY idx_posts_shared_post (shared_post_id),
    CONSTRAINT fk_posts_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_posts_shared_post FOREIGN KEY (shared_post_id) REFERENCES posts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @visibility_column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'visibility'
);
SET @visibility_migration = IF(
    @visibility_column_exists = 0,
    'ALTER TABLE posts ADD COLUMN visibility ENUM(''public'', ''followers'', ''private'') NOT NULL DEFAULT ''public''',
    'SELECT 1'
);
PREPARE ensure_post_visibility FROM @visibility_migration;
EXECUTE ensure_post_visibility;
DEALLOCATE PREPARE ensure_post_visibility;

SET @pinned_column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'is_pinned'
);
SET @pinned_migration = IF(
    @pinned_column_exists = 0,
    'ALTER TABLE posts ADD COLUMN is_pinned TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT 1'
);
PREPARE ensure_post_pinned FROM @pinned_migration;
EXECUTE ensure_post_pinned;
DEALLOCATE PREPARE ensure_post_pinned;

SET @shared_post_column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND COLUMN_NAME = 'shared_post_id'
);
SET @shared_post_migration = IF(
    @shared_post_column_exists = 0,
    'ALTER TABLE posts ADD COLUMN shared_post_id INT UNSIGNED NULL, ADD KEY idx_posts_shared_post (shared_post_id)',
    'SELECT 1'
);
PREPARE ensure_shared_post FROM @shared_post_migration;
EXECUTE ensure_shared_post;
DEALLOCATE PREPARE ensure_shared_post;

SET @shared_post_fk_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'posts' AND CONSTRAINT_NAME = 'fk_posts_shared_post'
);
SET @shared_post_fk_migration = IF(
    @shared_post_fk_exists = 0,
    'ALTER TABLE posts ADD CONSTRAINT fk_posts_shared_post FOREIGN KEY (shared_post_id) REFERENCES posts (id) ON DELETE CASCADE',
    'SELECT 1'
);
PREPARE ensure_shared_post_fk FROM @shared_post_fk_migration;
EXECUTE ensure_shared_post_fk;
DEALLOCATE PREPARE ensure_shared_post_fk;

CREATE TABLE IF NOT EXISTS comments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    parent_comment_id INT UNSIGNED NULL,
    content VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_comments_post_created (post_id, created_at),
    KEY idx_comments_user_id (user_id),
    KEY idx_comments_parent (parent_comment_id),
    CONSTRAINT fk_comments_post FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_comments_parent FOREIGN KEY (parent_comment_id) REFERENCES comments (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @parent_comment_column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'comments' AND COLUMN_NAME = 'parent_comment_id'
);
SET @parent_comment_migration = IF(
    @parent_comment_column_exists = 0,
    'ALTER TABLE comments ADD COLUMN parent_comment_id INT UNSIGNED NULL, ADD KEY idx_comments_parent (parent_comment_id), ADD CONSTRAINT fk_comments_parent FOREIGN KEY (parent_comment_id) REFERENCES comments (id) ON DELETE CASCADE',
    'SELECT 1'
);
PREPARE ensure_comment_parent FROM @parent_comment_migration;
EXECUTE ensure_comment_parent;
DEALLOCATE PREPARE ensure_comment_parent;

CREATE TABLE IF NOT EXISTS likes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_likes_post_user (post_id, user_id),
    KEY idx_likes_user_id (user_id),
    CONSTRAINT fk_likes_post FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
    CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS followers (
    follower_id INT UNSIGNED NOT NULL,
    followed_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (follower_id, followed_id),
    KEY idx_followers_followed (followed_id),
    CONSTRAINT fk_followers_follower FOREIGN KEY (follower_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_followers_followed FOREIGN KEY (followed_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS favorites (
    user_id INT UNSIGNED NOT NULL,
    post_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, post_id),
    KEY idx_favorites_post (post_id),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_post FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comment_likes (
    comment_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (comment_id, user_id),
    KEY idx_comment_likes_user (user_id),
    CONSTRAINT fk_comment_likes_comment FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_likes_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    recipient_id INT UNSIGNED NOT NULL,
    actor_id INT UNSIGNED NOT NULL,
    type ENUM('mention', 'post_like', 'comment_like', 'comment_reply', 'comment', 'share', 'follow', 'activity', 'moderation_warning', 'temporary_ban', 'permanent_ban', 'ban_lifted') NOT NULL,
    message VARCHAR(500) NULL,
    post_id INT UNSIGNED NULL,
    comment_id INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_notifications_recipient (recipient_id, created_at),
    CONSTRAINT fk_notifications_recipient FOREIGN KEY (recipient_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_actor FOREIGN KEY (actor_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT fk_notifications_post_preserve FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE SET NULL,
    CONSTRAINT fk_notifications_comment_preserve FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE notifications MODIFY COLUMN type ENUM('mention', 'post_like', 'comment_like', 'comment_reply', 'comment', 'share', 'follow', 'activity', 'moderation_warning', 'temporary_ban', 'permanent_ban', 'ban_lifted') NOT NULL;
SET @notification_post_delete_rule = (
    SELECT DELETE_RULE FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND CONSTRAINT_NAME = 'fk_notifications_post'
);
SET @notification_post_fk_migration = IF(
    @notification_post_delete_rule = 'CASCADE',
    'ALTER TABLE notifications DROP FOREIGN KEY fk_notifications_post, DROP INDEX fk_notifications_post, ADD CONSTRAINT fk_notifications_post_preserve FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE ensure_notification_post_fk FROM @notification_post_fk_migration;
EXECUTE ensure_notification_post_fk;
DEALLOCATE PREPARE ensure_notification_post_fk;
SET @notification_comment_delete_rule = (
    SELECT DELETE_RULE FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND CONSTRAINT_NAME = 'fk_notifications_comment'
);
SET @notification_comment_fk_migration = IF(
    @notification_comment_delete_rule = 'CASCADE',
    'ALTER TABLE notifications DROP FOREIGN KEY fk_notifications_comment, DROP INDEX fk_notifications_comment, ADD CONSTRAINT fk_notifications_comment_preserve FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE ensure_notification_comment_fk FROM @notification_comment_fk_migration;
EXECUTE ensure_notification_comment_fk;
DEALLOCATE PREPARE ensure_notification_comment_fk;
SET @notification_message_column_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND COLUMN_NAME = 'message'
);
SET @notification_message_migration = IF(
    @notification_message_column_exists = 0,
    'ALTER TABLE notifications ADD COLUMN message VARCHAR(500) NULL',
    'SELECT 1'
);
PREPARE ensure_notification_message FROM @notification_message_migration;
EXECUTE ensure_notification_message;
DEALLOCATE PREPARE ensure_notification_message;
UPDATE notifications SET type = 'activity' WHERE type = '';

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(64) NOT NULL,
    setting_value VARCHAR(255) NOT NULL,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('site_name', 'MVXB ChatSpace'), ('site_logo', '');

-- Demo accounts use the password: commonplace123
INSERT IGNORE INTO users (id, username, email, password, full_name, bio, role) VALUES
(1, 'maya', 'maya@example.test', '$2y$10$QZ8jb4NWQgyGymle3XFlG.GX9HuNdsglrYRSik5B7i4J1koeW146a', 'Maya Santos', 'Collecting little moments, good books, and new places.', 'user'),
(2, 'eli', 'eli@example.test', '$2y$10$QZ8jb4NWQgyGymle3XFlG.GX9HuNdsglrYRSik5B7i4J1koeW146a', 'Eli Navarro', 'Usually outside. Always up for a long conversation.', 'user'),
(3, 'samira', 'samira@example.test', '$2y$10$QZ8jb4NWQgyGymle3XFlG.GX9HuNdsglrYRSik5B7i4J1koeW146a', 'Samira Lim', 'Making things slowly and sharing them often.', 'user');

-- Admin demo account: username admin, password MiniAdmin2026!
INSERT IGNORE INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@mini-social.local', '$2y$10$Djo/x/GAwkrDP4GfFvt7BuCwhdUEPQiwo/V/GjNklHcsVPRJstejK', 'Mini Social Admin', 'admin');

INSERT IGNORE INTO posts (id, user_id, content, created_at) VALUES
(1, 1, 'Took the long way home today and found a tiny bookshop tucked behind the market. Sometimes the best plans are the ones you didn’t make.', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 2, 'A small reminder from this morning: you don’t have to turn every quiet moment into something productive. Coffee counts as a plan.', DATE_SUB(NOW(), INTERVAL 95 MINUTE)),
(3, 3, 'What’s a song you can listen to on repeat and never get tired of? I’m making a playlist for slow Sunday mornings.', DATE_SUB(NOW(), INTERVAL 22 MINUTE));

INSERT IGNORE INTO comments (id, post_id, user_id, content, created_at) VALUES
(1, 1, 2, 'Those are always the best finds. What did you pick up?', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 1, 1, 'A collection of short stories I’ve been meaning to read!', DATE_SUB(NOW(), INTERVAL 110 MINUTE)),
(3, 2, 3, 'This is the permission I needed today. Thank you.', DATE_SUB(NOW(), INTERVAL 70 MINUTE)),
(4, 3, 1, 'Anything by Sade belongs on a slow Sunday playlist.', DATE_SUB(NOW(), INTERVAL 10 MINUTE));

INSERT IGNORE INTO likes (post_id, user_id) VALUES
(1, 2), (1, 3), (2, 1), (2, 3), (3, 1), (3, 2);