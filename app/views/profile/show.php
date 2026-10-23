<section class="profile-page">
    <a class="back-link" href="<?= e(url(['page' => 'feed'])) ?>"><i class="bi bi-arrow-left"></i> Back to the feed</a>
    <header class="profile-header">
        <?php if (!empty($profile['profile_image'])): ?><button class="profile-avatar-trigger" type="button" data-image-viewer-trigger aria-label="View profile photo of <?= e($profile['full_name']) ?>"><img class="profile-avatar" src="public/uploads/<?= e($profile['profile_image']) ?>" alt="<?= e($profile['full_name']) ?>"></button>
        <?php else: ?><span class="profile-avatar avatar-initial"><?= e(strtoupper(substr($profile['full_name'], 0, 1))) ?></span><?php endif; ?>
        <div class="profile-identity"><span class="eyebrow">A little about me</span><h1><?= e($profile['full_name']) ?></h1><p>@<?= e($profile['username']) ?></p></div>
        <?php if ($isOwner): ?><a class="button button-outline" href="<?= e(url(['page' => 'profile-edit'])) ?>"><i class="bi bi-pencil"></i> Edit profile</a><?php elseif ($viewer): ?><form class="profile-follow-form" action="<?= e(url(['page' => 'follow-toggle'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="username" value="<?= e($profile['username']) ?>"><button class="button <?= $isFollowing ? 'button-outline' : 'button-primary' ?>" type="submit"><i class="bi <?= $isFollowing ? 'bi-person-check' : 'bi-person-plus' ?>"></i> <?= $isFollowing ? 'Following' : 'Follow' ?></button></form><?php endif; ?>
        <p class="profile-bio"><?= $profile['bio'] !== '' ? nl2br(e($profile['bio'])) : 'Still finding the words.' ?></p>
        <div class="profile-meta"><span><i class="bi bi-calendar3"></i> Here since <?= e(date('F Y', strtotime($profile['created_at']))) ?></span><span><i class="bi bi-chat-heart"></i> <?= count($posts) ?> <?= count($posts) === 1 ? 'post' : 'posts' ?></span><span><i class="bi bi-people"></i> <?= (int) $followerCount ?> followers</span></div>
    </header>
    <div class="section-heading profile-post-heading"><div><span class="eyebrow">From <?= e(explode(' ', $profile['full_name'])[0]) ?></span><h2>Posts</h2></div></div>
    <div class="post-list profile-posts">
        <?php foreach ($posts as $post): ?>
            <?php require __DIR__ . '/../posts/card.php'; ?>
        <?php endforeach; ?>
        <?php if (!$posts): ?><div class="empty-state compact-empty"><span class="empty-icon"><i class="bi bi-feather"></i></span><h2>No posts yet.</h2><p>When a thought is ready to be shared, it’ll show up here.</p></div><?php endif; ?>
    </div>
</section>