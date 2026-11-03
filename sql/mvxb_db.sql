-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 03, 2026 at 06:25 PM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_43249573_mvxb_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `parent_comment_id` int(10) UNSIGNED DEFAULT NULL,
  `content` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `parent_comment_id`, `content`, `created_at`) VALUES
(10, 7, 8, NULL, 'ok', '2026-10-23 10:34:37'),
(12, 4, 4, NULL, '@cheryl', '2026-10-23 11:43:03'),
(13, 13, 6, NULL, 'HAHAHA', '2026-11-03 00:28:50');

-- --------------------------------------------------------

--
-- Table structure for table `comment_likes`
--

CREATE TABLE `comment_likes` (
  `comment_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`user_id`, `post_id`, `created_at`) VALUES
(4, 13, '2026-10-29 07:21:47'),
(5, 6, '2026-10-23 10:11:33');

-- --------------------------------------------------------

--
-- Table structure for table `followers`
--

CREATE TABLE `followers` (
  `follower_id` int(10) UNSIGNED NOT NULL,
  `followed_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `followers`
--

INSERT INTO `followers` (`follower_id`, `followed_id`, `created_at`) VALUES
(4, 11, '2026-10-29 06:34:48'),
(5, 6, '2026-10-23 09:55:57'),
(5, 7, '2026-10-23 09:55:39'),
(6, 11, '2026-11-03 00:28:26'),
(8, 5, '2026-10-23 10:07:45'),
(8, 7, '2026-10-23 10:07:31'),
(11, 5, '2026-10-29 06:14:33'),
(12, 4, '2026-10-30 09:43:07');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
(9, 7, 5, '2026-10-23 10:00:37'),
(10, 6, 8, '2026-10-23 10:08:10'),
(11, 4, 8, '2026-10-23 10:08:11'),
(13, 8, 5, '2026-10-23 10:21:44'),
(14, 7, 8, '2026-10-23 10:28:28'),
(15, 4, 7, '2026-10-23 10:34:35'),
(16, 6, 7, '2026-10-23 10:34:37'),
(17, 7, 7, '2026-10-23 10:34:39'),
(18, 8, 7, '2026-10-23 10:34:41'),
(20, 13, 4, '2026-10-29 06:33:55'),
(21, 14, 4, '2026-10-31 16:49:10'),
(22, 15, 6, '2026-11-03 00:27:55'),
(23, 14, 6, '2026-11-03 00:28:32'),
(24, 13, 6, '2026-11-03 00:28:35');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `recipient_id` int(10) UNSIGNED NOT NULL,
  `actor_id` int(10) UNSIGNED NOT NULL,
  `type` enum('mention','post_like','comment_like','comment_reply','comment','share','follow','activity','moderation_warning','temporary_ban','permanent_ban','ban_lifted') NOT NULL,
  `message` varchar(500) DEFAULT NULL,
  `post_id` int(10) UNSIGNED DEFAULT NULL,
  `comment_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `recipient_id`, `actor_id`, `type`, `message`, `post_id`, `comment_id`, `created_at`, `read_at`) VALUES
(1, 5, 6, 'post_like', NULL, 4, NULL, '2026-10-23 09:47:12', '2026-10-23 09:47:20'),
(2, 5, 4, 'temporary_ban', 'An administrator suspended your account until Oct 23, 2026 5:52 am. Reason: sample', NULL, NULL, '2026-10-23 09:52:45', '2026-10-23 09:52:55'),
(3, 5, 4, 'ban_lifted', 'An administrator lifted the restriction on your account.', NULL, NULL, '2026-10-23 09:53:08', '2026-10-23 09:53:30'),
(4, 5, 4, 'permanent_ban', 'An administrator permanently suspended your account. Reason: sample', NULL, NULL, '2026-10-23 09:53:18', '2026-10-23 09:53:27'),
(5, 5, 4, 'ban_lifted', 'An administrator lifted the restriction on your account.', NULL, NULL, '2026-10-23 09:53:38', '2026-10-23 09:53:43'),
(6, 7, 5, 'follow', NULL, NULL, NULL, '2026-10-23 09:55:39', NULL),
(7, 6, 5, 'follow', NULL, NULL, NULL, '2026-10-23 09:55:57', '2026-11-03 00:28:59'),
(8, 7, 4, 'permanent_ban', 'An administrator permanently suspended your account. Reason: hello po, sample rani i balik ra gihapon nako', NULL, NULL, '2026-10-23 09:59:51', '2026-10-23 10:04:11'),
(9, 4, 5, 'post_like', NULL, 7, NULL, '2026-10-23 10:00:37', '2026-10-23 10:02:50'),
(10, 7, 4, 'ban_lifted', 'An administrator lifted the restriction on your account.', NULL, NULL, '2026-10-23 10:01:34', '2026-10-23 10:03:52'),
(11, 7, 8, 'follow', NULL, NULL, NULL, '2026-10-23 10:07:31', NULL),
(12, 5, 8, 'follow', NULL, NULL, NULL, '2026-10-23 10:07:45', '2026-10-23 10:13:39'),
(13, 5, 8, 'post_like', NULL, 6, NULL, '2026-10-23 10:08:10', '2026-10-23 10:13:35'),
(14, 5, 8, 'post_like', NULL, 4, NULL, '2026-10-23 10:08:11', '2026-10-23 10:11:38'),
(15, 8, 5, 'mention', NULL, 4, NULL, '2026-10-23 10:11:47', '2026-10-23 10:11:58'),
(16, 4, 8, 'post_like', NULL, 7, NULL, '2026-10-23 10:13:06', '2026-10-23 10:34:06'),
(17, 8, 5, 'follow', NULL, NULL, NULL, '2026-10-23 10:21:24', '2026-10-23 10:21:53'),
(18, 8, 5, 'post_like', NULL, 8, NULL, '2026-10-23 10:21:44', '2026-10-23 10:21:55'),
(19, 5, 8, 'comment', NULL, 6, NULL, '2026-10-23 10:22:11', '2026-10-23 10:22:17'),
(20, 8, 5, 'comment_reply', NULL, 6, NULL, '2026-10-23 10:22:21', '2026-10-23 10:22:50'),
(21, 5, 8, 'comment_reply', NULL, 6, NULL, '2026-10-23 10:22:31', '2026-10-23 10:42:29'),
(22, 5, 8, 'comment', NULL, 6, NULL, '2026-10-23 10:22:38', '2026-10-23 10:42:26'),
(23, 4, 8, 'post_like', NULL, 7, NULL, '2026-10-23 10:28:28', '2026-10-23 10:34:02'),
(24, 8, 4, 'moderation_warning', 'A moderator flagged your post. Please review the community rules. Reason: your post should legal', 8, NULL, '2026-10-23 10:29:27', '2026-10-23 10:29:41'),
(25, 5, 7, 'post_like', NULL, 4, NULL, '2026-10-23 10:34:35', '2026-10-23 10:41:38'),
(26, 4, 8, 'comment', NULL, 7, 10, '2026-10-23 10:34:37', '2026-10-23 11:42:20'),
(27, 5, 7, 'post_like', NULL, 6, NULL, '2026-10-23 10:34:37', '2026-10-23 10:41:31'),
(28, 4, 7, 'post_like', NULL, 7, NULL, '2026-10-23 10:34:39', '2026-10-23 11:31:23'),
(29, 8, 7, 'post_like', NULL, 8, NULL, '2026-10-23 10:34:41', '2026-10-23 11:20:34'),
(30, 7, 5, 'post_like', NULL, NULL, NULL, '2026-10-23 10:42:01', NULL),
(31, 7, 5, 'comment', NULL, NULL, NULL, '2026-10-23 10:42:11', NULL),
(32, 5, 4, 'comment', NULL, 4, 12, '2026-10-23 11:43:03', '2026-10-23 14:31:14'),
(33, 7, 4, 'mention', NULL, 4, 12, '2026-10-23 11:43:24', NULL),
(34, 5, 11, 'follow', NULL, NULL, NULL, '2026-10-29 06:14:33', NULL),
(35, 11, 4, 'post_like', NULL, 13, NULL, '2026-10-29 06:33:55', '2026-10-29 06:35:55'),
(36, 11, 4, 'follow', NULL, NULL, NULL, '2026-10-29 06:34:48', '2026-10-29 06:36:02'),
(37, 4, 12, 'follow', NULL, NULL, NULL, '2026-10-30 09:43:07', '2026-10-31 16:41:47'),
(38, 11, 4, 'post_like', NULL, 14, NULL, '2026-10-31 16:49:10', NULL),
(39, 11, 4, 'share', NULL, 16, NULL, '2026-10-31 16:49:17', NULL),
(40, 13, 6, 'post_like', NULL, 15, NULL, '2026-11-03 00:27:55', NULL),
(41, 11, 6, 'follow', NULL, NULL, NULL, '2026-11-03 00:28:26', NULL),
(42, 11, 6, 'post_like', NULL, 14, NULL, '2026-11-03 00:28:32', NULL),
(43, 11, 6, 'post_like', NULL, 13, NULL, '2026-11-03 00:28:35', NULL),
(44, 11, 6, 'comment', NULL, 13, 13, '2026-11-03 00:28:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `visibility` enum('public','followers','private') NOT NULL DEFAULT 'public',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `shared_post_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `image`, `visibility`, `is_pinned`, `shared_post_id`, `created_at`) VALUES
(4, 5, 'RMSC', NULL, 'public', 0, NULL, '2026-10-23 09:45:15'),
(6, 5, 'try dw ug comment', NULL, 'public', 0, NULL, '2026-10-23 09:54:52'),
(7, 4, 'Welcome Everyone', NULL, 'public', 0, NULL, '2026-10-23 10:00:32'),
(8, 8, 'im new here', NULL, 'public', 0, NULL, '2026-10-23 10:21:38'),
(11, 9, 'Padodot', NULL, 'public', 0, NULL, '2026-10-23 10:54:13'),
(12, 8, 'jo', NULL, 'followers', 0, NULL, '2026-10-23 11:45:06'),
(13, 11, 'Honest reaction when that \"specific person\" starts talking some nonsense shii', 'posts/0d959023fa9b5e536fd3e3a55ef89e7c.jpg', 'public', 0, NULL, '2026-10-29 06:29:09'),
(14, 11, 'Me soon when people start to get on my nerves even just for a bit', 'posts/1cf40283285f3095f1d7de63338c70bf.jpg', 'public', 0, NULL, '2026-10-29 06:35:05'),
(15, 13, 'Hello bre one', NULL, 'public', 0, NULL, '2026-10-30 10:57:24'),
(16, 4, '', NULL, 'public', 0, 13, '2026-10-31 16:49:17'),
(17, 14, '🌄', 'posts/19d25262d5e041e62daad0ab24427d69.png', 'public', 0, NULL, '2026-11-03 13:35:09');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(64) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES
('site_logo', 'branding/9e53fb89e8da89312559763b470deb3f.jpg'),
('site_name', 'MVXB ChatSpace');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(24) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(80) NOT NULL,
  `bio` varchar(240) NOT NULL DEFAULT '',
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `is_permanently_banned` tinyint(1) NOT NULL DEFAULT 0,
  `banned_until` datetime DEFAULT NULL,
  `ban_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `bio`, `profile_image`, `role`, `is_permanently_banned`, `banned_until`, `ban_reason`, `created_at`) VALUES
(4, 'admin', 'admin@mini-social.local', '$2y$10$Djo/x/GAwkrDP4GfFvt7BuCwhdUEPQiwo/V/GjNklHcsVPRJstejK', 'MVXB Admin', '', 'profiles/34da139b8995b50b74537cfb68bc6560.jpg', 'admin', 0, NULL, NULL, '2026-10-23 09:41:05'),
(5, 'clarkty', 'clark@gmail.com', '$2y$12$c0lNy9ylvXkwG3emKSAttO1l6Qp5LSuXehq4KzbeoXKRv90Ad5qFy', 'clark', 'just me nothing else', 'profiles/686ac0ceb2ad9121a0e1995b8589ab80.jpg', 'user', 0, NULL, NULL, '2026-10-23 09:44:09'),
(6, 'juna', 'junaliz372@gmail.com', '$2y$12$ZweFn8cEN0b6eFVxDtE53eTkdYZJxUCft528q4CBE1CcRZImCeSqy', 'Junaliz', '', 'profiles/9d7de02ae58908b5d6598625ac2109d9.jpg', 'user', 0, NULL, NULL, '2026-10-23 09:46:39'),
(7, 'che-che', 'cheisme@gmail.com', '$2y$12$6Aryea7LyUrp4syLCpTXSee9GWFud6YipcoNdhTspeEZMXqpWw7oG', 'Che', '', 'profiles/7edd557b6abbf0708844e9418ea87be7.jpg', 'user', 0, NULL, NULL, '2026-10-23 09:53:39'),
(8, 'justin', 'justin@gmail.com', '$2y$12$Be9W6I6Rsmj6e7Z3GZAMb.Dp0bnBUSG8N3hluLMtpnyA0w9UnStJy', 'justin', '', NULL, 'user', 0, NULL, NULL, '2026-10-23 10:06:00'),
(9, 'Mikegupana', 'mikegupana20@gmail.com', '$2y$12$DzsS.CG7OudIr41bCe3liuO8RGSE6rwrsytJlC0bgGkqPFlYwb7B6', 'Mikegupana', '', NULL, 'user', 0, NULL, NULL, '2026-10-23 10:52:21'),
(10, 'tonet', 'antonettealibanggo@gmail.com', '$2y$12$J4Rr31xM496jsPZ/T7LvPOsTo4M3RYAsdTZI8Wszc..Pd2cB09fiS', 'tonet', '', NULL, 'user', 0, NULL, NULL, '2026-10-24 03:33:58'),
(11, 'Xyeah', 'xynettethea_japson@smccnasipit.edu.ph', '$2y$12$5PB3USxObnhBPA9GqKYOLevGdd5x3gPtG3MkQFR4S9o4nsnJbQ/d.', 'Xynette Thea', 'Not here to make friends; hence, expressing my alter ego.', 'profiles/58e5c4722c1899d7b00d8bbfd4e8990d.jpg', 'user', 0, NULL, NULL, '2026-10-29 06:10:24'),
(12, 'Syndiah', 'canalescindy202@gmail.com', '$2y$12$C82IoAL.rExpALeJ3TPEtOtOmGYVRpyyV539F.48EsTLSinZ1SrL2', 'Syndiah', '', 'profiles/a1e78fd87d38dee09ba8162f8a5bb5a0.jpg', 'user', 0, NULL, NULL, '2026-10-30 09:37:43'),
(13, 'Vier', 'viersmiringhuy@gmail.com', '$2y$12$1DE9UY5P/Dsv03V17sp6/u5BVZU0UrpaVFktWD9NugjWiVCgnyl.O', 'Vier', '', NULL, 'user', 0, NULL, NULL, '2026-10-30 10:57:00'),
(14, 'brent', 'achitotshika@gmail.com', '$2y$12$Ry04K3ecLAe8tc.KuIq.Me2modqDp32Ru5CYA211TcJp7ZbXxsc3e', 'Brendon', '', NULL, 'user', 0, NULL, NULL, '2026-11-03 13:33:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comments_post_created` (`post_id`,`created_at`),
  ADD KEY `idx_comments_user_id` (`user_id`),
  ADD KEY `idx_comments_parent` (`parent_comment_id`);

