<?php
declare(strict_types=1);

final class SearchController
{
    public function __construct(private UserModel $users, private PostModel $posts, private CommentModel $comments)
    {
    }

    public function users(): void
    {
        require_auth();
        $query = trim((string) ($_GET['q'] ?? ''));
        render('search/users', ['title' => 'Find people', 'query' => $query, 'users' => $query === '' ? [] : $this->users->search($query)]);
    }

    public function posts(): void
    {
        $user = require_auth();
        $query = trim((string) ($_GET['q'] ?? ''));
        $posts = $query === '' ? [] : $this->posts->search((int) $user['id'], $query);
        foreach ($posts as &$post) {
            $post['comments'] = $this->comments->forPost((int) $post['id'], (int) $user['id']);
        }
        unset($post);
        render('feed/index', [
            'title' => 'Search posts',
            'posts' => $posts,
            'query' => $query,
            'suggestions' => $this->users->search(''),
            'feedHeading' => 'Explore posts',
            'feedEyebrow' => 'Search by topic or keyword',
        ]);
    }
}