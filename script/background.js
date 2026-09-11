const canvas = document.createElement("canvas");
const ctx = canvas.getContext("2d");
document.body.appendChild(canvas);

canvas.style.position = "absolute";
canvas.style.top = "0";
canvas.style.left = "0";
canvas.style.width = "100%";
canvas.style.height = "100%";
canvas.style.zIndex = "-1";
canvas.style.pointerEvents = "none";

let width, height;
let particles = [];
const connectionDistance = 150;
const mouseRange = 200;
const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

// Line drawing is O(n^2), so scale the particle count with screen width
function particleCount() {
  return Math.min(100, Math.max(30, Math.floor(window.innerWidth / 15)));
}

// Mouse tracking
let mouse = {
  x: null,
  y: null,
};

window.addEventListener("mousemove", (e) => {
  mouse.x = e.pageX;
  mouse.y = e.pageY;
});

let resizeTimer;
window.addEventListener("resize", () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(resize, 200);
});

function resize() {
  width = canvas.width = window.innerWidth;
  height = canvas.height = Math.max(
    document.body.scrollHeight,
    document.documentElement.scrollHeight,
    document.body.offsetHeight,
    document.documentElement.offsetHeight,
    document.body.clientHeight,
    document.documentElement.clientHeight
  );
  createParticles();
  // Resizing clears the canvas; with no animation loop, redraw the static frame
  if (reduceMotion) drawFrame();
}

class Particle {
  constructor() {
    this.x = Math.random() * width;
    this.y = Math.random() * height;
    this.vx = (Math.random() - 0.5) * 1.5;
    this.vy = (Math.random() - 0.5) * 1.5;
    this.size = Math.random() * 2 + 1;
    this.baseX = this.x;
    this.baseY = this.y;
    this.density = Math.random() * 30 + 1;
  }

  update() {
    this.x += this.vx;
    this.y += this.vy;

    // Bounce off edges
    if (this.x < 0 || this.x > width) this.vx *= -1;
    if (this.y < 0 || this.y > height) this.vy *= -1;

    // Mouse interaction
    if (mouse.x != null) {
      let dx = mouse.x - this.x;
      let dy = mouse.y - this.y;
      let distance = Math.sqrt(dx * dx + dy * dy);

      if (distance < mouseRange) {
        const forceDirectionX = dx / distance;
        const forceDirectionY = dy / distance;
        const maxDistance = mouseRange;
        const force = (maxDistance - distance) / maxDistance;
        const directionX = forceDirectionX * force * this.density;
        const directionY = forceDirectionY * force * this.density;

        if (distance < mouseRange) {
          this.x -= directionX;
          this.y -= directionY;
        }
      }
    }
  }

  draw() {
    ctx.fillStyle = "#ff00ff";
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
    ctx.closePath();
    ctx.fill();
  }
}

function createParticles() {
  particles = Array.from({ length: particleCount() }, () => new Particle());
}

function drawFrame() {
  ctx.clearRect(0, 0, width, height);

  // Draw connecting lines
  for (let a = 0; a < particles.length; a++) {
    for (let b = a; b < particles.length; b++) {
      let dx = particles[a].x - particles[b].x;
      let dy = particles[a].y - particles[b].y;
      let distance = Math.sqrt(dx * dx + dy * dy);

      if (distance < connectionDistance) {
        let opacity = 1 - distance / connectionDistance;
        ctx.strokeStyle = `rgba(0, 243, 255, ${opacity * 0.5})`; // Cyan lines
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(particles[a].x, particles[a].y);
        ctx.lineTo(particles[b].x, particles[b].y);
        ctx.stroke();
      }
    }
  }

  particles.forEach((particle) => {
    if (!reduceMotion) particle.update();
    particle.draw();
  });
}

// Stop the loop while the tab is hidden; `running` prevents a second loop on return
let running = false;

function animate() {
  drawFrame();
  if (document.hidden) {
    running = false;
    return;
  }
  requestAnimationFrame(animate);
}

function start() {
  if (running || reduceMotion) return;
  running = true;
  requestAnimationFrame(animate);
}

document.addEventListener("visibilitychange", () => {
  if (!document.hidden) start();
});

resize();
start();
