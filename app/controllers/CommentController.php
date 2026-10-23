<?php
declare(strict_types=1);

final class CommentController
{
    public function __construct(private CommentModel $comments, private PostModel $posts, private NotificationModel $notifications)
    {
    }

    public function create(): void
    {
        $user = require_auth();
        verify_csrf();
        $postId = (int) ($_POST['post_id'] ?? 0);
        $parentCommentId = (int) ($_POST['parent_comment_id'] ?? 0);
        $content = trim((string) ($_POST['content'] ?? ''));
        if (!$this->posts->canView($postId, (int) $user['id'])) {
            http_response_code(404);
            if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Post not found.']);
                return;
            }
            set_flash('error', 'That post is unavailable.');
            redirect(post_location($postId, (string) ($_POST['return_profile'] ?? '')));
        }
        $parentComment = $parentCommentId > 0 ? $this->comments->commentForPost($parentCommentId, $postId) : null;
        if ($parentCommentId > 0 && !$parentComment) {
            http_response_code(422);
            if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'You can only reply to comments on this post.']);
                return;
            }
            set_flash('error', 'That comment is unavailable for a reply.');
            redirect(post_location($postId, (string) ($_POST['return_profile'] ?? '')));
        }
        if ($content === '' || strlen($content) > 500) {
            if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
                http_response_code(422);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Comments must contain 1-500 characters.']);
                return;
            }
            set_flash('error', 'Comments must contain 1-500 characters.');
        } else {
            $commentId = $this->comments->create($postId, (int) $user['id'], $content, $parentCommentId > 0 ? $parentCommentId : null);
            if ($parentComment) {
                $this->notifications->notifyCommentReply((int) $parentComment['user_id'], (int) $user['id'], $postId, $commentId);
            }
            $post = $this->posts->find($postId);
            if ($post && (!$parentComment || (int) $parentComment['user_id'] !== (int) $post['user_id'])) {
                $this->notifications->notifyPostComment((int) $post['user_id'], (int) $user['id'], $postId, $commentId);
            }
            $this->notifications->notifyMentions($content, (int) $user['id'], $postId, $commentId);
            if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
                $comment = $this->comments->findWithAuthor($commentId);
                if (!$comment) {
                    throw new RuntimeException('The new comment could not be retrieved.');
                }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'id' => (int) $comment['id'],
                    'content' => $comment['content'],
                    'full_name' => $comment['full_name'],
                    'username' => $comment['username'],
                    'created_at' => date('M j', strtotime($comment['created_at'])),
                    'profile_url' => url(['page' => 'profile', 'username' => $comment['username']]),
                    'parent_comment_id' => $parentCommentId > 0 ? $parentCommentId : null,
                ]);
                return;
            }
        }
        redirect(post_location($postId, (string) ($_POST['return_profile'] ?? '')));
    }

    public function update(): void
    {
        $user = require_auth();
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $comment = $this->ownedComment($id, (int) $user['id']);
        $postId = (int) ($comment['post_id'] ?? 0);
        $content = trim((string) ($_POST['content'] ?? ''));
        if ($comment && !$this->posts->canView($postId, (int) $user['id'])) {
            set_flash('error', 'That comment is unavailable.');
        } elseif ($comment && $content !== '' && strlen($content) <= 500) {
            $this->comments->update($id, $content);
            $this->notifications->notifyMentions($content, (int) $user['id'], $postId, $id);
        }
        redirect(post_location($postId, (string) ($_POST['return_profile'] ?? '')));
    }

    public function delete(): void
    {
        $user = require_auth();
        verify_csrf();
        $comment = $this->comments->find((int) ($_POST['id'] ?? 0));
        $postId = (int) ($comment['post_id'] ?? 0);
        $isAdmin = ($user['role'] ?? 'user') === 'admin';
        if ($comment && !$this->posts->canView($postId, (int) $user['id'])) {
            set_flash('error', 'That comment is unavailable.');
        } elseif ($comment && ($isAdmin || (int) $comment['user_id'] === (int) $user['id'])) {
            $this->comments->delete((int) $comment['id']);
        } else {
            set_flash('error', 'You can only delete your own comments.');
        }
        redirect(post_location($postId, (string) ($_POST['return_profile'] ?? '')));
    }

    public function toggleLike(): void
    {
        $user = require_auth();
        verify_csrf();
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        $comment = $this->comments->findWithAuthor($commentId);
        $postId = (int) ($comment['post_id'] ?? 0);
        if (!$comment || !$this->posts->canView($postId, (int) $user['id'])) {
            http_response_code(404);
            if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Comment not found.']);
                return;
            }
            set_flash('error', 'That comment is unavailable.');
            redirect(post_location($postId, (string) ($_POST['return_profile'] ?? '')));
        }
        $liked = $this->comments->toggleLike($commentId, (int) $user['id']);
        if ($liked) {
            $this->notifications->notifyCommentLike((int) $comment['user_id'], (int) $user['id'], $postId, $commentId);
        }
        $state = $this->comments->likeState($commentId, (int) $user['id']);
        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($state);
            return;
        }
        redirect(post_location($postId, (string) ($_POST['return_profile'] ?? '')));
    }

    private function ownedComment(int $id, int $userId): ?array
    {
        $comment = $this->comments->find($id);
        if (!$comment || (int) $comment['user_id'] !== $userId) {
            set_flash('error', 'That comment is unavailable.');
            return null;
        }
        return $comment;
    }
}