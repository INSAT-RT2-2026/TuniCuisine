export function initNav() {
    const toggle = document.getElementById('mobile-nav-toggle');
    const nav = document.querySelector('.site-nav');

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    document.querySelectorAll('.account-menu').forEach((menu) => {
        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target)) {
                menu.removeAttribute('open');
            }
        });
    });
}
