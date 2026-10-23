<?php
declare(strict_types=1);

final class LikeModel
{
    public function __construct(private mysqli $db)
    {
    }

    public function toggle(int $postId, int $userId): bool
    {
        $statement = $this->db->prepare('DELETE FROM likes WHERE post_id = ? AND user_id = ?');
        $statement->bind_param('ii', $postId, $userId);
        $statement->execute();
        if ($statement->affected_rows > 0) {
            return false;
        }
        $statement = $this->db->prepare('INSERT INTO likes (post_id, user_id) VALUES (?, ?)');
        $statement->bind_param('ii', $postId, $userId);
        $statement->execute();
        return true;
    }

    public function stateForPost(int $postId, int $userId): array
    {
        $statement = $this->db->prepare('SELECT COUNT(*) AS total, COALESCE(SUM(user_id = ?), 0) AS liked FROM likes WHERE post_id = ?');
        $statement->bind_param('ii', $userId, $postId);
        $statement->execute();
        $state = $statement->get_result()->fetch_assoc();
        return ['count' => (int) $state['total'], 'liked' => (bool) $state['liked']];
    }
}