// Guestbook as a 2003 messenger window: BUZZ!!!, emoticons and Ctrl+Enter to send
(() => {
  const chat = document.getElementById('chatWindow');
  if (!chat) return;
  const MAX_BUZZ_LINES = 3;
  const log = chat.querySelector('.chat-log');
  const form = chat.querySelector('form');
  const message = chat.querySelector('#gb-message');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  chat.querySelector('.buzz-btn')?.addEventListener('click', () => {
    const line = document.createElement('p');
    line.className = 'chat-line chat-line--buzz';
    line.textContent = 'BUZZ!!!';
    log.appendChild(line);
    const buzzLines = log.querySelectorAll('.chat-line--buzz');
    if (buzzLines.length > MAX_BUZZ_LINES) buzzLines[0].remove();
    log.scrollTop = log.scrollHeight;

    window.kurseSound?.play('buzz');
    if (reduceMotion.matches) return;
    // Restart the shake even if it's already running
    chat.classList.remove('is-buzzing');
    void chat.offsetWidth;
    chat.classList.add('is-buzzing');
  });

  chat.addEventListener('animationend', event => {
    if (event.target === chat) chat.classList.remove('is-buzzing');
  });

  chat.querySelectorAll('[data-emoticon]').forEach(button => {
    button.addEventListener('click', () => {
      const start = message.selectionStart ?? message.value.length;
      const end = message.selectionEnd ?? start;
      const before = message.value.slice(0, start);
      const spacer = before && !/\s$/.test(before) ? ' ' : '';
      message.setRangeText(spacer + button.dataset.emoticon + ' ', start, end, 'end');
      message.focus();
    });
  });

  message?.addEventListener('keydown', event => {
    if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
      event.preventDefault();
      form.requestSubmit();
    }
  });
})();
