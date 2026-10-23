<?php
declare(strict_types=1);

final class AdminController
{
    public function __construct(private UserModel $users, private PostModel $posts, private NotificationModel $notifications, private SiteSettingsModel $siteSettings)
    {
    }

    public function index(): void
    {
        require_admin();
        render('admin/index', [
            'title' => 'Admin dashboard',
            'userCount' => $this->users->countAll(),
            'postCount' => $this->posts->countAll(),
            'users' => $this->users->adminList(),
            'posts' => $this->posts->adminRecent(),
            'siteSettings' => $this->siteSettings->all(),
        ]);
    }

    public function updateBranding(): void
    {
        require_admin();
        verify_csrf();
        $siteName = trim((string) ($_POST['site_name'] ?? ''));
        if ($siteName === '' || strlen($siteName) > 80) {
            set_flash('error', 'Enter a site name using 1 to 80 characters.');
            redirect(url(['page' => 'admin']));
        }

        try {
            $logo = upload_image('site_logo', 'branding');
            $this->siteSettings->saveBranding($siteName, $logo);
            set_flash('success', 'Site name and logo settings have been updated.');
        } catch (RuntimeException $exception) {
            set_flash('error', $exception->getMessage());
        }
        redirect(url(['page' => 'admin']));
    }

    public function deletePost(): void
    {
        require_admin();
        verify_csrf();
        $post = $this->posts->find((int) ($_POST['id'] ?? 0));
        if ($post) {
            $this->posts->delete((int) $post['id']);
            set_flash('success', 'The post has been removed.');
        }
        redirect(url(['page' => 'admin']));
    }

    public function moderateUser(): void
    {
        $admin = require_admin();
        verify_csrf();
        $userId = (int) ($_POST['user_id'] ?? 0);
        $target = $this->users->findById($userId);
        $action = (string) ($_POST['action'] ?? '');
        $reason = trim((string) ($_POST['reason'] ?? ''));

        if (!$target || $userId === (int) $admin['id'] || $target['role'] === 'admin') {
            set_flash('error', 'You cannot moderate this account.');
            redirect(url(['page' => 'admin']));
        }
        if ($action === 'unban') {
            $this->users->clearBan($userId);
            $this->notifications->notifyModeration($userId, (int) $admin['id'], 'ban_lifted', 'An administrator lifted the restriction on your account.');
            set_flash('success', '@' . $target['username'] . ' can use their account again.');
            redirect(url(['page' => 'admin']));
        }
        if ($reason === '' || strlen($reason) > 350) {
            set_flash('error', 'Enter a moderation reason (up to 350 characters).');
            redirect(url(['page' => 'admin']));
        }

        if ($action === 'warning') {
            $postId = (int) ($_POST['post_id'] ?? 0);
            if ($postId > 0) {
                $post = $this->posts->find($postId);
                if (!$post || (int) $post['user_id'] !== $userId) {
                    set_flash('error', 'That post does not belong to this user.');
                    redirect(url(['page' => 'admin']));
                }
            }
            $message = $postId > 0
                ? 'A moderator flagged your post. Please review the community rules. Reason: ' . $reason
                : 'A moderator sent you a community warning. Please review the community rules. Reason: ' . $reason;
            $this->notifications->notifyModeration($userId, (int) $admin['id'], 'moderation_warning', $message, $postId ?: null);
            set_flash('success', 'A warning was sent to @' . $target['username'] . '.');
            redirect(url(['page' => 'admin']));
        }

        if (!in_array($action, ['temporary', 'permanent'], true)) {
            set_flash('error', 'Choose a valid moderation action.');
            redirect(url(['page' => 'admin']));
        }

        $permanent = $action === 'permanent';
        $bannedUntil = null;
        if (!$permanent) {
            $duration = filter_var($_POST['duration'] ?? null, FILTER_VALIDATE_INT);
            $unit = (string) ($_POST['duration_unit'] ?? '');
            $intervals = [
                'seconds' => ['PT', 'S'],
                'minutes' => ['PT', 'M'],
                'hours' => ['PT', 'H'],
                'days' => ['P', 'D'],
                'weeks' => ['P', 'W'],
                'months' => ['P', 'M'],
                'years' => ['P', 'Y'],
            ];
            if ($duration === false || $duration < 1 || $duration > 100 || !isset($intervals[$unit])) {
                set_flash('error', 'Choose a ban duration from 1 to 100 seconds, minutes, hours, days, weeks, months, or years.');
                redirect(url(['page' => 'admin']));
            }
            [$period, $interval] = $intervals[$unit];
            $expiry = (new DateTimeImmutable())->add(new DateInterval($period . $duration . $interval));
            $bannedUntil = $expiry->format('Y-m-d H:i:s');
        }

        $this->users->setBan($userId, $permanent, $bannedUntil, $reason);
        $type = $permanent ? 'permanent_ban' : 'temporary_ban';
        $message = $permanent
            ? 'An administrator permanently suspended your account. Reason: ' . $reason
            : 'An administrator suspended your account until ' . date('M j, Y g:i a', strtotime($bannedUntil)) . '. Reason: ' . $reason;
        $this->notifications->notifyModeration($userId, (int) $admin['id'], $type, $message);
        set_flash('success', '@' . $target['username'] . ($permanent ? ' was permanently banned.' : ' was temporarily banned.'));
        redirect(url(['page' => 'admin']));
    }
}