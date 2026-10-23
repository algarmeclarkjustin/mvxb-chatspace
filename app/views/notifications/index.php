<section class="narrow-page notifications-page">
    <div class="section-heading">
        <div><span class="eyebrow">A little heads-up</span><h1>Notifications</h1></div>
    </div>
    <div class="notification-list">
        <?php foreach ($notifications as $notification):
            $description = match ($notification['type']) {
                'mention' => 'mentioned you',
                'post_like' => 'hearted your post',
                'comment_like' => 'hearted your comment',
                default => 'interacted with you',
            };
        ?>
            <a class="notification-row" href="<?= e(url(['page' => 'feed']) . '#post-' . (int) $notification['post_id']) ?>">
                <span class="notification-icon"><i class="bi <?= $notification['type'] === 'mention' ? 'bi-at' : 'bi-heart-fill' ?>"></i></span>
                <span class="notification-copy"><strong><?= e($notification['actor_name']) ?></strong> <?= e($description) ?><?php if ($notification['type'] === 'mention' && $notification['comment_id']): ?> in a comment<?php endif; ?>
                </span>
                <time datetime="<?= e($notification['created_at']) ?>"><?= e(date('M j, g:i a', strtotime($notification['created_at']))) ?></time>
            </a>
        <?php endforeach; ?>
        <?php if (!$notifications): ?><div class="empty-state"><span class="empty-icon"><i class="bi bi-bell"></i></span><h2>All caught up.</h2><p>Mentions and hearts on your posts and comments will show up here.</p></div><?php endif; ?>
    </div>
</section>
