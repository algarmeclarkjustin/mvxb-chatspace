const themeToggle = document.querySelector('[data-theme-toggle]');

function setTheme(theme) {
  document.documentElement.dataset.theme = theme;
  const isDark = theme === 'dark';
  themeToggle.setAttribute('aria-label', `Switch to ${isDark ? 'light' : 'dark'} mode`);
  themeToggle.title = `Switch to ${isDark ? 'light' : 'dark'} mode`;
  themeToggle.querySelector('i').className = `bi ${isDark ? 'bi-sun' : 'bi-moon-stars'}`;
  document.querySelector('meta[name="theme-color"]').content = isDark ? '#101614' : '#ffffff';
}

if (themeToggle) {
  setTheme(document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light');
  themeToggle.addEventListener('click', () => {
    const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    setTheme(theme);
    try {
      localStorage.setItem('chatspace-theme', theme);
    } catch (error) {
      console.warn('Theme preference could not be saved.', error);
    }
  });
}

document.addEventListener('click', (event) => {
  const shareButton = event.target.closest('[data-share-post]');
  if (shareButton) {
    const shareUrl = new URL('index.php', window.location.href);
    shareUrl.searchParams.set('page', 'feed');
    shareUrl.hash = `post-${shareButton.dataset.sharePost}`;
    const title = 'MVXB ChatSpace post';
    const text = 'Take a look at this post.';
    if (navigator.share) {
      navigator.share({ title, text, url: shareUrl.href }).catch((error) => {
        if (error.name !== 'AbortError') console.error('Post could not be shared.', error);
      });
    } else if (navigator.clipboard) {
      navigator.clipboard.writeText(shareUrl.href)
        .then(() => {
          const label = shareButton.querySelector('span');
          label.textContent = 'Copied!';
          window.setTimeout(() => { label.textContent = 'Share'; }, 1800);
        })
        .catch((error) => {
          console.error('Share link could not be copied.', error);
          window.prompt('Copy this post link:', shareUrl.href);
        });
    } else {
      window.prompt('Copy this post link:', shareUrl.href);
    }
    return;
  }

  const imageTrigger = event.target.closest('[data-image-viewer-trigger]');
  if (imageTrigger) {
    const image = imageTrigger.querySelector('img');
    const viewer = document.querySelector('[data-image-viewer]');
    const viewerImage = viewer.querySelector('[data-image-viewer-image]');
    viewerImage.src = image.currentSrc || image.src;
    viewerImage.alt = image.alt;
    viewer.showModal();
    return;
  }

  const closeButton = event.target.closest('[data-image-viewer-close]');
  if (closeButton) {
    closeButton.closest('[data-image-viewer]').close();
    return;
  }

  const viewer = event.target.closest('[data-image-viewer]');
  if (viewer && event.target === viewer) {
    viewer.close();
    return;
  }

  const replyLink = event.target.closest('a[href^="#comment-form-"]');
  if (!replyLink) return;

  event.preventDefault();
});

document.addEventListener('change', (event) => {
  const input = event.target.closest('.file-pick input[type="file"]');
  if (!input) return;

  const preview = input.form.querySelector('[data-image-preview]');
  if (!preview) return;

  const previewImage = preview.querySelector('img');
  const clearButton = preview.querySelector('[data-clear-image]');
  const file = input.files[0];

  if (preview.dataset.objectUrl) {
    URL.revokeObjectURL(preview.dataset.objectUrl);
    delete preview.dataset.objectUrl;
  }

  if (file) {
    const objectUrl = URL.createObjectURL(file);
    preview.dataset.objectUrl = objectUrl;
    previewImage.src = objectUrl;
    preview.hidden = false;
    if (clearButton) clearButton.hidden = false;
  } else if (preview.dataset.originalSrc) {
    previewImage.src = preview.dataset.originalSrc;
    preview.hidden = false;
    if (clearButton) clearButton.hidden = true;
  } else {
    previewImage.removeAttribute('src');
    preview.hidden = true;
    if (clearButton) clearButton.hidden = true;
  }
});

document.addEventListener('click', (event) => {
  const clearButton = event.target.closest('[data-clear-image]');
  if (!clearButton) return;

  const preview = clearButton.closest('[data-image-preview]');
  const input = clearButton.closest('form').querySelector('.file-pick input[type="file"]');
  input.value = '';

  if (preview.dataset.objectUrl) {
    URL.revokeObjectURL(preview.dataset.objectUrl);
    delete preview.dataset.objectUrl;
  }

  if (preview.dataset.originalSrc) {
    preview.querySelector('img').src = preview.dataset.originalSrc;
    preview.hidden = false;
    clearButton.hidden = true;
  } else {
    preview.querySelector('img').removeAttribute('src');
    preview.hidden = true;
  }
});

document.addEventListener('submit', async (event) => {
  const commentForm = event.target.closest('form.comment-form');
  if (commentForm) {
    if (commentForm.dataset.pending === 'true') {
      event.preventDefault();
      return;
    }

    event.preventDefault();
    commentForm.dataset.pending = 'true';
    const submitButton = commentForm.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    try {
      const response = await fetch(commentForm.action, {
        method: 'POST',
        body: new FormData(commentForm),
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      if (!response.ok) throw new Error('Reply failed');

      const comment = await response.json();
      const post = commentForm.closest('.post');
      const row = document.createElement('div');
      row.className = 'comment-row';

      const avatar = document.createElement('span');
      avatar.className = 'comment-avatar';
      avatar.textContent = comment.full_name.charAt(0).toUpperCase();

      const content = document.createElement('div');
      content.className = 'comment-content';
      const heading = document.createElement('p');
      const profileLink = document.createElement('a');
      profileLink.href = comment.profile_url;
      const name = document.createElement('strong');
      name.textContent = comment.full_name;
      profileLink.append(name);
      const date = document.createElement('span');
      date.textContent = comment.created_at;
      heading.append(profileLink, document.createTextNode(' '), date);

      const text = document.createElement('div');
      text.className = 'comment-text';
      text.textContent = comment.content;
      content.append(heading, text);
      row.append(avatar, content);
      commentForm.before(row);

      const count = Number(post.querySelector('[data-comment-total]').textContent) + 1;
      post.querySelector('[data-comment-total]').textContent = count;
      post.querySelector('[data-comment-word]').textContent = count === 1 ? 'reply' : 'replies';
      commentForm.reset();
    } catch {
      commentForm.submit();
    } finally {
      delete commentForm.dataset.pending;
      submitButton.disabled = false;
    }
    return;
  }

  const commentLikeForm = event.target.closest('form.comment-like-form');
  if (commentLikeForm) {
    event.preventDefault();
    if (commentLikeForm.dataset.pending === 'true') return;
    commentLikeForm.dataset.pending = 'true';
    const button = commentLikeForm.querySelector('button');
    try {
      const response = await fetch(commentLikeForm.action, {
        method: 'POST',
        body: new FormData(commentLikeForm),
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!response.ok) throw new Error('Comment reaction failed');
      const state = await response.json();
      button.classList.toggle('is-liked', state.liked);
      button.setAttribute('aria-pressed', String(state.liked));
      button.querySelector('i').className = `bi ${state.liked ? 'bi-heart-fill' : 'bi-heart'}`;
      button.querySelector('[data-comment-like-count]').textContent = state.count;
    } catch {
      commentLikeForm.submit();
    } finally {
      delete commentLikeForm.dataset.pending;
    }
    return;
  }

  const form = event.target.closest('form.like-form');
  if (!form || form.dataset.pending === 'true') return;

  event.preventDefault();
  form.dataset.pending = 'true';

  try {
    const response = await fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    if (!response.ok) throw new Error('Reaction failed');

    const state = await response.json();
    const post = form.closest('.post');
    const button = form.querySelector('button');
    const icon = button.querySelector('i');
    button.classList.toggle('is-liked', state.liked);
    button.setAttribute('aria-pressed', String(state.liked));
    icon.classList.toggle('bi-heart-fill', state.liked);
    icon.classList.toggle('bi-heart', !state.liked);
    post.querySelector('[data-like-total]').textContent = state.count;
    post.querySelector('[data-like-word]').textContent = state.count === 1 ? 'heart' : 'hearts';
  } catch {
    form.submit();
  } finally {
    delete form.dataset.pending;
  }
});