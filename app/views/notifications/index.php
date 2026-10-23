<div class="feed-layout notifications-layout">
    <aside class="feed-rail">
        <span class="eyebrow">Your corner</span>
        <a class="rail-link" href="<?= e(url(['page' => 'feed'])) ?>"><i class="bi bi-house-door"></i> The feed</a>
        <a class="rail-link" href="<?= e(url(['page' => 'search-users'])) ?>"><i class="bi bi-people"></i> Find people</a>
        <a class="rail-link" href="<?= e(url(['page' => 'favorites'])) ?>"><i class="bi bi-bookmark-heart"></i> Favorites</a>
        <a class="rail-link rail-notifications active" href="<?= e(url(['page' => 'notifications'])) ?>"><i class="bi bi-bell"></i> Notifications</a>
    </aside>
    <section class="notifications-page">
        <div class="section-heading">
            <div><span class="eyebrow">A little heads-up</span><h1>Notifications</h1></div>
        </div>
        <div class="notification-list">
            <?php foreach ($notifications as $notification):
                $description = match ($notification['type']) {
                    'mention' => 'mentioned you' . ($notification['comment_id'] ? ' in a comment' : ' in a post'),
                    'post_like' => 'hearted your post',
                    'comment_like' => 'hearted your comment',
                    'comment' => 'commented on your post',
                    'comment_reply' => 'replied to your comment',
                    'share' => 'shared your post',
                    'activity' => 'interacted with your post',
                    default => 'interacted with you',
                };
                $isCommentActivity = in_array($notification['type'], ['comment', 'comment_like', 'comment_reply'], true)
                    || ($notification['type'] === 'mention' && $notification['comment_id']);
                $excerpt = $isCommentActivity ? $notification['comment_content'] : $notification['post_content'];
                $icon = match ($notification['type']) {
                    'mention' => 'bi-at',
                    'post_like', 'comment_like' => 'bi-heart-fill',
                    'comment', 'comment_reply' => 'bi-chat-left-text-fill',
                    'share' => 'bi-share-fill',
                    default => 'bi-bell-fill',
                };
                $postUrl = url(['page' => 'feed']) . '#post-' . (int) $notification['post_id'];
            ?>
                <a class="notification-row <?= $notification['read_at'] === null ? 'is-unread' : '' ?>" href="<?= e($postUrl) ?>">
                    <span class="notification-icon"><i class="bi <?= e($icon) ?>"></i></span>
                    <span class="notification-copy"><strong>@<?= e($notification['actor_username']) ?></strong> <?= e($description) ?>
                        <?php if ($excerpt !== null && $excerpt !== ''): ?><small><?= e($excerpt) ?></small><?php endif; ?>
                    </span>
                    <time datetime="<?= e($notification['created_at']) ?>"><?= e(date('M j, g:i a', strtotime($notification['created_at']))) ?></time>
                    <?php if ($notification['read_at'] === null): ?><span class="notification-unread-dot" aria-label="Unread"></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
            <?php if (!$notifications): ?><div class="empty-state"><span class="empty-icon"><i class="bi bi-bell"></i></span><h2>All caught up.</h2><p>Reactions, comments, replies, shares, and mentions on your posts and comments will show up here.</p></div><?php endif; ?>
        </div>
    </section>
</div>
