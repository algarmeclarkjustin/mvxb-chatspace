<?php
declare(strict_types=1);

final class NotificationController
{
    public function __construct(private NotificationModel $notifications, private PostModel $posts)
    {
    }

    public function index(): void
    {
        $user = require_auth();
        $notifications = $this->notifications->forUser((int) $user['id']);
        $filter = (string) ($_GET['filter'] ?? 'all');
        if (!in_array($filter, ['all', 'unread'], true)) {
            $filter = 'all';
        }
        if ($filter === 'unread') {
            $notifications = array_values(array_filter($notifications, static fn (array $notification): bool => $notification['read_at'] === null));
        }
        render('notifications/index', [
            'title' => 'Notifications',
            'notifications' => $notifications,
            'notificationFilter' => $filter,
        ]);
    }

    public function stream(): void
    {
        $user = require_auth();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        set_time_limit(0);
        ignore_user_abort(true);

        echo "retry: 2000\n\n";
        flush();

        while (!connection_aborted()) {
            $count = $this->notifications->unreadCount((int) $user['id']);
            echo "event: notification\n";
            echo 'data: ' . json_encode(['unread' => $count], JSON_THROW_ON_ERROR) . "\n\n";
            flush();
            sleep(3);
        }
    }

    public function dismiss(): void
    {
        $user = require_auth();
        verify_csrf();
        $this->notifications->markOneRead((int) $user['id'], (int) ($_POST['id'] ?? 0));
        redirect(url(['page' => 'feed']));
    }

    public function open(): void
    {
        $user = require_auth();
        verify_csrf();
        $notificationId = (int) ($_POST['id'] ?? 0);
        $notification = $this->notifications->findForUser($notificationId, (int) $user['id']);
        if (!$notification) {
            set_flash('error', 'That notification is unavailable.');
            redirect(url(['page' => 'notifications']));
        }
        $this->notifications->markOneRead((int) $user['id'], $notificationId);

        if (in_array($notification['type'], ['temporary_ban', 'permanent_ban'], true)) {
            redirect(url(['page' => 'account-restricted']));
        }
        $postId = (int) ($notification['post_id'] ?? 0);
        $commentId = (int) ($notification['comment_id'] ?? 0);
        if ($postId > 0) {
            if (!$this->posts->canView($postId, (int) $user['id'])) {
                redirect(url(['page' => 'notifications']));
            }
            redirect(url(['page' => 'post', 'id' => $postId]) . ($commentId > 0 ? '#comment-' . $commentId : '#post-' . $postId));
        }
        redirect(url(['page' => 'notifications']));
    }
}
