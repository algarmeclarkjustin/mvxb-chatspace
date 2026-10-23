<article class="post" id="post-<?= (int) $post['id'] ?>">
    <div class="post-head">
        <a class="post-author" href="<?= e(url(['page' => 'profile', 'username' => $post['username']])) ?>">
            <?php if (!empty($post['profile_image'])): ?><img class="avatar" src="public/uploads/<?= e($post['profile_image']) ?>" alt="">
            <?php else: ?><span class="avatar avatar-initial"><?= e(strtoupper(substr($post['full_name'], 0, 1))) ?></span><?php endif; ?>
            <span><strong><?= e($post['full_name']) ?></strong><small>@<?= e($post['username']) ?> <span class="dot-separator">·</span> <?= e(date('M j, g:i a', strtotime($post['created_at']))) ?></small></span>
        </a>
        <?php $isPostOwner = $viewer && (int) $viewer['id'] === (int) $post['user_id']; $canModeratePosts = $viewer && ($viewer['role'] ?? 'user') === 'admin'; ?>
        <div class="post-head-end">
            <?php if ($isPostOwner): ?><span class="visibility-label"><i class="bi <?= $post['visibility'] === 'private' ? 'bi-lock' : ($post['visibility'] === 'followers' ? 'bi-people' : 'bi-globe2') ?>"></i> <?= e(ucfirst($post['visibility'] === 'followers' ? 'friends' : $post['visibility'])) ?></span><?php endif; ?>
            <?php if ($isPostOwner || $canModeratePosts): ?>
                <div class="post-menu"><?php if ($isPostOwner): ?><a class="icon-link" href="<?= e(url(['page' => 'post-edit', 'id' => $post['id']])) ?>" aria-label="Edit post" title="Edit post"><i class="bi bi-pencil"></i></a><?php endif; ?>
                    <form action="<?= e(url(['page' => 'post-delete'])) ?>" method="post" onsubmit="return confirm('Delete this post?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $post['id'] ?>"><button class="icon-link danger-link" type="submit" aria-label="Delete post" title="Delete post"><i class="bi bi-trash3"></i></button></form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($post['content'] !== ''): ?><p class="post-copy"><?= nl2br(linked_mentions($post['content'])) ?></p><?php endif; ?>
    <?php if (!empty($post['image'])): ?><button class="post-image-trigger" type="button" data-image-viewer-trigger aria-label="View image shared by <?= e($post['full_name']) ?>"><img class="post-image" src="public/uploads/<?= e($post['image']) ?>" alt="Image shared by <?= e($post['full_name']) ?>" loading="lazy"></button><?php endif; ?>
    <div class="post-stats"><span class="like-count" data-like-count><span data-like-total><?= (int) $post['like_count'] ?></span> <span data-like-word><?= (int) $post['like_count'] === 1 ? 'heart' : 'hearts' ?></span></span><a href="#comments-<?= (int) $post['id'] ?>"><span data-comment-total><?= (int) $post['comment_count'] ?></span> <span data-comment-word><?= (int) $post['comment_count'] === 1 ? 'reply' : 'replies' ?></span></a></div>
    <div class="post-actions">
        <form class="like-form" action="<?= e(url(['page' => 'like'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><button class="post-action <?= !empty($post['liked']) ? 'is-liked' : '' ?>" type="submit" aria-pressed="<?= !empty($post['liked']) ? 'true' : 'false' ?>"><i class="bi <?= !empty($post['liked']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i> Heart</button></form>
        <a class="post-action" href="#comment-form-<?= (int) $post['id'] ?>"><i class="bi bi-chat"></i> Comment</a>
        <button class="post-action share-button" type="button" data-share-post="<?= (int) $post['id'] ?>" aria-label="Share post"><i class="bi bi-share"></i><span>Share</span></button>
        <form class="favorite-form" action="<?= e(url(['page' => 'favorite-toggle'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><input type="hidden" name="return_to" value="<?= (($_GET['page'] ?? '') === 'favorites') ? 'favorites' : 'feed' ?>"><button class="post-action <?= !empty($post['favorited']) ? 'is-favorited' : '' ?>" type="submit" aria-pressed="<?= !empty($post['favorited']) ? 'true' : 'false' ?>" title="<?= !empty($post['favorited']) ? 'Remove from favorites' : 'Add to favorites' ?>"><i class="bi <?= !empty($post['favorited']) ? 'bi-bookmark-heart-fill' : 'bi-bookmark-heart' ?>"></i><span>Favorite</span></button></form>
    </div>
    <div class="comments" id="comments-<?= (int) $post['id'] ?>">
        <?php foreach ($post['comments'] as $comment): ?>
            <?php $isReply = false; require __DIR__ . '/comment.php'; ?>
            <?php foreach ($comment['replies'] as $reply): $comment = $reply; $isReply = true; require __DIR__ . '/comment.php'; endforeach; ?>
            <?php if ($viewer): ?><form class="comment-form reply-form" id="reply-form-<?= (int) $post['id'] ?>-<?= (int) $comment['id'] ?>" action="<?= e(url(['page' => 'comment-create'])) ?>" method="post" hidden><?= csrf_field() ?><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><input type="hidden" name="parent_comment_id" value="<?= (int) $comment['id'] ?>"><input name="content" maxlength="500" placeholder="Reply to @<?= e($comment['username']) ?>… Use @username to mention someone" aria-label="Reply to comment" required><button type="submit" aria-label="Send reply"><i class="bi bi-arrow-up-right"></i></button></form><?php endif; ?>
        <?php endforeach; ?>
        <?php if ($viewer): ?><form class="comment-form" id="comment-form-<?= (int) $post['id'] ?>" action="<?= e(url(['page' => 'comment-create'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><input type="hidden" name="parent_comment_id" value="0"><input name="content" maxlength="500" placeholder="Write a thoughtful comment… Use @username to mention someone" aria-label="Write a comment" required><button type="submit" aria-label="Send comment"><i class="bi bi-arrow-up-right"></i></button></form><?php endif; ?>
    </div>
</article>
