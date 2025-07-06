const userIcon = document.querySelector('.user-info');
const popup = document.getElementById('user-popup');

userIcon.addEventListener('click', (e) => {
    e.stopPropagation();
    popup.classList.toggle('hidden');
});

// Bezárás ha máshová kattint a felhasználó
document.addEventListener('click', (e) => {
    if (!popup.contains(e.target) && !userIcon.contains(e.target)) {
        popup.classList.add('hidden');
    }
});

// Dynamically show/hide login/logout button and user name
const welcomeText = document.getElementById('welcome-text');
const loginBtn = document.getElementById('login-btn');
const logoutBtn = document.getElementById('logout-btn');

// These are set server-side in user_view.php, so no need to set them here
// Just ensure the correct button is visible (handled by PHP output)

//Contact Button
const contactBtn = document.getElementById('contact-btn'); // ID a footer gombra
const contactPopup = document.getElementById('contact-popup');

contactBtn.addEventListener('click', () => {
    contactPopup.classList.toggle('hidden');
});

// Automatikus eltüntetés 5 mp után (opcionális)
setInterval(() => {
    if (!contactPopup.classList.contains('hidden')) {
        contactPopup.classList.add('hidden');
    }
}, 5000);

// Reservation Button (footer) - only if present
const resBtn = document.getElementById('res-btn');
const resPopup = document.getElementById('res-popup');
if (resBtn && resPopup) {
    resBtn.addEventListener('click', () => {
        resPopup.classList.toggle('hidden');
    });

    setInterval(() => {
        if (!resPopup.classList.contains('hidden')) {
            resPopup.classList.add('hidden');
        }
    }, 5000);
}

const carousel = document.querySelector('#introCarousel');
const indicators = document.querySelectorAll('.custom-indicators .indicator');
if (carousel && indicators.length > 0) {
    carousel.addEventListener('slide.bs.carousel', function (event) {
        indicators.forEach(indicator => indicator.classList.remove('active'));
        indicators[event.to].classList.add('active');
    });
}

document.querySelectorAll('.custom-indicators .indicator').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
    });
});

// --- Header/Footer scroll direction logic with intro carousel detection ---

const header = document.querySelector('.header');
const footer = document.querySelector('.footer');
const introCarousel = document.getElementById('introCarousel');

let lastScrollY = window.scrollY;
let ticking = false;

function isIntroCarouselInView() {
    if (!introCarousel) return false;
    const rect = introCarousel.getBoundingClientRect();
    // Consider in view if any part is visible in viewport
    return rect.bottom > 0 && rect.top < window.innerHeight;
}

function updateHeaderFooterVisibility() {
    if (isIntroCarouselInView()) {
        header.classList.add('visible');
        footer.classList.add('visible');
        return;
    }
    if (window.scrollY < 10) {
        // At the very top, show both
        header.classList.add('visible');
        footer.classList.add('visible');
        return;
    }
    if (window.scrollY > lastScrollY) {
        // Scrolling down
        header.classList.remove('visible');
        footer.classList.remove('visible');
    } else if (window.scrollY < lastScrollY) {
        // Scrolling up
        header.classList.add('visible');
        footer.classList.add('visible');
    }
    lastScrollY = window.scrollY;
}

window.addEventListener('scroll', () => {
    if (!ticking) {
        window.requestAnimationFrame(() => {
            updateHeaderFooterVisibility();
            ticking = false;
        });
        ticking = true;
    }
});

// On load, set initial state
document.addEventListener('DOMContentLoaded', () => {
    updateHeaderFooterVisibility();
});