<?php $flash = take_flash(); $viewer = current_user(); $currentPage = (string) ($_GET['page'] ?? ($viewer ? 'feed' : 'login')); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <script>
        try {
            if (localStorage.getItem('chatspace-theme') === 'dark') {
                document.documentElement.dataset.theme = 'dark';
                document.querySelector('meta[name="theme-color"]').content = '#101614';
            }
        } catch (error) {
            console.warn('Theme preference could not be loaded.', error);
        }
    </script>
    <title><?= e($title ?? 'MVXB ChatSpace') ?> · MVXB ChatSpace</title>
    <link rel="icon" type="image/jpeg" href="files/MVXB%20Logo.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,500;6..72,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/assets/app.css">
</head>
<body class="<?= in_array($currentPage, ['login', 'register'], true) ? 'auth-body' : '' ?>">
<header class="topbar">
    <a class="brand" href="<?= e(url(['page' => $viewer ? 'feed' : 'login'])) ?>" aria-label="MVXB ChatSpace home">
        <span class="brand-mark" aria-hidden="true"><img src="files/MVXB%20Logo.jpg" alt=""></span>
        <span>MVXB ChatSpace</span>
    </a>
    <?php if ($viewer): ?>
        <form class="top-search" action="index.php" method="get">
            <input type="hidden" name="page" value="search-posts">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" placeholder="Search posts and topics" aria-label="Search posts and topics">
            <kbd>/</kbd>
        </form>
        <nav class="top-actions" aria-label="Account navigation">
            <a class="icon-link" href="<?= e(url(['page' => 'feed'])) ?>" aria-label="Home" title="Home"><i class="bi bi-house-door"></i></a>
            <a class="icon-link notification-link" href="<?= e(url(['page' => 'notifications'])) ?>" aria-label="Notifications<?= $unreadNotificationCount > 0 ? ', ' . (int) $unreadNotificationCount . ' unread' : '' ?>" title="Notifications<?= $unreadNotificationCount > 0 ? ' (' . (int) $unreadNotificationCount . ' unread)' : '' ?>"><i class="bi bi-bell"></i><?php if ($unreadNotificationCount > 0): ?><span class="notification-badge"><?= (int) $unreadNotificationCount ?></span><?php endif; ?></a>
            <?php if (($viewer['role'] ?? 'user') === 'admin'): ?><a class="admin-nav-link" href="<?= e(url(['page' => 'admin'])) ?>"><i class="bi bi-shield-check"></i> Admin</a><?php endif; ?>
            <a class="viewer-link" href="<?= e(url(['page' => 'profile', 'username' => $viewer['username']])) ?>">
                <?php if (!empty($viewer['profile_image'])): ?><img class="avatar avatar-small" src="public/uploads/<?= e($viewer['profile_image']) ?>" alt="">
                <?php else: ?><span class="avatar avatar-small avatar-initial"><?= e(strtoupper(substr($viewer['full_name'] ?? $viewer['username'], 0, 1))) ?></span><?php endif; ?>
                <span><?= e($viewer['username']) ?></span>
            </a>
            <form action="<?= e(url(['page' => 'logout'])) ?>" method="post">
                <?= csrf_field() ?><button class="icon-link" type="submit" aria-label="Sign out" title="Sign out"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </nav>
    <?php endif; ?>
    <button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch to dark mode" title="Switch to dark mode"><i class="bi bi-moon-stars" aria-hidden="true"></i></button>
</header>
<?php if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>" role="status"><i class="bi <?= $flash['type'] === 'error' ? 'bi-exclamation-circle' : 'bi-check-circle' ?>"></i><?= e($flash['message']) ?></div>
<?php endif; ?>
<main class="page-shell">