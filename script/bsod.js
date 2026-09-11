// "Press any key to continue" means what it says. Tab and the modifier keys are left
// alone so the two links can still be reached from the keyboard.
(() => {
  const PASSIVE_KEYS = ['Tab', 'Shift', 'Control', 'Alt', 'Meta', 'Enter', ' '];

  document.addEventListener('keydown', event => {
    if (PASSIVE_KEYS.includes(event.key) || event.ctrlKey || event.metaKey || event.altKey) return;
    window.location.href = '/index.php';
  });
})();
