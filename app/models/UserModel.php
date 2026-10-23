<?php
declare(strict_types=1);

final class UserModel
{
    public function __construct(private mysqli $db)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $statement->bind_param('s', $username);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function findByLogin(string $login): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1');
        $statement->bind_param('ss', $login, $login);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT id, username, full_name, bio, profile_image, role, created_at FROM users WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        return $statement->get_result()->fetch_assoc() ?: null;
    }

    public function create(string $username, string $email, string $passwordHash, string $fullName): int
    {
        $statement = $this->db->prepare('INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)');
        $statement->bind_param('ssss', $username, $email, $passwordHash, $fullName);
        $statement->execute();
        return (int) $this->db->insert_id;
    }

    public function updateProfile(int $id, string $fullName, string $bio, ?string $profileImage): void
    {
        $sql = 'UPDATE users SET full_name = ?, bio = ?';
        if ($profileImage !== null) {
            $sql .= ', profile_image = ?';
        }
        $sql .= ' WHERE id = ?';
        $statement = $this->db->prepare($sql);
        if ($profileImage !== null) {
            $statement->bind_param('sssi', $fullName, $bio, $profileImage, $id);
        } else {
            $statement->bind_param('ssi', $fullName, $bio, $id);
        }
        $statement->execute();
    }

    public function search(string $query): array
    {
        $statement = $this->db->prepare('SELECT id, username, full_name, bio, profile_image FROM users WHERE username LIKE ? OR full_name LIKE ? ORDER BY full_name LIMIT 30');
        $wildcardQuery = '%' . $query . '%';
        $statement->bind_param('ss', $wildcardQuery, $wildcardQuery);
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function toggleFollow(int $followerId, int $followedId): bool
    {
        if ($followerId === $followedId) {
            return false;
        }
        $statement = $this->db->prepare('DELETE FROM followers WHERE follower_id = ? AND followed_id = ?');
        $statement->bind_param('ii', $followerId, $followedId);
        $statement->execute();
        if ($statement->affected_rows > 0) {
            return false;
        }
        $statement = $this->db->prepare('INSERT INTO followers (follower_id, followed_id) VALUES (?, ?)');
        $statement->bind_param('ii', $followerId, $followedId);
        $statement->execute();
        return true;
    }

    public function isFollowing(int $followerId, int $followedId): bool
    {
        $statement = $this->db->prepare('SELECT 1 FROM followers WHERE follower_id = ? AND followed_id = ?');
        $statement->bind_param('ii', $followerId, $followedId);
        $statement->execute();
        return (bool) $statement->get_result()->fetch_row();
    }

    public function followerCount(int $userId): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) AS total FROM followers WHERE followed_id = ?');
        $statement->bind_param('i', $userId);
        $statement->execute();
        return (int) $statement->get_result()->fetch_assoc()['total'];
    }

    public function countAll(): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) AS total FROM users');
        $statement->execute();
        return (int) $statement->get_result()->fetch_assoc()['total'];
    }

    public function adminList(): array
    {
        $statement = $this->db->prepare('SELECT id, username, email, full_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 100');
        $statement->execute();
        return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}