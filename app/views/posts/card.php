<article class="post" id="post-<?= (int) $post['id'] ?>">
    <div class="post-head">
        <a class="post-author" href="<?= e(url(['page' => 'profile', 'username' => $post['username']])) ?>">
            <?php if (!empty($post['profile_image'])): ?><img class="avatar" src="public/uploads/<?= e($post['profile_image']) ?>" alt="">
            <?php else: ?><span class="avatar avatar-initial"><?= e(strtoupper(substr($post['full_name'], 0, 1))) ?></span><?php endif; ?>
            <span><strong><?= e($post['full_name']) ?></strong><small>@<?= e($post['username']) ?> <span class="dot-separator">·</span> <?= e(date('M j, g:i a', strtotime($post['created_at']))) ?></small></span>
        </a>
        <?php $isPostOwner = $viewer && (int) $viewer['id'] === (int) $post['user_id']; $canModeratePosts = $viewer && ($viewer['role'] ?? 'user') === 'admin'; ?>
        <?php if ($isPostOwner || $canModeratePosts): ?>
            <div class="post-menu"><?php if ($isPostOwner): ?><a class="icon-link" href="<?= e(url(['page' => 'post-edit', 'id' => $post['id']])) ?>" aria-label="Edit post" title="Edit post"><i class="bi bi-pencil"></i></a><?php endif; ?>
                <form action="<?= e(url(['page' => 'post-delete'])) ?>" method="post" onsubmit="return confirm('Delete this post?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $post['id'] ?>"><button class="icon-link danger-link" type="submit" aria-label="Delete post" title="Delete post"><i class="bi bi-trash3"></i></button></form>
            </div>
        <?php endif; ?>
    </div>
    <p class="post-copy"><?= nl2br(e($post['content'])) ?></p>
    <?php if (!empty($post['image'])): ?><button class="post-image-trigger" type="button" data-image-viewer-trigger aria-label="View image shared by <?= e($post['full_name']) ?>"><img class="post-image" src="public/uploads/<?= e($post['image']) ?>" alt="Image shared by <?= e($post['full_name']) ?>" loading="lazy"></button><?php endif; ?>
    <div class="post-stats"><span class="like-count" data-like-count><span data-like-total><?= (int) $post['like_count'] ?></span> <span data-like-word><?= (int) $post['like_count'] === 1 ? 'heart' : 'hearts' ?></span></span><a href="#comments-<?= (int) $post['id'] ?>" data-comment-count><span data-comment-total><?= (int) $post['comment_count'] ?></span> <span data-comment-word><?= (int) $post['comment_count'] === 1 ? 'reply' : 'replies' ?></span></a></div>
    <div class="post-actions">
        <form class="like-form" action="<?= e(url(['page' => 'like'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><button class="post-action <?= !empty($post['liked']) ? 'is-liked' : '' ?>" type="submit" aria-pressed="<?= !empty($post['liked']) ? 'true' : 'false' ?>"><i class="bi <?= !empty($post['liked']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i> Heart</button></form>
        <a class="post-action" href="#comment-form-<?= (int) $post['id'] ?>"><i class="bi bi-chat"></i> Reply</a>
    </div>
    <div class="comments" id="comments-<?= (int) $post['id'] ?>">
        <?php foreach ($post['comments'] as $comment): ?>
            <div class="comment-row"><span class="comment-avatar"><?= e(strtoupper(substr($comment['full_name'], 0, 1))) ?></span><div class="comment-content"><p><a href="<?= e(url(['page' => 'profile', 'username' => $comment['username']])) ?>"><strong><?= e($comment['full_name']) ?></strong></a> <span><?= e(date('M j', strtotime($comment['created_at']))) ?></span></p><div class="comment-text"><?= nl2br(e($comment['content'])) ?></div>
                <?php if ($viewer && (int) $viewer['id'] === (int) $comment['user_id']): ?><details class="comment-edit"><summary>Edit</summary><form action="<?= e(url(['page' => 'comment-update'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $comment['id'] ?>"><input name="content" value="<?= e($comment['content']) ?>" maxlength="500" required><button class="text-button" type="submit">Save</button></form><form action="<?= e(url(['page' => 'comment-delete'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $comment['id'] ?>"><button class="text-button danger-text" type="submit">Delete</button></form></details><?php elseif ($canModeratePosts): ?><form class="comment-admin-delete" action="<?= e(url(['page' => 'comment-delete'])) ?>" method="post" onsubmit="return confirm('Remove this comment?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $comment['id'] ?>"><button class="text-button danger-text" type="submit">Remove</button></form><?php endif; ?>
            </div></div>
        <?php endforeach; ?>
        <?php if ($viewer): ?><form class="comment-form" id="comment-form-<?= (int) $post['id'] ?>" action="<?= e(url(['page' => 'comment-create'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><input name="content" maxlength="500" placeholder="Write a thoughtful reply…" aria-label="Write a reply" required><button type="submit" aria-label="Send reply"><i class="bi bi-arrow-up-right"></i></button></form><?php endif; ?>
    </div>
</article>