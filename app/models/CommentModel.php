<?php
declare(strict_types=1);

final class CommentModel
{
    public function __construct(private mysqli $db)
    {
    }

    public function forPost(int $postId, int $viewerId): array
    {
        $statement = $this->db->prepare('SELECT c.*, u.username, u.full_name, u.profile_image,
            (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id) AS like_count,
            EXISTS(SELECT 1 FROM comment_likes cl WHERE cl.comment_id = c.id AND cl.user_id = ?) AS liked
            FROM comments c JOIN users u ON u.id = c.user_id WHERE c.post_id = ?
            ORDER BY COALESCE(c.parent_comment_id, c.id), c.parent_comment_id IS NOT NULL, c.created_at ASC');
        $statement->bind_param('ii', $viewerId, $postId);
        $statement->execute();
        $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        $comments = [];
        foreach ($rows as $row) {
            $row['replies'] = [];
            $comments[(int) $row['id']] = $row;
        }
        $tree = [];
        foreach (array_keys($comments) as $id) {
            $parentId = (int) ($comments[$id]['parent_comment_id'] ?? 0);
            if ($parentId > 0 && isset($comments[$parentId])) {
                $comments[$parentId]['replies'][] = &$comments[$id];
            } else {
                $tree[] = &$comments[$id];
            }
        }
        return $tree;
    }

    public function create(int $postId, int $userId, string $content, ?int $parentCommentId = null): int
    {
        $statement = $this->db->prepare('INSERT INTO comments (post_id, user_id, parent_comment_id, content) VALUES (?, ?, ?, ?)');
        $statement->bind_param('iiis', $postId, $userId, $parentCommentId, $content);
        $statement->execute();
        return (int) $statement->insert_id;
    }

    public function findWithAuthor(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT c.*, u.username, u.full_name FROM comments c JOIN users u ON u.id = c.user_id WHERE c.id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function commentForPost(int $commentId, int $postId): ?array
    {
        $statement = $this->db->prepare('SELECT id, post_id, user_id FROM comments WHERE id = ? AND post_id = ?');
        $statement->bind_param('ii', $commentId, $postId);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function update(int $id, string $content): void
    {
        $statement = $this->db->prepare('UPDATE comments SET content = ? WHERE id = ?');
        $statement->bind_param('si', $content, $id);
        $statement->execute();
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM comments WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM comments WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function toggleLike(int $commentId, int $userId): bool
    {
        $statement = $this->db->prepare('DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?');
        $statement->bind_param('ii', $commentId, $userId);
        $statement->execute();
        if ($statement->affected_rows > 0) {
            return false;
        }
        $statement = $this->db->prepare('INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)');
        $statement->bind_param('ii', $commentId, $userId);
        $statement->execute();
        return true;
    }

    public function likeState(int $commentId, int $userId): array
    {
        $statement = $this->db->prepare('SELECT COUNT(*) AS total, COALESCE(SUM(user_id = ?), 0) AS liked FROM comment_likes WHERE comment_id = ?');
        $statement->bind_param('ii', $userId, $commentId);
        $statement->execute();
        $state = $statement->get_result()->fetch_assoc();
        return ['count' => (int) $state['total'], 'liked' => (bool) $state['liked']];
    }
}