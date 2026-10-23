<?php
declare(strict_types=1);

final class NotificationController
{
    public function __construct(private NotificationModel $notifications)
    {
    }

    public function index(): void
    {
        $user = require_auth();
        $this->notifications->markRead((int) $user['id']);
        render('notifications/index', [
            'title' => 'Notifications',
            'notifications' => $this->notifications->forUser((int) $user['id']),
        ]);
    }
}
