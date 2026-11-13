// Small progressive-enhancement script. Search/filtering is server-side PHP for reliability and low bandwidth.
const menuToggle = document.querySelector('.menu-toggle');
const nav = document.querySelector('.main-nav');
if (menuToggle && nav) {
  menuToggle.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    menuToggle.setAttribute('aria-expanded', String(open));
  });
  document.querySelectorAll('.main-nav a').forEach(link => link.addEventListener('click', () => {
    nav.classList.remove('open');
    menuToggle.setAttribute('aria-expanded', 'false');
  }));
}

const hodMenuToggle = document.querySelector('.hod-menu');
const hodMobileMenu = document.querySelector('.hod-mobile-menu');
if (hodMenuToggle && hodMobileMenu) {
  hodMenuToggle.addEventListener('click', () => {
    const open = hodMobileMenu.classList.toggle('open');
    hodMenuToggle.setAttribute('aria-expanded', String(open));
  });
  document.addEventListener('click', (event) => {
    if (!hodMobileMenu.contains(event.target) && !hodMenuToggle.contains(event.target)) {
      hodMobileMenu.classList.remove('open');
      hodMenuToggle.setAttribute('aria-expanded', 'false');
    }
  });
}
