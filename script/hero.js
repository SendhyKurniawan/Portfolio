// Subtitle Glitch Logic
const heroSubtitle = document.querySelector('.hero-subtitle');
const subtitleTexts = [
  "[ ARCHITECTING DIGITAL EXPERIENCES ]", 
  "[ CREATIVE DEVELOPER ]", 
  "[ SYSTEM ARCHITECT ]", 
  "[ FULL STACK ENGINEER ]"
];
let subtitleIndex = 0;

setInterval(() => {
  if (!heroSubtitle) return;
  
  heroSubtitle.style.animation = 'none';
  void heroSubtitle.offsetWidth; 
  
  subtitleIndex = (subtitleIndex + 1) % subtitleTexts.length;
  const newSubtitle = subtitleTexts[subtitleIndex];
  
  heroSubtitle.setAttribute('data-text', newSubtitle);
  heroSubtitle.textContent = newSubtitle;
  
  heroSubtitle.style.animation = 'glitch-anim-1 0.5s infinite';
  setTimeout(() => {
     heroSubtitle.style.animation = 'glitch-anim-1 3s infinite linear alternate-reverse';
  }, 500);
}, 4000);

// Title Swapping Logic (KURNIAWAN SENDHY <-> KURSE.CO)
const heroTitle = document.querySelector('.hero-title');
const titleTexts = ["KURNIAWAN SENDHY", "KURSE.CO"];
let titleIndex = 0;

setInterval(() => {
  if (!heroTitle) return;

  heroTitle.style.animation = 'none';
  void heroTitle.offsetWidth; 

  titleIndex = (titleIndex + 1) % titleTexts.length;
  const newTitle = titleTexts[titleIndex];

  heroTitle.setAttribute('data-text', newTitle);
  heroTitle.textContent = newTitle;

  // Sync glitch with text change
  heroTitle.style.animation = 'glitch-anim-1 0.5s infinite';
  setTimeout(() => {
     heroTitle.style.animation = 'glitch-anim-1 3s infinite linear alternate-reverse';
  }, 500);
}, 5000); // Swap every 5 seconds
