<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function linked_mentions(string $text): string
{
    $escaped = e($text);
    return preg_replace_callback('/(?<![A-Za-z0-9_])@([A-Za-z0-9_]{1,24})/', static function (array $match): string {
        return '<a class="mention" href="' . e(url(['page' => 'profile', 'username' => $match[1]])) . '">@' . $match[1] . '</a>';
    }, $escaped) ?? $escaped;
}

function url(array $parameters = []): string
{
    return 'index.php' . ($parameters ? '?' . http_build_query($parameters) : '');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        set_flash('error', 'Please sign in to continue.');
        redirect(url(['page' => 'login']));
    }

    return $user;
}

function require_admin(): array
{
    $user = require_auth();
    if (($user['role'] ?? 'user') !== 'admin') {
        set_flash('error', 'Admin access only.');
        redirect(url(['page' => 'feed']));
    }

    return $user;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_field(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['csrf_token'] ?? '';
    if (!is_string($submitted) || !is_string($known) || $known === '' || !hash_equals($known, $submitted)) {
        http_response_code(419);
        exit('Your session expired. Refresh the page and try again.');
    }
}

function render(string $view, array $data = []): void
{
    $notificationModel = $GLOBALS['notification_model'] ?? null;
    $viewer = current_user();
    if ($notificationModel instanceof NotificationModel && $viewer) {
        $data['unreadNotificationCount'] = $notificationModel->unreadCount((int) $viewer['id']);
        $data['moderationAlert'] = $notificationModel->unreadModerationAlert((int) $viewer['id']);
    } else {
        $data['unreadNotificationCount'] = 0;
        $data['moderationAlert'] = null;
    }
    extract($data, EXTR_SKIP);
    require __DIR__ . '/views/partials/header.php';
    require __DIR__ . '/views/' . $view . '.php';
    require __DIR__ . '/views/partials/footer.php';
}

function render_post_comment(array $comment, array $post, ?array $viewer, bool $canModeratePosts, bool $isReply = false): void
{
    require __DIR__ . '/views/posts/comment.php';
}

function upload_image(string $field, string $folder): ?string
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Images must be smaller than 5 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Choose a JPG, PNG, WEBP, or GIF image.');
    }

    $directory = __DIR__ . '/../public/uploads/' . $folder;
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The upload folder could not be created.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('The image could not be uploaded.');
    }

    return $folder . '/' . $filename;
}