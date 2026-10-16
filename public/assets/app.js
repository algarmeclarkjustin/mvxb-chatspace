document.addEventListener('click', (event) => {
  const replyLink = event.target.closest('a[href^="#comment-form-"]');
  if (!replyLink) return;

  event.preventDefault();
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