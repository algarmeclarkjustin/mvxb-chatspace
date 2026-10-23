<?php
declare(strict_types=1);

final class FeedController
{
    public function __construct(private PostModel $posts, private CommentModel $comments, private UserModel $users)
    {
    }

    public function index(): void
    {
        $user = require_auth();
        $query = trim((string) ($_GET['q'] ?? ''));
        $posts = $this->posts->feed((int) $user['id'], $query);
        foreach ($posts as &$post) {
            $post['comments'] = $this->comments->forPost((int) $post['id'], (int) $user['id']);
        }
        unset($post);
        render('feed/index', ['title' => 'Your feed', 'posts' => $posts, 'query' => $query, 'suggestions' => $this->users->search('')]);
    }
}