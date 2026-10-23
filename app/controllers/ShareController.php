<?php
declare(strict_types=1);

final class ShareController
{
    public function __construct(private PostModel $posts, private NotificationModel $notifications)
    {
    }

    public function track(): void
    {
        $user = require_auth();
        verify_csrf();
        $postId = (int) ($_POST['post_id'] ?? 0);
        $post = $this->posts->find($postId);
        if (!$post || !$this->posts->canView($postId, (int) $user['id'])) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Post not found.']);
            return;
        }

        $this->notifications->notifyShare((int) $post['user_id'], (int) $user['id'], $postId);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['url' => url(['page' => 'feed']) . '#post-' . $postId]);
    }
}
