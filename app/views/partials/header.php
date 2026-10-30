<?php $flash = take_flash(); $viewer = current_user(); $currentPage = (string) ($_GET['page'] ?? ($viewer ? 'feed' : 'login')); $siteSettings = $GLOBALS['site_settings'] ?? ['site_name' => 'MVXB ChatSpace', 'site_logo' => '']; $siteName = $siteSettings['site_name'] ?? 'MVXB ChatSpace'; $siteLogo = $siteSettings['site_logo'] ?? ''; ?>
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
    <title><?= e($title ?? $siteName) ?> · <?= e($siteName) ?></title>
    <?php if ($siteLogo !== ''): ?><link rel="icon" href="public/uploads/<?= e($siteLogo) ?>"><?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,500;6..72,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="public/assets/app.css">
</head>
<body class="<?= in_array($currentPage, ['login', 'register'], true) ? 'auth-body' : '' ?>" data-notification-stream="<?= e($viewer ? url(['page' => 'notification-stream']) : '') ?>">
<header class="topbar">
    <a class="brand" href="<?= e(url(['page' => $viewer ? 'feed' : 'login'])) ?>" aria-label="<?= e($siteName) ?> home">
        <span class="brand-mark" aria-hidden="true"><?php if ($siteLogo !== ''): ?><img src="public/uploads/<?= e($siteLogo) ?>" alt=""><?php else: ?><i class="bi bi-chat-heart-fill"></i><?php endif; ?></span>
        <span><?= e($siteName) ?></span>
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
<?php if ($moderationAlert): ?>
    <?php
    $moderationTitle = match ($moderationAlert['type']) {
        'moderation_warning' => 'A post needs your attention',
        'temporary_ban' => 'Your account is temporarily suspended',
        'permanent_ban' => 'Your account has been suspended',
        'ban_lifted' => 'Your account is available again',
        default => 'Account notice',
    };
    ?>
    <dialog class="moderation-dialog" open aria-labelledby="moderation-title">
        <div class="moderation-dialog-card"><span class="moderation-alert-icon"><i class="bi bi-exclamation-triangle"></i></span><h2 id="moderation-title"><?= e($moderationTitle) ?></h2><p><?= e($moderationAlert['message'] ?? '') ?></p>
            <form action="<?= e(url(['page' => 'notification-dismiss'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $moderationAlert['id'] ?>"><button class="button button-primary" type="submit">Got it</button></form>
        </div>
    </dialog>
<?php endif; ?>
<main class="page-shell">