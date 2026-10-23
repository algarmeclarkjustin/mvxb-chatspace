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
    $siteSettings = new SiteSettingsModel($db);
    $GLOBALS['notification_model'] = $notifications;
    $GLOBALS['site_settings'] = $siteSettings->all();
    $page = (string) ($_GET['page'] ?? (current_user() ? 'feed' : 'login'));
    $activeBan = current_user() ? $users->activeBan((int) current_user()['id']) : null;

    if ($activeBan && !in_array($page, ['account-restricted', 'logout', 'notification-dismiss', 'notification-open'], true)) {
        redirect(url(['page' => 'account-restricted']));
    }

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
        case 'post':
            (new FeedController($posts, $comments, $users))->showPost();
            break;
        case 'profile':
            (new ProfileController($users, $posts, $notifications))->show();
            break;
        case 'profile-edit':
            (new ProfileController($users, $posts, $notifications))->edit();
            break;
        case 'profile-update':
            (new ProfileController($users, $posts, $notifications))->update();
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
            (new NotificationController($notifications, $posts))->index();
            break;
        case 'notification-dismiss':
            (new NotificationController($notifications, $posts))->dismiss();
            break;
        case 'notification-open':
            (new NotificationController($notifications, $posts))->open();
            break;
        case 'share-track':
            (new ShareController($posts, $notifications))->track();
            break;
        case 'follow-toggle':
            (new ProfileController($users, $posts, $notifications))->toggleFollow();
            break;
        case 'admin':
            (new AdminController($users, $posts, $notifications, $siteSettings))->index();
            break;
        case 'admin-branding':
            (new AdminController($users, $posts, $notifications, $siteSettings))->updateBranding();
            break;
        case 'admin-post-delete':
            (new AdminController($users, $posts, $notifications, $siteSettings))->deletePost();
            break;
        case 'admin-user-moderate':
            (new AdminController($users, $posts, $notifications, $siteSettings))->moderateUser();
            break;
        case 'account-restricted':
            $user = require_auth();
            if (!$activeBan) {
                redirect(url(['page' => 'feed']));
            }
            render('moderation/restricted', [
                'title' => 'Account suspended',
                'activeBan' => $activeBan,
            ]);
            break;
        default:
            http_response_code(404);
            render('errors/not_found', ['title' => 'Page not found']);
    }
} catch (mysqli_sql_exception $exception) {
    http_response_code(500);
    render('errors/database', ['title' => 'Database connection needed']);
}