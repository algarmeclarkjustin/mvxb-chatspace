<section class="admin-page">
    <div class="section-heading">
        <div><span class="eyebrow"><?= e($siteSettings['site_name']) ?></span>
            <h1>Admin dashboard</h1>
        </div><span class="admin-badge"><i class="bi bi-shield-check"></i> Administrator</span>
    </div>
    <div class="admin-stats">
        <article class="admin-stat"><span>Registered members</span><strong><?= (int) $userCount ?></strong><i class="bi bi-people"></i></article>
        <article class="admin-stat"><span>Community posts</span><strong><?= (int) $postCount ?></strong><i class="bi bi-chat-square-text"></i></article>
    </div>
    <section class="admin-section branding-section">
        <div class="admin-section-heading">
            <div><span class="eyebrow">Site identity</span><h2>Logo and name</h2></div>
        </div>
        <form class="branding-form" action="<?= e(url(['page' => 'admin-branding'])) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <?php if (!empty($siteSettings['site_logo'])): ?><img class="branding-preview" src="public/uploads/<?= e($siteSettings['site_logo']) ?>" alt="Current site logo"><?php else: ?><span class="branding-preview branding-placeholder"><i class="bi bi-chat-heart-fill"></i></span><?php endif; ?>
            <label>Website name<input type="text" name="site_name" maxlength="80" value="<?= e($siteSettings['site_name']) ?>" required></label>
            <label>Upload logo<input type="file" name="site_logo" accept="image/jpeg,image/png,image/webp,image/gif"></label>
            <button class="button button-primary" type="submit">Save branding</button>
        </form>
    </section>
    <section class="admin-section">
        <div class="admin-section-heading">
            <div><span class="eyebrow">Community</span>
                <h2>Members</h2>
            </div><span><?= count($users) ?> shown</span>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Account status</th>
                        <th>Moderation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $member): ?><tr>
                            <td><?= e($member['full_name']) ?></td>
                            <td>@<?= e($member['username']) ?></td>
                            <td><?= e($member['email']) ?></td>
                            <td><span class="role-pill <?= $member['role'] === 'admin' ? 'role-admin' : '' ?>"><?= e(ucfirst($member['role'])) ?></span></td>
                            <?php $isBanned = (bool) $member['is_permanently_banned'] || (!empty($member['banned_until']) && strtotime($member['banned_until']) > time()); ?>
                            <td><span class="role-pill <?= $isBanned ? 'role-banned' : '' ?>"><?= $member['is_permanently_banned'] ? 'Permanent ban' : ($isBanned ? 'Until ' . date('M j, Y', strtotime($member['banned_until'])) : 'Active') ?></span><?php if ($isBanned && !empty($member['ban_reason'])): ?><small class="admin-ban-reason"><?= e($member['ban_reason']) ?></small><?php endif; ?></td>
                            <td class="admin-moderation-cell">
                                <?php if ($member['role'] !== 'admin' && (int) $member['id'] !== (int) $viewer['id']): ?>
                                    <details class="admin-moderation"><summary>Moderate</summary>
                                        <form action="<?= e(url(['page' => 'admin-user-moderate'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int) $member['id'] ?>">
                                            <label>Action<select name="action"><option value="warning">Send warning</option><option value="temporary">Temporary ban</option><option value="permanent">Permanent ban</option></select></label>
                                            <div class="admin-ban-duration"><label>Length<input type="number" name="duration" min="1" max="100" value="7"></label><label>Unit<select name="duration_unit"><option value="seconds">Seconds</option><option value="minutes">Minutes</option><option value="hours">Hours</option><option value="days">Days</option><option value="weeks">Weeks</option><option value="months">Months</option><option value="years">Years</option></select></label></div>
                                            <label>Reason<textarea name="reason" maxlength="350" required placeholder="Explain the moderation action"></textarea></label>
                                            <button class="admin-delete" type="submit">Apply action</button>
                                        </form>
                                    </details>
                                    <?php if ($isBanned): ?><form action="<?= e(url(['page' => 'admin-user-moderate'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int) $member['id'] ?>"><input type="hidden" name="action" value="unban"><button class="text-button" type="submit">Lift ban</button></form><?php endif; ?>
                                <?php else: ?><span class="admin-empty">—</span><?php endif; ?>
                            </td>
                        </tr><?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <section class="admin-section">
        <div class="admin-section-heading">
            <div><span class="eyebrow">Moderation</span>
                <h2>All posts</h2>
            </div><span><?= count($posts) ?> total</span>
        </div>
        <div class="admin-post-list">
            <?php foreach ($posts as $post): ?><article class="admin-post">
                    <div class="admin-post-copy"><strong><?= e($post['full_name']) ?> <span>@<?= e($post['username']) ?></span></strong>
                        <?php if (!empty($post['shared_post_id'])): ?><p><i class="bi bi-repeat"></i> Shared from @<?= e($post['shared_username']) ?>: <?= nl2br(e($post['shared_content'])) ?></p><?php else: ?><p><?= nl2br(e($post['content'])) ?></p><?php endif; ?><small><?= e(date('M j, Y · g:i a', strtotime($post['created_at']))) ?></small>
                    </div>
                    <div class="admin-post-actions">
                        <details class="admin-moderation"><summary>Warn author</summary>
                            <form action="<?= e(url(['page' => 'admin-user-moderate'])) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="user_id" value="<?= (int) $post['user_id'] ?>"><input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>"><input type="hidden" name="action" value="warning"><label>Reason<textarea name="reason" maxlength="350" required placeholder="What should the author correct?"></textarea></label><button class="admin-delete" type="submit">Send warning</button></form>
                        </details>
                        <form action="<?= e(url(['page' => 'admin-post-delete'])) ?>" method="post" onsubmit="return confirm('Remove this post?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $post['id'] ?>"><button class="admin-delete" type="submit" aria-label="Remove post"><i class="bi bi-trash3"></i> Remove</button></form>
                    </div>
                </article><?php endforeach; ?>
            <?php if (!$posts): ?><p class="admin-empty">No community posts yet.</p><?php endif; ?>
        </div>
    </section>
</section>