// Runs in <head>: the CRT power-on in css/crt.css plays once per visit, later pages open already warm
try {
  if (sessionStorage.getItem('kurse-crt-warm')) {
    document.documentElement.dataset.crtWarm = '';
  } else {
    sessionStorage.setItem('kurse-crt-warm', '1');
  }
} catch (e) {
  // Storage blocked (private mode, strict settings): power on every load
}
