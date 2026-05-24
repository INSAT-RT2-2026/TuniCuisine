let regionsCleanupFns = [];

export function initRegions() {
    regionsCleanupFns.forEach((fn) => fn());
    regionsCleanupFns = [];

    const filterButtons = document.querySelectorAll('.regions-page .filter-item[data-filter]');
    const cards = document.querySelectorAll('.regions-page .region-card[data-region-type]');
    if (!filterButtons.length || !cards.length) return;

    const applyFilter = (filter) => {
        filterButtons.forEach((btn) => {
            btn.classList.toggle('active', btn.dataset.filter === filter);
        });

        cards.forEach((card) => {
            const type = card.dataset.regionType;
            const show = filter === 'all' || type === filter;
            card.classList.toggle('is-hidden', !show);
        });
    };

    filterButtons.forEach((btn) => {
        const handler = () => applyFilter(btn.dataset.filter || 'all');
        btn.addEventListener('click', handler);
        regionsCleanupFns.push(() => btn.removeEventListener('click', handler));
    });

    if (window.location.hash.startsWith('#region-')) {
        const target = document.querySelector(window.location.hash);
        if (target) {
            setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'center' }), 300);
        }
    }
}
