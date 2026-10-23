<?php
declare(strict_types=1);

final class PostController
{
    public function __construct(private PostModel $posts, private NotificationModel $notifications)
    {
    }

    public function create(): void
    {
        $user = require_auth();
        verify_csrf();
        $content = trim((string) ($_POST['content'] ?? ''));
        $visibility = (string) ($_POST['visibility'] ?? 'public');
        if (!in_array($visibility, ['public', 'followers', 'private'], true)) {
            $visibility = 'public';
        }
        if (strlen($content) > 2000) {
            set_flash('error', 'A post must be 2,000 characters or fewer.');
            redirect(url(['page' => 'feed']));
        }
        try {
            $image = upload_image('image', 'posts');
            if ($content === '' && $image === null) {
                set_flash('error', 'Add some text or choose a photo before sharing.');
                redirect(url(['page' => 'feed']));
            }
            $postId = $this->posts->create((int) $user['id'], $content, $image, $visibility);
            $this->notifications->notifyMentions($content, (int) $user['id'], $postId);
            set_flash('success', 'Your post is live.');
        } catch (RuntimeException $exception) {
            set_flash('error', $exception->getMessage());
        }
        redirect(url(['page' => 'feed']));
    }

    public function edit(): void
    {
        $user = require_auth();
        $post = $this->ownedPost((int) ($_GET['id'] ?? 0), (int) $user['id']);
        if (!$post) {
            redirect(url(['page' => 'feed']));
        }
        render('posts/edit', ['title' => 'Edit post', 'post' => $post]);
    }

    public function update(): void
    {
        $user = require_auth();
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $post = $this->ownedPost($id, (int) $user['id']);
        if (!$post) {
            redirect(url(['page' => 'feed']));
        }
        $content = trim((string) ($_POST['content'] ?? ''));
        $visibility = (string) ($_POST['visibility'] ?? ($post['visibility'] ?? 'public'));
        if (!in_array($visibility, ['public', 'followers', 'private'], true)) {
            $visibility = 'public';
        }
        if (strlen($content) > 2000) {
            set_flash('error', 'A post must be 2,000 characters or fewer.');
            redirect(url(['page' => 'post-edit', 'id' => $id]));
        }
        try {
            $image = upload_image('image', 'posts');
            if ($content === '' && $image === null && empty($post['image'])) {
                set_flash('error', 'Add some text or choose a photo before saving.');
                redirect(url(['page' => 'post-edit', 'id' => $id]));
            }
            $this->posts->update($id, $content, $image, $visibility);
            $this->notifications->notifyMentions($content, (int) $user['id'], $id);
            set_flash('success', 'Your post has been updated.');
        } catch (RuntimeException $exception) {
            set_flash('error', $exception->getMessage());
            redirect(url(['page' => 'post-edit', 'id' => $id]));
        }
        redirect(url(['page' => 'feed']));
    }

    public function delete(): void
    {
        $user = require_auth();
        verify_csrf();
        $post = $this->posts->find((int) ($_POST['id'] ?? 0));
        $isAdmin = ($user['role'] ?? 'user') === 'admin';
        if ($post && ($isAdmin || (int) $post['user_id'] === (int) $user['id'])) {
            $this->posts->delete((int) $post['id']);
            set_flash('success', 'The post has been deleted.');
        } else {
            set_flash('error', 'You can only delete your own posts.');
        }
        redirect(url(['page' => 'feed']));
    }

    public function togglePin(): void
    {
        $user = require_auth();
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $post = $this->posts->find($id);
        if (!$post || (int) $post['user_id'] !== (int) $user['id']) {
            set_flash('error', 'You can only pin your own posts.');
            redirect(url(['page' => 'profile', 'username' => $user['username']]));
        }
        $wasPinned = (bool) $post['is_pinned'];
        $this->posts->togglePin($id, (int) $user['id']);
        set_flash('success', $wasPinned ? 'Post unpinned from your profile.' : 'Post pinned to your profile.');
        redirect(url(['page' => 'profile', 'username' => $user['username']]));
    }

    private function ownedPost(int $id, int $userId): ?array
    {
        $post = $this->posts->find($id);
        if (!$post || (int) $post['user_id'] !== $userId) {
            set_flash('error', 'That post is unavailable.');
            return null;
        }
        return $post;
    }
}