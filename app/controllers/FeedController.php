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

    public function showPost(): void
    {
        $user = require_auth();
        $post = $this->posts->visiblePost((int) ($_GET['id'] ?? 0), (int) $user['id']);
        if (!$post) {
            http_response_code(404);
            render('errors/not_found', ['title' => 'Post not found']);
            return;
        }
        $post['comments'] = $this->comments->forPost((int) $post['id'], (int) $user['id']);
        render('feed/post', ['title' => 'Post by ' . $post['full_name'], 'post' => $post]);
    }
}