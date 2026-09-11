// Runs in <head>: applies the saved iMac flavour before the page paints
try {
  var savedFlavour = localStorage.getItem('kurse-flavour');
  if (/^(tangerine|grape|lime|strawberry)$/.test(savedFlavour)) {
    document.documentElement.dataset.flavour = savedFlavour;
  }
} catch (e) {
  // Storage blocked (private mode, strict settings): stay Bondi Blue
}
