<?php
declare(strict_types=1);

final class PostModel
{
    public function __construct(private mysqli $db)
    {
    }

    public function feed(int $viewerId, ?string $query = null): array
    {
        $sql = 'SELECT p.*, u.username, u.full_name, u.profile_image,
            original.content AS shared_content, original.image AS shared_image,
            original.id AS shared_source_id, original_user.username AS shared_username,
            original_user.full_name AS shared_full_name,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
            EXISTS(SELECT 1 FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS liked,
            EXISTS(SELECT 1 FROM favorites f WHERE f.post_id = p.id AND f.user_id = ?) AS favorited
            FROM posts p JOIN users u ON u.id = p.user_id
            LEFT JOIN posts original ON original.id = p.shared_post_id
            LEFT JOIN users original_user ON original_user.id = original.user_id
            WHERE (p.visibility = \'public\' OR p.user_id = ? OR
                (p.visibility = \'followers\' AND EXISTS (
                    SELECT 1 FROM followers f WHERE f.follower_id = ? AND f.followed_id = p.user_id
                )))
            AND (p.shared_post_id IS NULL OR (original.id IS NOT NULL AND
                (original.visibility = \'public\' OR original.user_id = ? OR
                    (original.visibility = \'followers\' AND EXISTS (
                        SELECT 1 FROM followers sf WHERE sf.follower_id = ? AND sf.followed_id = original.user_id
                    )))))';
        if ($query !== null && $query !== '') {
            $sql .= ' AND (p.content LIKE ? ESCAPE \'!\' OR original.content LIKE ? ESCAPE \'!\')';
        }
        $sql .= ' ORDER BY p.created_at DESC LIMIT 60';
        $statement = $this->db->prepare($sql);
        if ($query !== null && $query !== '') {
            $escapedQuery = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query);
            $wildcardQuery = '%' . $escapedQuery . '%';
            $statement->bind_param('iiiiiiss', $viewerId, $viewerId, $viewerId, $viewerId, $viewerId, $viewerId, $wildcardQuery, $wildcardQuery);
        } else {
            $statement->bind_param('iiiiii', $viewerId, $viewerId, $viewerId, $viewerId, $viewerId, $viewerId);
        }
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM posts WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function visiblePost(int $postId, int $viewerId): ?array
    {
        $statement = $this->db->prepare('SELECT p.*, u.username, u.full_name, u.profile_image,
            original.content AS shared_content, original.image AS shared_image,
            original.id AS shared_source_id, original_user.username AS shared_username,
            original_user.full_name AS shared_full_name,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
            EXISTS(SELECT 1 FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS liked,
            EXISTS(SELECT 1 FROM favorites f WHERE f.post_id = p.id AND f.user_id = ?) AS favorited
            FROM posts p JOIN users u ON u.id = p.user_id
            LEFT JOIN posts original ON original.id = p.shared_post_id
            LEFT JOIN users original_user ON original_user.id = original.user_id
            WHERE p.id = ? AND (p.visibility = \'public\' OR p.user_id = ? OR
                (p.visibility = \'followers\' AND EXISTS (
                    SELECT 1 FROM followers f WHERE f.follower_id = ? AND f.followed_id = p.user_id
                )))
            AND (p.shared_post_id IS NULL OR (original.id IS NOT NULL AND
                (original.visibility = \'public\' OR original.user_id = ? OR
                    (original.visibility = \'followers\' AND EXISTS (
                        SELECT 1 FROM followers sf WHERE sf.follower_id = ? AND sf.followed_id = original.user_id
                    )))))');
        $statement->bind_param('iiiiiii', $viewerId, $viewerId, $postId, $viewerId, $viewerId, $viewerId, $viewerId);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function forUser(int $userId, int $viewerId): array
    {
        $statement = $this->db->prepare('SELECT p.*, u.username, u.full_name, u.profile_image,
            original.content AS shared_content, original.image AS shared_image,
            original.id AS shared_source_id,
            original_user.username AS shared_username, original_user.full_name AS shared_full_name,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
            EXISTS(SELECT 1 FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS liked,
            EXISTS(SELECT 1 FROM favorites f WHERE f.post_id = p.id AND f.user_id = ?) AS favorited
            FROM posts p JOIN users u ON u.id = p.user_id
            LEFT JOIN posts original ON original.id = p.shared_post_id
            LEFT JOIN users original_user ON original_user.id = original.user_id
            WHERE p.user_id = ? AND (p.visibility = \'public\' OR p.user_id = ? OR
                (p.visibility = \'followers\' AND EXISTS (
                    SELECT 1 FROM followers f WHERE f.follower_id = ? AND f.followed_id = p.user_id
                )))
            AND (p.shared_post_id IS NULL OR (original.id IS NOT NULL AND
                (original.visibility = \'public\' OR original.user_id = ? OR
                    (original.visibility = \'followers\' AND EXISTS (
                        SELECT 1 FROM followers sf WHERE sf.follower_id = ? AND sf.followed_id = original.user_id
                    ))))) ORDER BY p.is_pinned DESC, p.created_at DESC');
        $statement->bind_param('iiiiiii', $viewerId, $viewerId, $userId, $viewerId, $viewerId, $viewerId, $viewerId);
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create(int $userId, string $content, ?string $image, string $visibility): int
    {
        $statement = $this->db->prepare('INSERT INTO posts (user_id, content, image, visibility) VALUES (?, ?, ?, ?)');
        $statement->bind_param('isss', $userId, $content, $image, $visibility);
        $statement->execute();
        return (int) $statement->insert_id;
    }

    public function createShare(int $userId, int $postId): array
    {
        $post = $this->find($postId);
        if (!$post) {
            throw new RuntimeException('That post is no longer available.');
        }
        $sourcePostId = (int) ($post['shared_post_id'] ?? $post['id']);
        $sourcePost = $this->find($sourcePostId);
        if (!$sourcePost) {
            throw new RuntimeException('The original post is no longer available.');
        }
        $statement = $this->db->prepare('INSERT INTO posts (user_id, content, visibility, shared_post_id) VALUES (?, \'\', ?, ?)');
        $statement->bind_param('isi', $userId, $sourcePost['visibility'], $sourcePostId);
        $statement->execute();
        return ['post_id' => (int) $statement->insert_id, 'owner_id' => (int) $sourcePost['user_id']];
    }

    public function update(int $id, string $content, ?string $image, string $visibility): void
    {
        $sql = 'UPDATE posts SET content = ?, visibility = ?';
        if ($image !== null) {
            $sql .= ', image = ?';
        }
        $sql .= ' WHERE id = ?';
        $statement = $this->db->prepare($sql);
        if ($image !== null) {
            $statement->bind_param('sssi', $content, $visibility, $image, $id);
        } else {
            $statement->bind_param('ssi', $content, $visibility, $id);
        }
        $statement->execute();
    }

    public function canView(int $postId, int $viewerId): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM posts p
            LEFT JOIN posts original ON original.id = p.shared_post_id
            WHERE p.id = ? AND (p.visibility = \'public\' OR p.user_id = ? OR
            (p.visibility = \'followers\' AND EXISTS (
                SELECT 1 FROM followers f WHERE f.follower_id = ? AND f.followed_id = p.user_id
            )))
            AND (p.shared_post_id IS NULL OR (original.id IS NOT NULL AND
                (original.visibility = \'public\' OR original.user_id = ? OR
                    (original.visibility = \'followers\' AND EXISTS (
                        SELECT 1 FROM followers sf WHERE sf.follower_id = ? AND sf.followed_id = original.user_id
                    )))))');
        $statement->bind_param('iiiii', $postId, $viewerId, $viewerId, $viewerId, $viewerId);
        $statement->execute();
        return (bool) $statement->get_result()->fetch_row();
    }

    public function toggleFavorite(int $postId, int $userId): bool
    {
        $statement = $this->db->prepare('DELETE FROM favorites WHERE post_id = ? AND user_id = ?');
        $statement->bind_param('ii', $postId, $userId);
        $statement->execute();
        if ($statement->affected_rows > 0) {
            return false;
        }
        $statement = $this->db->prepare('INSERT INTO favorites (post_id, user_id) VALUES (?, ?)');
        $statement->bind_param('ii', $postId, $userId);
        $statement->execute();
        return true;
    }

    public function favorites(int $userId): array
    {
        $statement = $this->db->prepare('SELECT p.*, u.username, u.full_name, u.profile_image,
            original.content AS shared_content, original.image AS shared_image,
            original_user.username AS shared_username, original_user.full_name AS shared_full_name,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
            EXISTS(SELECT 1 FROM likes l WHERE l.post_id = p.id AND l.user_id = ?) AS liked,
            1 AS favorited
            FROM favorites f JOIN posts p ON p.id = f.post_id JOIN users u ON u.id = p.user_id
            LEFT JOIN posts original ON original.id = p.shared_post_id
            LEFT JOIN users original_user ON original_user.id = original.user_id
            WHERE f.user_id = ? AND (p.visibility = \'public\' OR p.user_id = ? OR
                (p.visibility = \'followers\' AND EXISTS (
                    SELECT 1 FROM followers x WHERE x.follower_id = ? AND x.followed_id = p.user_id
                )))
            AND (p.shared_post_id IS NULL OR (original.id IS NOT NULL AND
                (original.visibility = \'public\' OR original.user_id = ? OR
                    (original.visibility = \'followers\' AND EXISTS (
                        SELECT 1 FROM followers sf WHERE sf.follower_id = ? AND sf.followed_id = original.user_id
                    ))))) ORDER BY f.created_at DESC');
        $statement->bind_param('iiiiii', $userId, $userId, $userId, $userId, $userId, $userId);
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function togglePin(int $postId, int $userId): bool
    {
        $post = $this->find($postId);
        if (!$post || (int) $post['user_id'] !== $userId) {
            return false;
        }
        $isPinned = (bool) $post['is_pinned'];
        $this->db->begin_transaction();
        try {
            $statement = $this->db->prepare('UPDATE posts SET is_pinned = 0 WHERE user_id = ?');
            $statement->bind_param('i', $userId);
            $statement->execute();
            if (!$isPinned) {
                $statement = $this->db->prepare('UPDATE posts SET is_pinned = 1 WHERE id = ? AND user_id = ?');
                $statement->bind_param('ii', $postId, $userId);
                $statement->execute();
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }
        return !$isPinned;
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM posts WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
    }

    public function likedBy(int $postId, int $userId): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM likes WHERE post_id = ? AND user_id = ?');
        $statement->bind_param('ii', $postId, $userId);
        $statement->execute();
        return (bool) $statement->get_result()->fetch_row();
    }

    public function search(int $viewerId, string $query): array
    {
        return $this->feed($viewerId, $query);
    }

    public function countAll(): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) AS total FROM posts');
        $statement->execute();
        return (int) $statement->get_result()->fetch_assoc()['total'];
    }

    public function adminRecent(): array
    {
        $statement = $this->db->prepare('SELECT p.id, p.content, p.created_at, p.shared_post_id,
            shared.content AS shared_content, u.id AS user_id, u.username, u.full_name,
            original_user.username AS shared_username
            FROM posts p JOIN users u ON u.id = p.user_id
            LEFT JOIN posts shared ON shared.id = p.shared_post_id
            LEFT JOIN users original_user ON original_user.id = shared.user_id
            ORDER BY p.created_at DESC');
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}