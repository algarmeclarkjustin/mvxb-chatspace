<div class="comment-thread">
    <div class="comment-row <?= $isReply ? 'comment-reply' : '' ?>" id="comment-<?= (int) $comment['id'] ?>" data-comment-id="<?= (int) $comment['id'] ?>">
        <span class="comment-avatar"><?= e(strtoupper(substr($comment['full_name'], 0, 1))) ?></span>
        <div class="comment-content">
            <p><a href="<?= e(url(['page' => 'profile', 'username' => $comment['username']])) ?>"><strong><?= e($comment['full_name']) ?></strong></a> <span><?= e(date('M j', strtotime($comment['created_at']))) ?></span></p>
            <div class="comment-text"><?= nl2br(linked_mentions($comment['content'])) ?></div>
            <div class="comment-tools">
                <?php if ($viewer): ?>
                    <form class="comment-like-form" action="<?= e(url(['page' => 'comment-like'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="comment_id" value="<?= (int) $comment['id'] ?>"><input type="hidden" name="return_profile" value="<?= (($_GET['page'] ?? '') === 'profile') ? e((string) ($_GET['username'] ?? '')) : '' ?>"><button class="comment-like-button <?= !empty($comment['liked']) ? 'is-liked' : '' ?>" type="submit" aria-pressed="<?= !empty($comment['liked']) ? 'true' : 'false' ?>"><i class="bi <?= !empty($comment['liked']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i><span data-comment-like-count><?= (int) $comment['like_count'] ?></span></button></form>
                    <button class="text-button reply-toggle" type="button" data-reply-comment="<?= (int) $comment['id'] ?>"><i class="bi bi-reply"></i> Reply</button>
                <?php endif; ?>
                <?php if ($viewer && (int) $viewer['id'] === (int) $comment['user_id']): ?>
                    <details class="comment-edit"><summary>Edit</summary><form action="<?= e(url(['page' => 'comment-update'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $comment['id'] ?>"><input type="hidden" name="return_profile" value="<?= (($_GET['page'] ?? '') === 'profile') ? e((string) ($_GET['username'] ?? '')) : '' ?>"><input name="content" value="<?= e($comment['content']) ?>" maxlength="500" required><button class="text-button" type="submit">Save</button></form><form action="<?= e(url(['page' => 'comment-delete'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $comment['id'] ?>"><input type="hidden" name="return_profile" value="<?= (($_GET['page'] ?? '') === 'profile') ? e((string) ($_GET['username'] ?? '')) : '' ?>"><button class="text-button danger-text" type="submit">Delete</button></form></details>
                <?php elseif ($canModeratePosts): ?>
                    <form class="comment-admin-delete" action="<?= e(url(['page' => 'comment-delete'])) ?>" method="post" onsubmit="return confirm('Remove this comment?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $comment['id'] ?>"><input type="hidden" name="return_profile" value="<?= (($_GET['page'] ?? '') === 'profile') ? e((string) ($_GET['username'] ?? '')) : '' ?>"><button class="text-button danger-text" type="submit">Remove</button></form>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($viewer): ?>
            <form class="comment-form reply-form" data-parent-comment="<?= (int) $comment['id'] ?>" action="<?= e(url(['page' => 'comment-create'])) ?>" method="post" hidden><?= csrf_field() ?><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><input type="hidden" name="parent_comment_id" value="<?= (int) $comment['id'] ?>"><input type="hidden" name="return_profile" value="<?= (($_GET['page'] ?? '') === 'profile') ? e((string) ($_GET['username'] ?? '')) : '' ?>"><input name="content" maxlength="500" placeholder="Reply to @<?= e($comment['username']) ?>…" aria-label="Reply to comment" required><button type="submit" aria-label="Send reply"><i class="bi bi-arrow-up-right"></i></button></form>
        <?php endif; ?>
    </div>
    <?php if (!empty($comment['replies'])): ?>
        <div class="comment-replies">
            <?php foreach ($comment['replies'] as $reply): render_post_comment($reply, $post, $viewer, $canModeratePosts, true); endforeach; ?>
        </div>
    <?php endif; ?>
</div>
