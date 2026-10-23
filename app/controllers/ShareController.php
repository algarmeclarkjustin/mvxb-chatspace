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

        $share = $this->posts->createShare((int) $user['id'], $postId);
        $this->notifications->notifyShare($share['owner_id'], (int) $user['id'], $share['post_id']);
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(201);
        echo json_encode(['url' => url(['page' => 'feed']) . '#post-' . $share['post_id']]);
    }
}
