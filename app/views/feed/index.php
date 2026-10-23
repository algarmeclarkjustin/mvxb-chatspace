<div class="feed-layout">
    <aside class="feed-rail">
        <span class="eyebrow">Your corner</span>
        <a class="rail-link active" href="<?= e(url(['page' => 'feed'])) ?>"><i class="bi bi-house-door"></i> The feed</a>
        <a class="rail-link" href="<?= e(url(['page' => 'search-users'])) ?>"><i class="bi bi-people"></i> Find people</a>
        <div class="rail-note"><i class="bi bi-chat-heart"></i>
            <p>Good conversations start with showing up.</p>
        </div>
    </aside>

    <section class="feed-main">
        <div class="section-heading">
            <div><span class="eyebrow">Monday to whenever</span>
                <h1>Your feed</h1>
            </div><span class="live-dot">A shared space</span>
        </div>
        <form class="composer" action="<?= e(url(['page' => 'post-create'])) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="composer-top">
                <?php if (!empty($viewer['profile_image'])): ?><img class="avatar" src="public/uploads/<?= e($viewer['profile_image']) ?>" alt="">
                <?php else: ?><span class="avatar avatar-initial"><?= e(strtoupper(substr($viewer['full_name'] ?? $viewer['username'], 0, 1))) ?></span><?php endif; ?>
                <textarea name="content" rows="2" maxlength="2000" placeholder="What’s on your mind, <?= e(explode(' ', $viewer['full_name'] ?? $viewer['username'])[0]) ?>?"></textarea>
            </div>
            <div class="image-preview" data-image-preview hidden><img alt="Selected photo preview"><button class="image-preview-clear" type="button" data-clear-image aria-label="Remove selected photo"><i class="bi bi-x-lg"></i></button></div>
            <div class="composer-bottom"><label class="file-pick"><i class="bi bi-image"></i><span>Add a photo</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"></label><span class="quiet-hint">Keep it kind. Keep it you.</span><button class="button button-primary" type="submit">Share <i class="bi bi-arrow-up-right"></i></button></div>
        </form>

        <?php if ($query !== ''): ?><div class="search-result-line">Showing posts for “<?= e($query) ?>” <a href="<?= e(url(['page' => 'feed'])) ?>">Clear</a></div><?php endif; ?>

        <div class="post-list">
            <?php foreach ($posts as $post): ?>
                <?php require __DIR__ . '/../posts/card.php'; ?>
            <?php endforeach; ?>
            <?php if (!$posts): ?><div class="empty-state"><span class="empty-icon"><i class="bi bi-feather"></i></span>
                    <h2><?= $query ? 'Nothing found just yet.' : 'A quiet beginning.' ?></h2>
                    <p><?= $query ? 'Try another search, or clear it to see the whole feed.' : 'Be the first to share a thought with your community.' ?></p>
                </div><?php endif; ?>
        </div>
    </section>

    <aside class="feed-aside">
        <section class="aside-section">
            <div class="aside-heading"><span class="eyebrow">Around here</span><a href="<?= e(url(['page' => 'search-users'])) ?>">Find more</a></div>
            <?php foreach (array_slice($suggestions, 0, 5) as $person): if ((int) $person['id'] === (int) $viewer['id']) continue; ?>
                <a class="person-row" href="<?= e(url(['page' => 'profile', 'username' => $person['username']])) ?>">
                    <?php if (!empty($person['profile_image'])): ?><img class="avatar avatar-small" src="public/uploads/<?= e($person['profile_image']) ?>" alt="">
                    <?php else: ?><span class="avatar avatar-small avatar-initial"><?= e(strtoupper(substr($person['full_name'], 0, 1))) ?></span><?php endif; ?>
                    <span class="person-copy"><strong><?= e($person['full_name']) ?></strong><small>@<?= e($person['username']) ?></small></span><i class="bi bi-arrow-up-right"></i>
                </a>
            <?php endforeach; ?>
        </section>
        <p class="aside-foot">A place to connect, not to keep score.</p>
    </aside>
</div>