--
-- Indexes for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`comment_id`,`user_id`),
  ADD KEY `idx_comment_likes_user` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`post_id`),
  ADD KEY `idx_favorites_post` (`post_id`);

--
-- Indexes for table `followers`
--
ALTER TABLE `followers`
  ADD PRIMARY KEY (`follower_id`,`followed_id`),
  ADD KEY `idx_followers_followed` (`followed_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_likes_post_user` (`post_id`,`user_id`),
  ADD KEY `idx_likes_user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_recipient` (`recipient_id`,`created_at`),
  ADD KEY `fk_notifications_actor` (`actor_id`),
  ADD KEY `fk_notifications_post_preserve` (`post_id`),
  ADD KEY `fk_notifications_comment_preserve` (`comment_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_posts_created_at` (`created_at`),
  ADD KEY `idx_posts_user_id` (`user_id`),
  ADD KEY `idx_posts_shared_post` (`shared_post_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments_parent` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `fk_comment_likes_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comment_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_favorites_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `followers`
--
ALTER TABLE `followers`
  ADD CONSTRAINT `fk_followers_followed` FOREIGN KEY (`followed_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_followers_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `fk_likes_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_likes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_notifications_comment_preserve` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notifications_post_preserve` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notifications_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_posts_shared_post` FOREIGN KEY (`shared_post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_posts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
