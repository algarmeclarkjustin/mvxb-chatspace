<section class="restricted-page">
    <span class="moderation-alert-icon"><i class="bi bi-shield-exclamation"></i></span>
    <span class="eyebrow">Account access paused</span>
    <h1><?= !empty($activeBan['is_permanently_banned']) ? 'Your account is permanently banned.' : 'Your account is temporarily suspended.' ?></h1>
    <?php if (empty($activeBan['is_permanently_banned']) && !empty($activeBan['banned_until'])): ?><p class="restricted-expiry">You can sign in again after <strong><?= e(date('F j, Y \a\t g:i a', strtotime($activeBan['banned_until']))) ?></strong>.</p><?php endif; ?>
    <?php if (!empty($activeBan['ban_reason'])): ?><div class="restricted-reason"><strong>Reason from the administrator</strong><p><?= e($activeBan['ban_reason']) ?></p></div><?php endif; ?>
    <p>You can’t use your account while this restriction is active. If you believe this is a mistake, contact the site administrator.</p>
    <form action="<?= e(url(['page' => 'logout'])) ?>" method="post"><?= csrf_field() ?><button class="button button-outline" type="submit">Sign out</button></form>
</section>
