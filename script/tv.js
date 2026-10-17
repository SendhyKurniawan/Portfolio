// The hero TV: a YouTube video that starts playing muted, because browsers hold sound back
// until the visitor clicks somewhere. While the sound is on the disc spins, and once the TV scrolls
// out of view it docks in a corner so the music rides along. Power or mute sends it home.
(() => {
  const tv = document.querySelector('.tv');
  if (!tv) return;
  const slot = tv.parentElement;
  const disc = document.querySelector('.disc-wrap');
  const lcd = tv.querySelector('.tv-lcd');
  const soundButton = tv.querySelector('.tv-sound');
  const powerButton = tv.querySelector('.tv-power');
  let player = null;
  let playing = false;
  // Tracked here: the player's own isMuted() lags a moment behind mute() and unMute()
  let muted = true;
  let inView = true;
  // The video's owner can block playback on other sites, or in some countries
  let noSignal = false;

  function render() {
    const audible = playing && !muted;
    if (audible) disc.dataset.state = 'playing';
    else if (disc.dataset.state) disc.dataset.state = 'paused';

    soundButton.setAttribute('aria-pressed', String(!muted));
    powerButton.setAttribute('aria-pressed', String(playing));
    lcd.textContent = noSignal ? 'NO SIGNAL' : !playing ? 'PAUSED' : muted ? 'TAP FOR SOUND' : `♪ ${tv.dataset.title}`;

    const dock = audible && !inView;
    // Hold the TV's place so the page under it doesn't jump when it leaves for the corner
    if (dock) slot.style.height = `${slot.offsetHeight}px`;
    tv.classList.toggle('is-docked', dock);
  }

  function setSound(on) {
    muted = !on;
    if (muted) {
      player.mute();
    } else {
      player.unMute();
      player.playVideo();
    }
    render();
  }

  soundButton.addEventListener('click', () => {
    if (player) setSound(muted);
  });

  // The visitor's first click anywhere turns the sound on, the earliest browsers allow it.
  // A first click on the TV's own buttons is left to them
  let woken = false;
  document.addEventListener('click', ({ target }) => {
    if (woken || !player) return;
    woken = true;
    if (!tv.contains(target)) setSound(true);
  });

  powerButton.addEventListener('click', () => {
    if (!player) return;
    if (playing) player.pauseVideo();
    else player.playVideo();
  });

  new IntersectionObserver(([entry]) => {
    inView = entry.isIntersecting;
    render();
  }).observe(slot);

  // YouTube's IFrame API takes over the iframe already in the page
  window.onYouTubeIframeAPIReady = () => {
    player = new YT.Player(tv.querySelector('iframe'), {
      events: {
        onStateChange: ({ data }) => {
          playing = data === YT.PlayerState.PLAYING || data === YT.PlayerState.BUFFERING;
          render();
        },
        onError: () => {
          noSignal = true;
          render();
        },
      },
    });
  };
  const api = document.createElement('script');
  api.src = 'https://www.youtube.com/iframe_api';
  document.head.append(api);
})();
