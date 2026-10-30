</main>
<?php if (current_user()): ?>
<nav class="mobile-nav" aria-label="Mobile navigation">
    <a href="<?= e(url(['page' => 'feed'])) ?>" aria-label="Home"><i class="bi bi-house-door"></i></a>
    <a href="<?= e(url(['page' => 'search-users'])) ?>" aria-label="Find people"><i class="bi bi-search"></i></a>
    <a href="<?= e(url(['page' => 'favorites'])) ?>" aria-label="Favorites"><i class="bi bi-bookmark-heart"></i></a>
    <a class="mobile-notifications" href="<?= e(url(['page' => 'notifications'])) ?>" aria-label="Notifications<?= $unreadNotificationCount > 0 ? ', ' . (int) $unreadNotificationCount . ' unread' : '' ?>"><i class="bi bi-bell"></i><?php if ($unreadNotificationCount > 0): ?><span class="notification-badge" data-notification-badge><?= (int) $unreadNotificationCount ?></span><?php else: ?><span class="notification-badge" data-notification-badge hidden>0</span><?php endif; ?></a>
    <a href="<?= e(url(['page' => 'profile', 'username' => current_user()['username']])) ?>" aria-label="Profile"><i class="bi bi-person"></i></a>
</nav>
<?php endif; ?>
<dialog class="image-viewer" data-image-viewer aria-label="Image viewer">
    <button class="image-viewer-close" type="button" data-image-viewer-close aria-label="Close image"><i class="bi bi-x-lg"></i></button>
    <img data-image-viewer-image alt="">
</dialog>
<script src="public/assets/app.js" defer></script>
</body>
</html>