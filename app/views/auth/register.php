<section class="auth-layout">
    <div class="auth-panel">
        <div class="auth-heading"><span class="eyebrow">Create account</span><h2>Join <?= e($siteName) ?></h2><p>Enter your details to get started.</p></div>
        <form class="form-stack" action="<?= e(url(['page' => 'register'])) ?>" method="post">
            <?= csrf_field() ?>
            <label>Your name<input name="full_name" type="text" maxlength="80" autocomplete="name" required></label>
            <label>Username<input name="username" type="text" minlength="3" maxlength="24" pattern="[A-Za-z0-9_]+" autocomplete="username" required><small>Letters, numbers, and underscores.</small></label>
            <label>Email<input name="email" type="email" autocomplete="email" required></label>
            <label>Password<input name="password" type="password" minlength="8" autocomplete="new-password" required><small>At least 8 characters.</small></label>
            <button class="button button-primary button-wide" type="submit">Create account <i class="bi bi-arrow-right"></i></button>
        </form>
        <p class="auth-switch">Already a member? <a href="<?= e(url(['page' => 'login'])) ?>">Sign in</a></p>
    </div>
</section>