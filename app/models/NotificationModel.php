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

    public function forUser(int $userId): array
    {
        $statement = $this->db->prepare('SELECT n.*, actor.username AS actor_username, actor.full_name AS actor_name
            FROM notifications n JOIN users actor ON actor.id = n.actor_id
            WHERE n.recipient_id = ? ORDER BY n.created_at DESC LIMIT 100');
        $statement->bind_param('i', $userId);
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function unreadCount(int $userId): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) AS total FROM notifications WHERE recipient_id = ? AND read_at IS NULL');
        $statement->bind_param('i', $userId);
        $statement->execute();
        return (int) $statement->get_result()->fetch_assoc()['total'];
    }

    public function markRead(int $userId): void
    {
        $statement = $this->db->prepare('UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE recipient_id = ? AND read_at IS NULL');
        $statement->bind_param('i', $userId);
        $statement->execute();
    }

    private function create(int $recipientId, int $actorId, string $type, int $postId, ?int $commentId = null): void
    {
        if ($recipientId === $actorId) {
            return;
        }
        $statement = $this->db->prepare('INSERT INTO notifications (recipient_id, actor_id, type, post_id, comment_id) VALUES (?, ?, ?, ?, ?)');
        $statement->bind_param('iisii', $recipientId, $actorId, $type, $postId, $commentId);
        $statement->execute();
    }
}
