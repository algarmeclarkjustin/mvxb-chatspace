<?php
declare(strict_types=1);

final class ProfileController
{
    public function __construct(private UserModel $users, private PostModel $posts, private NotificationModel $notifications)
    {
    }

    public function show(): void
    {
        $viewer = current_user();
        $username = (string) ($_GET['username'] ?? '');
        $profile = $this->users->findByUsername($username);
        if (!$profile) {
            http_response_code(404);
            render('errors/not_found', ['title' => 'Profile not found']);
            return;
        }
        $viewerId = (int) ($viewer['id'] ?? 0);
        $isOwner = $viewerId > 0 && $viewerId === (int) $profile['id'];
        render('profile/show', [
            'title' => $profile['full_name'],
            'profile' => $profile,
            'posts' => $this->posts->forUser((int) $profile['id'], $viewerId),
            'isOwner' => $isOwner,
            'isFollowing' => $viewerId > 0 && $this->users->isFollowing($viewerId, (int) $profile['id']),
            'followerCount' => $this->users->followerCount((int) $profile['id']),
        ]);
    }

    public function toggleFollow(): void
    {
        $user = require_auth();
        verify_csrf();
        $profile = $this->users->findByUsername((string) ($_POST['username'] ?? ''));
        if (!$profile || (int) $profile['id'] === (int) $user['id']) {
            set_flash('error', 'That profile is unavailable.');
            redirect(url(['page' => 'feed']));
        }
        $following = $this->users->toggleFollow((int) $user['id'], (int) $profile['id']);
        if ($following) {
            $this->notifications->notifyFollow((int) $profile['id'], (int) $user['id']);
        }
        set_flash('success', $following ? 'You are now following @' . $profile['username'] . '.' : 'You unfollowed @' . $profile['username'] . '.');
        redirect(url(['page' => 'profile', 'username' => $profile['username']]));
    }

    public function edit(): void
    {
        $user = require_auth();
        render('profile/edit', ['title' => 'Edit profile', 'profile' => $user]);
    }

    public function update(): void
    {
        $user = require_auth();
        verify_csrf();
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $bio = trim((string) ($_POST['bio'] ?? ''));
        if ($fullName === '' || strlen($fullName) > 80 || strlen($bio) > 240) {
            set_flash('error', 'Your name is required, and your bio must be 240 characters or fewer.');
            redirect(url(['page' => 'profile-edit']));
        }
        try {
            $image = upload_image('profile_image', 'profiles');
            $this->users->updateProfile((int) $user['id'], $fullName, $bio, $image);
            $_SESSION['user'] = $this->users->findById((int) $user['id']);
            set_flash('success', 'Your profile has been updated.');
        } catch (RuntimeException $exception) {
            set_flash('error', $exception->getMessage());
            redirect(url(['page' => 'profile-edit']));
        }
        redirect(url(['page' => 'profile', 'username' => $user['username']]));
    }
}