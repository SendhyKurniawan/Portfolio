// Aqua-style interface sounds, synthesised with Web Audio (no audio files).
// Off until the visitor turns them on with the speaker button; the choice is remembered.
(() => {
  const STORAGE_KEY = 'kurse-sound';
  const CLICKABLE = '.btn-gel, .inbox-row, .desk-icon, .nav-link, .gel-close, .emoticon, .web-badge';
  let context = null;
  let enabled = false;
  try {
    enabled = localStorage.getItem(STORAGE_KEY) === 'on';
  } catch (e) {
    // Storage blocked: sounds stay off
  }

  function audio() {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return null;
    context = context || new AudioContextClass();
    if (context.state === 'suspended') context.resume();
    return context;
  }

  function tone({ freq, endFreq = freq, start = 0, duration, type = 'sine', volume = 0.1 }) {
    const ac = audio();
    if (!ac) return;
    const t = ac.currentTime + start;
    const osc = ac.createOscillator();
    const gain = ac.createGain();
    osc.type = type;
    osc.frequency.setValueAtTime(freq, t);
    osc.frequency.exponentialRampToValueAtTime(endFreq, t + duration);
    gain.gain.setValueAtTime(0.0001, t);
    gain.gain.exponentialRampToValueAtTime(volume, t + 0.01);
    gain.gain.exponentialRampToValueAtTime(0.0001, t + duration);
    osc.connect(gain).connect(ac.destination);
    osc.start(t);
    osc.stop(t + duration + 0.02);
  }

  const SOUNDS = {
    // Short bubbly click, like pressing a gel button
    pop: () => tone({ freq: 700, endFreq: 1400, duration: 0.07, volume: 0.08 }),
    // Two-note chime when sounds are switched on
    chime: () => {
      tone({ freq: 659, duration: 0.35, type: 'triangle', volume: 0.1 });
      tone({ freq: 988, start: 0.12, duration: 0.5, type: 'triangle', volume: 0.08 });
    },
    // Rattling buzz for BUZZ!!!
    buzz: () => {
      for (let i = 0; i < 6; i++) {
        tone({ freq: 95, endFreq: 70, start: i * 0.07, duration: 0.06, type: 'sawtooth', volume: 0.07 });
      }
    },
  };

  function play(name) {
    if (enabled && SOUNDS[name]) SOUNDS[name]();
  }

  const toggle = document.querySelector('.sound-toggle');
  function render() {
    if (!toggle) return;
    toggle.setAttribute('aria-pressed', String(enabled));
    toggle.querySelector('i').className = enabled ? 'bi bi-volume-up' : 'bi bi-volume-mute';
  }

  toggle?.addEventListener('click', () => {
    enabled = !enabled;
    try {
      localStorage.setItem(STORAGE_KEY, enabled ? 'on' : 'off');
    } catch (e) {
      // Not remembered, but works for this visit
    }
    render();
    play('chime');
  });
  render();

  document.addEventListener('click', event => {
    if (event.target.closest(CLICKABLE)) play('pop');
  });

  window.kurseSound = { play };
})();
