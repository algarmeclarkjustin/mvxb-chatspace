<?php
declare(strict_types=1);

final class NotificationModel
{
    public function __construct(private mysqli $db)
    {
    }

    public function notifyMentions(string $content, int $actorId, int $postId, ?int $commentId = null): void
    {
        preg_match_all('/(?<![A-Za-z0-9_])@([A-Za-z0-9_]{1,24})/', $content, $matches);
        foreach (array_unique($matches[1]) as $username) {
            $statement = $this->db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $statement->bind_param('s', $username);
            $statement->execute();
            $recipientId = $statement->get_result()->fetch_column();
            if ($recipientId !== false && (int) $recipientId !== $actorId) {
                $this->create((int) $recipientId, $actorId, 'mention', $postId, $commentId);
            }
        }
    }

    public function notifyPostLike(int $recipientId, int $actorId, int $postId): void
    {
        $this->create($recipientId, $actorId, 'post_like', $postId);
    }

    public function notifyCommentLike(int $recipientId, int $actorId, int $postId, int $commentId): void
    {
        $this->create($recipientId, $actorId, 'comment_like', $postId, $commentId);
    }

    public function notifyCommentReply(int $recipientId, int $actorId, int $postId, int $commentId): void
    {
        $this->create($recipientId, $actorId, 'comment_reply', $postId, $commentId);
    }

    public function notifyPostComment(int $recipientId, int $actorId, int $postId, int $commentId): void
    {
        $this->create($recipientId, $actorId, 'comment', $postId, $commentId);
    }

    public function notifyShare(int $recipientId, int $actorId, int $postId): void
    {
        $this->create($recipientId, $actorId, 'share', $postId);
    }

    public function notifyFollow(int $recipientId, int $actorId): void
    {
        $this->create($recipientId, $actorId, 'follow');
    }

    public function notifyModeration(int $recipientId, int $actorId, string $type, string $message, ?int $postId = null): void
    {
        $this->create($recipientId, $actorId, $type, $postId, null, $message);
    }

    public function forUser(int $userId): array
    {
        $statement = $this->db->prepare('SELECT n.*, COALESCE(actor.username, \'former member\') AS actor_username,
            actor.full_name AS actor_name, actor.profile_image AS actor_image,
            COALESCE(NULLIF(p.content, \'\'), shared.content) AS post_content, c.content AS comment_content
            FROM notifications n LEFT JOIN users actor ON actor.id = n.actor_id
            LEFT JOIN posts p ON p.id = n.post_id
            LEFT JOIN posts shared ON shared.id = p.shared_post_id
            LEFT JOIN comments c ON c.id = n.comment_id
            WHERE n.recipient_id = ? AND n.type <> \'\'
            ORDER BY n.created_at DESC');
        $statement->bind_param('i', $userId);
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findForUser(int $notificationId, int $userId): ?array
    {
        $statement = $this->db->prepare('SELECT id, type, post_id, comment_id FROM notifications WHERE id = ? AND recipient_id = ?');
        $statement->bind_param('ii', $notificationId, $userId);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function unreadCount(int $userId): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) AS total FROM notifications WHERE recipient_id = ? AND read_at IS NULL AND type <> \'\'');
        $statement->bind_param('i', $userId);
        $statement->execute();
        return (int) $statement->get_result()->fetch_assoc()['total'];
    }

    public function unreadModerationAlert(int $userId): ?array
    {
        $statement = $this->db->prepare('SELECT id, type, message FROM notifications WHERE recipient_id = ? AND read_at IS NULL
            AND type IN (\'moderation_warning\', \'temporary_ban\', \'permanent_ban\', \'ban_lifted\')
            ORDER BY created_at DESC LIMIT 1');
        $statement->bind_param('i', $userId);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function markOneRead(int $userId, int $notificationId): void
    {
        $statement = $this->db->prepare('UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE id = ? AND recipient_id = ? AND read_at IS NULL');
        $statement->bind_param('ii', $notificationId, $userId);
        $statement->execute();
    }

    public function markRead(int $userId): void
    {
        $statement = $this->db->prepare('UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE recipient_id = ? AND read_at IS NULL');
        $statement->bind_param('i', $userId);
        $statement->execute();
    }

    private function create(int $recipientId, int $actorId, string $type, ?int $postId = null, ?int $commentId = null, ?string $message = null): void
    {
        if ($recipientId === $actorId) {
            return;
        }
        $statement = $this->db->prepare('INSERT INTO notifications (recipient_id, actor_id, type, post_id, comment_id, message) VALUES (?, ?, ?, ?, ?, ?)');
        $statement->bind_param('iisiis', $recipientId, $actorId, $type, $postId, $commentId, $message);
        $statement->execute();
    }
}
