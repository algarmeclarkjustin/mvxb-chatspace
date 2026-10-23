<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/config/database.php';
require __DIR__ . '/app/helpers.php';
foreach (glob(__DIR__ . '/app/models/*.php') as $modelFile) {
    require_once $modelFile;
}
foreach (glob(__DIR__ . '/app/controllers/*.php') as $controllerFile) {
    require_once $controllerFile;
}

try {
    $db = database();
    $users = new UserModel($db);
    $posts = new PostModel($db);
    $comments = new CommentModel($db);
    $likes = new LikeModel($db);
    $notifications = new NotificationModel($db);
    $GLOBALS['notification_model'] = $notifications;
    $page = (string) ($_GET['page'] ?? (current_user() ? 'feed' : 'login'));

    switch ($page) {
        case 'login':
            (new AuthController($users))->login();
            break;
        case 'register':
            (new AuthController($users))->register();
            break;
        case 'logout':
            (new AuthController($users))->logout();
            break;
        case 'feed':
            (new FeedController($posts, $comments, $users))->index();
            break;
        case 'profile':
            (new ProfileController($users, $posts))->show();
            break;
        case 'profile-edit':
            (new ProfileController($users, $posts))->edit();
            break;
        case 'profile-update':
            (new ProfileController($users, $posts))->update();
            break;
        case 'post-create':
            (new PostController($posts, $notifications))->create();
            break;
        case 'post-edit':
            (new PostController($posts, $notifications))->edit();
            break;
        case 'post-update':
            (new PostController($posts, $notifications))->update();
            break;
        case 'post-delete':
            (new PostController($posts, $notifications))->delete();
            break;
        case 'post-pin':
            (new PostController($posts, $notifications))->togglePin();
            break;
        case 'comment-create':
            (new CommentController($comments, $posts, $notifications))->create();
            break;
        case 'comment-update':
            (new CommentController($comments, $posts, $notifications))->update();
            break;
        case 'comment-delete':
            (new CommentController($comments, $posts, $notifications))->delete();
            break;
        case 'comment-like':
            (new CommentController($comments, $posts, $notifications))->toggleLike();
            break;
        case 'like':
            (new LikeController($likes, $posts, $notifications))->toggle();
            break;
        case 'search-users':
            (new SearchController($users, $posts, $comments))->users();
            break;
        case 'search-posts':
            (new SearchController($users, $posts, $comments))->posts();
            break;
        case 'favorites':
            (new FavoriteController($posts, $comments, $users))->index();
            break;
        case 'favorite-toggle':
            (new FavoriteController($posts, $comments, $users))->toggle();
            break;
        case 'notifications':
            (new NotificationController($notifications))->index();
            break;
        case 'share-track':
            (new ShareController($posts, $notifications))->track();
            break;
        case 'follow-toggle':
            (new ProfileController($users, $posts))->toggleFollow();
            break;
        case 'admin':
            (new AdminController($users, $posts))->index();
            break;
        case 'admin-post-delete':
            (new AdminController($users, $posts))->deletePost();
            break;
        default:
            http_response_code(404);
            render('errors/not_found', ['title' => 'Page not found']);
    }
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    render('errors/database', ['title' => 'Database connection needed']);
}