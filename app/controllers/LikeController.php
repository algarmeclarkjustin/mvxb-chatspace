<?php
declare(strict_types=1);

final class LikeController
{
    public function __construct(private LikeModel $likes, private PostModel $posts, private NotificationModel $notifications)
    {
    }

    public function toggle(): void
    {
        $user = require_auth();
        verify_csrf();
        $postId = (int) ($_POST['post_id'] ?? 0);
        $post = $this->posts->find($postId);
        if ($post && $this->posts->canView($postId, (int) $user['id'])) {
            $liked = $this->likes->toggle($postId, (int) $user['id']);
            if ($liked) {
                $this->notifications->notifyPostLike((int) $post['user_id'], (int) $user['id'], $postId);
            }
        } else {
            $post = null;
        }

        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            if (!$post) {
                http_response_code(404);
                echo json_encode(['error' => 'Post not found.']);
                return;
            }
            echo json_encode($this->likes->stateForPost($postId, (int) $user['id']));
            return;
        }

        redirect(post_location($postId, (string) ($_POST['return_profile'] ?? '')));
    }
}