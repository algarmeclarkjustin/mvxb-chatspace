<?php
declare(strict_types=1);

final class FavoriteController
{
    public function __construct(private PostModel $posts, private CommentModel $comments, private UserModel $users)
    {
    }

    public function index(): void
    {
        $user = require_auth();
        $posts = $this->posts->favorites((int) $user['id']);
        foreach ($posts as &$post) {
            $post['comments'] = $this->comments->forPost((int) $post['id'], (int) $user['id']);
        }
        unset($post);
        render('feed/index', [
            'title' => 'Favorites',
            'posts' => $posts,
            'query' => '',
            'suggestions' => $this->users->search(''),
            'feedHeading' => 'Your favorites',
            'feedEyebrow' => 'Saved for later',
        ]);
    }

    public function toggle(): void
    {
        $user = require_auth();
        verify_csrf();
        $postId = (int) ($_POST['post_id'] ?? 0);
        if (!$this->posts->canView($postId, (int) $user['id'])) {
            set_flash('error', 'That post is unavailable.');
            redirect(url(['page' => 'feed']));
        }
        $favorited = $this->posts->toggleFavorite($postId, (int) $user['id']);
        set_flash('success', $favorited ? 'Post added to your favorites.' : 'Post removed from your favorites.');
        if ((string) ($_POST['return_to'] ?? '') === 'favorites') {
            redirect(url(['page' => 'favorites']) . '#post-' . $postId);
        }
        redirect(post_location($postId, (string) ($_POST['return_profile'] ?? '')));
    }
}
