<?php
$notificationGroups = ['Today' => [], 'Earlier' => []];
foreach ($notifications as $notification) {
    $group = date('Y-m-d', strtotime($notification['created_at'])) === date('Y-m-d') ? 'Today' : 'Earlier';
    $notificationGroups[$group][] = $notification;
}
?>
<div class="feed-layout notifications-layout">
    <aside class="feed-rail">
        <span class="eyebrow">Your corner</span>
        <a class="rail-link" href="<?= e(url(['page' => 'feed'])) ?>"><i class="bi bi-house-door"></i> The feed</a>
        <a class="rail-link" href="<?= e(url(['page' => 'search-users'])) ?>"><i class="bi bi-people"></i> Find people</a>
        <a class="rail-link" href="<?= e(url(['page' => 'favorites'])) ?>"><i class="bi bi-bookmark-heart"></i> Favorites</a>
        <a class="rail-link rail-notifications active" href="<?= e(url(['page' => 'notifications'])) ?>"><i class="bi bi-bell"></i> Notifications<?php if ($unreadNotificationCount > 0): ?><span class="notification-badge"><?= (int) $unreadNotificationCount ?></span><?php endif; ?></a>
    </aside>
    <section class="notifications-page">
        <div class="section-heading">
            <div><span class="eyebrow">A little heads-up</span><h1>Notifications</h1></div>
            <span class="notification-total"><?= (int) $unreadNotificationCount ?> unread</span>
        </div>
        <nav class="notification-tabs" aria-label="Filter notifications">
            <a class="<?= $notificationFilter === 'all' ? 'active' : '' ?>" href="<?= e(url(['page' => 'notifications', 'filter' => 'all'])) ?>">All</a>
            <a class="<?= $notificationFilter === 'unread' ? 'active' : '' ?>" href="<?= e(url(['page' => 'notifications', 'filter' => 'unread'])) ?>">Unread</a>
        </nav>
        <?php foreach ($notificationGroups as $groupName => $groupNotifications): if (!$groupNotifications) continue; ?>
            <section class="notification-group">
                <h2><?= e($groupName) ?></h2>
                <?php foreach ($groupNotifications as $notification):
                    $description = match ($notification['type']) {
                        'mention' => 'mentioned you' . ($notification['comment_id'] ? ' in a comment' : ' in a post'),
                        'post_like' => 'hearted your post',
                        'comment_like' => 'hearted your comment',
                        'comment' => 'commented on your post',
                        'comment_reply' => 'replied to your comment',
                        'share' => 'shared your post',
                        'activity' => 'interacted with your post',
                        'moderation_warning' => 'sent you a community warning',
                        'temporary_ban' => 'temporarily suspended your account',
                        'permanent_ban' => 'permanently suspended your account',
                        'ban_lifted' => 'lifted your account restriction',
                        default => 'interacted with you',
                    };
                    $isCommentActivity = in_array($notification['type'], ['comment', 'comment_like', 'comment_reply'], true)
                        || ($notification['type'] === 'mention' && $notification['comment_id']);
                    $excerpt = str_starts_with($notification['type'], 'moderation_') || str_ends_with($notification['type'], '_ban') || $notification['type'] === 'ban_lifted'
                        ? $notification['message']
                        : ($isCommentActivity ? $notification['comment_content'] : $notification['post_content']);
                    $icon = match ($notification['type']) {
                        'mention' => 'bi-at',
                        'post_like', 'comment_like' => 'bi-heart-fill',
                        'comment', 'comment_reply' => 'bi-chat-left-text-fill',
                        'share' => 'bi-share-fill',
                        'moderation_warning', 'temporary_ban', 'permanent_ban' => 'bi-shield-exclamation',
                        'ban_lifted' => 'bi-shield-check',
                        default => 'bi-bell-fill',
                    };
                ?>
                    <form class="notification-row-form" action="<?= e(url(['page' => 'notification-open'])) ?>" method="post">
                        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $notification['id'] ?>">
                        <button class="notification-row <?= $notification['read_at'] === null ? 'is-unread' : '' ?>" type="submit">
                            <?php if (!empty($notification['actor_image'])): ?><img class="notification-avatar" src="public/uploads/<?= e($notification['actor_image']) ?>" alt="">
                            <?php else: ?><span class="notification-avatar avatar-initial"><?= e(strtoupper(substr($notification['actor_name'] ?? $notification['actor_username'], 0, 1))) ?></span><?php endif; ?>
                            <span class="notification-event-icon"><i class="bi <?= e($icon) ?>"></i></span>
                            <span class="notification-copy"><strong><?= e($notification['actor_name'] ?? '@' . $notification['actor_username']) ?></strong> <span class="notification-actor-username">@<?= e($notification['actor_username']) ?></span> <?= e($description) ?>
                                <?php if ($excerpt !== null && $excerpt !== ''): ?><small><?= e($excerpt) ?></small><?php endif; ?>
                                <time datetime="<?= e($notification['created_at']) ?>"><?= e(date('M j, g:i a', strtotime($notification['created_at']))) ?></time>
                            </span>
                            <?php if ($notification['read_at'] === null): ?><span class="notification-unread-dot" aria-label="Unread"></span><?php endif; ?>
                        </button>
                    </form>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
        <?php if (!$notifications): ?><div class="empty-state"><span class="empty-icon"><i class="bi bi-bell"></i></span><h2><?= $notificationFilter === 'unread' ? 'No unread notifications.' : 'All caught up.' ?></h2><p>Reactions, comments, replies, shares, mentions, and moderation updates will show up here.</p></div><?php endif; ?>
    </section>
</div>
