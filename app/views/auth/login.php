<section class="auth-layout">
    <div class="auth-panel">
        <?php if (!empty($accountNotice)): ?><div class="account-notice" role="alert"><i class="bi bi-shield-exclamation"></i><div><strong>Account access paused</strong><p><?= e($accountNotice) ?></p></div></div><?php endif; ?>
        <div class="auth-heading"><span class="eyebrow">Welcome back</span><h2>Sign in</h2><p>Log in to connect with your community.</p></div>
        <form class="form-stack" action="<?= e(url(['page' => 'login'])) ?>" method="post">
            <?= csrf_field() ?>
            <label>Username or email<input name="login" type="text" autocomplete="username" placeholder="Enter your username or email" required autofocus></label>
            <label>Password<input name="password" type="password" autocomplete="current-password" required></label>
            <button class="button button-primary button-wide" type="submit">Sign in <i class="bi bi-arrow-right"></i></button>
        </form>
        <p class="auth-switch">New around here? <a href="<?= e(url(['page' => 'register'])) ?>">Make an account</a></p>
    </div>
</section>