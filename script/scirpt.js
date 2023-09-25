const home = document.getElementById("home");
const about = document.getElementById("about");
const projects = document.getElementById("projects");
const contact = document.getElementById("contact");

function scrollByPercentage(percentage) {
  // Ensure percentage is within the range [0, 100]
  percentage = Math.min(100, Math.max(0, percentage));

  // Calculate the scroll position based on the percentage
  const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
  const scrollTo = (percentage / 100) * scrollHeight;

  // Scroll the window to the calculated position
  window.scrollTo(0, scrollTo);
}

// Usage example: Scroll by 50% of the page height
scrollByPercentage(50);

home.addEventListener("click", function () {
  window.scrollByPercentage(0);
  home.classList.add("nav-link-active");
  projects.classList.remove("nav-link-active");
  contact.classList.remove("nav-link-active");
});
projects.addEventListener("click", function () {
  window.scrollByPercentage(50);
  home.classList.remove("nav-link-active");
  projects.classList.add("nav-link-active");
  contact.classList.remove("nav-link-active");
});
contact.addEventListener("click", function () {
  window.scrollByPercentage(100);
  home.classList.remove("nav-link-active");
  projects.classList.remove("nav-link-active");
  contact.classList.add("nav-link-active");
});

const cuffour = document.getElementById("cuffour");
const lynx = document.getElementById("lynx");
const lapizzeria = document.getElementById("lapizzeria");
const mrs = document.getElementById("mrs");
const imedia = document.getElementById("imedia");
const cuffourexp = document.getElementById("cuffourexp");
const lynxexp = document.getElementById("lynxexp");
const lapizzeriaexp = document.getElementById("lapizzeriaexp");
const mrsexp = document.getElementById("mrsexp");
const imediaexp = document.getElementById("imediaexp");

cuffour.addEventListener("mouseenter", function () {
  cuffour.classList.add("projhover");
  lynx.classList.remove("projhover");
  lapizzeria.classList.remove("projhover");
  mrs.classList.remove("projhover");
  imedia.classList.remove("projhover");
  cuffourexp.classList.remove("d-none");
  lynxexp.classList.add("d-none");
  lapizzeriaexp.classList.add("d-none");
  mrsexp.classList.add("d-none");
  imediaexp.classList.add("d-none");
});
lynx.addEventListener("mouseenter", function () {
  cuffour.classList.remove("projhover");
  lynx.classList.add("projhover");
  lapizzeria.classList.remove("projhover");
  mrs.classList.remove("projhover");
  imedia.classList.remove("projhover");
  cuffourexp.classList.add("d-none");
  lynxexp.classList.remove("d-none");
  lapizzeriaexp.classList.add("d-none");
  mrsexp.classList.add("d-none");
  imediaexp.classList.add("d-none");
});
lapizzeria.addEventListener("mouseenter", function () {
  cuffour.classList.remove("projhover");
  lynx.classList.remove("projhover");
  lapizzeria.classList.add("projhover");
  mrs.classList.remove("projhover");
  imedia.classList.remove("projhover");
  cuffourexp.classList.add("d-none");
  lynxexp.classList.add("d-none");
  lapizzeriaexp.classList.remove("d-none");
  mrsexp.classList.add("d-none");
  imediaexp.classList.add("d-none");
});
mrs.addEventListener("mouseenter", function () {
  cuffour.classList.remove("projhover");
  lynx.classList.remove("projhover");
  lapizzeria.classList.remove("projhover");
  mrs.classList.add("projhover");
  imedia.classList.remove("projhover");
  cuffourexp.classList.add("d-none");
  lynxexp.classList.add("d-none");
  lapizzeriaexp.classList.add("d-none");
  mrsexp.classList.remove("d-none");
  imediaexp.classList.add("d-none");
});
imedia.addEventListener("mouseenter", function () {
  cuffour.classList.remove("projhover");
  lynx.classList.remove("projhover");
  lapizzeria.classList.remove("projhover");
  mrs.classList.remove("projhover");
  imedia.classList.add("projhover");
  cuffourexp.classList.add("d-none");
  lynxexp.classList.add("d-none");
  lapizzeriaexp.classList.add("d-none");
  mrsexp.classList.add("d-none");
  imediaexp.classList.remove("d-none");
});

const scriptURL = "https://script.google.com/macros/s/AKfycbzfBkO4U0w79AUj2abG95z2LPtm6HNYvy_X1usD9poEOkq4aixLyW_8uuJi2IksQ072/exec";
const form = document.forms["contact-form"];
const btnKirim = document.querySelector("btn-kirim");
const btnLoading = document.querySelector("btn-loading");
const myAlert = document.querySelector(".my-alert");

form.addEventListener("submit", (e) => {
  e.preventDefault();

  document.getElementById("btnKirim").classList.add("d-none");
  document.getElementById("btnLoading").classList.remove("d-none");

  fetch(scriptURL, { method: "POST", body: new FormData(form) })
    .then((response) => {
      document.getElementById("btnKirim").classList.remove("d-none");
      document.getElementById("btnLoading").classList.add("d-none");
      myAlert.classList.toggle("d-none");
      form.reset();
      console.log("Success!", response);
    })
    .catch((error) => console.error("Error!", error.message));
});

