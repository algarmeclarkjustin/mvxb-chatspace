<section class="profile-page">
    <a class="back-link" href="<?= e(url(['page' => 'feed'])) ?>"><i class="bi bi-arrow-left"></i> Back to the feed</a>
    <header class="profile-header">
        <?php if (!empty($profile['profile_image'])): ?><img class="profile-avatar" src="public/uploads/<?= e($profile['profile_image']) ?>" alt="<?= e($profile['full_name']) ?>">
        <?php else: ?><span class="profile-avatar avatar-initial"><?= e(strtoupper(substr($profile['full_name'], 0, 1))) ?></span><?php endif; ?>
        <div class="profile-identity"><span class="eyebrow">A little about me</span><h1><?= e($profile['full_name']) ?></h1><p>@<?= e($profile['username']) ?></p></div>
        <?php if ($isOwner): ?><a class="button button-outline" href="<?= e(url(['page' => 'profile-edit'])) ?>"><i class="bi bi-pencil"></i> Edit profile</a><?php endif; ?>
        <p class="profile-bio"><?= $profile['bio'] !== '' ? nl2br(e($profile['bio'])) : 'Still finding the words.' ?></p>
        <div class="profile-meta"><span><i class="bi bi-calendar3"></i> Here since <?= e(date('F Y', strtotime($profile['created_at']))) ?></span><span><i class="bi bi-chat-heart"></i> <?= count($posts) ?> <?= count($posts) === 1 ? 'post' : 'posts' ?></span></div>
    </header>
    <div class="section-heading profile-post-heading"><div><span class="eyebrow">From <?= e(explode(' ', $profile['full_name'])[0]) ?></span><h2>Posts</h2></div></div>
    <div class="profile-posts">
        <?php foreach ($posts as $post): ?><article class="profile-post"><div class="post-date"><i class="bi bi-dot"></i><?= e(date('M j, Y · g:i a', strtotime($post['created_at']))) ?><?php if ($isOwner || ($viewer && ($viewer['role'] ?? 'user') === 'admin')): ?><span class="profile-post-tools"><?php if ($isOwner): ?><a href="<?= e(url(['page' => 'post-edit', 'id' => $post['id']])) ?>">Edit</a><?php endif; ?><form action="<?= e(url(['page' => 'post-delete'])) ?>" method="post" onsubmit="return confirm('Delete this post?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $post['id'] ?>"><button class="text-button danger-text" type="submit"><?= $isOwner ? 'Delete' : 'Remove' ?></button></form></span><?php endif; ?></div><p><?= nl2br(e($post['content'])) ?></p><?php if (!empty($post['image'])): ?><button class="post-image-trigger" type="button" data-image-viewer-trigger aria-label="View image shared by <?= e($profile['full_name']) ?>"><img class="post-image" src="public/uploads/<?= e($post['image']) ?>" alt="Image shared by <?= e($profile['full_name']) ?>" loading="lazy"></button><?php endif; ?><div class="profile-post-stats"><span><i class="bi bi-heart"></i> <?= (int) $post['like_count'] ?></span><span><i class="bi bi-chat"></i> <?= (int) $post['comment_count'] ?></span></div></article>
        <?php endforeach; ?>
        <?php if (!$posts): ?><div class="empty-state compact-empty"><span class="empty-icon"><i class="bi bi-feather"></i></span><h2>No posts yet.</h2><p>When a thought is ready to be shared, it’ll show up here.</p></div><?php endif; ?>
    </div>
</section>