import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

import chart01 from './components/charts/chart-01';
import chart02 from './components/charts/chart-02';
import chart03 from './components/charts/chart-03';

Alpine.plugin(persist);
window.Alpine = Alpine;
Alpine.start();

// Init charts
document.addEventListener('DOMContentLoaded', () => {
    chart01();
    chart02();
    chart03();
});

// Get the current year
const year = document.getElementById('year');
if (year) {
    year.textContent = new Date().getFullYear();
}

// Persist sidebar scroll position across page navigations
(() => {
    const KEY = 'sidebarScrollTop';
    const scroller = document.getElementById('sidebar-scroll');
    if (!scroller) return;

    const restore = () => {
        const saved = parseInt(sessionStorage.getItem(KEY) ?? '', 10);
        if (Number.isFinite(saved)) scroller.scrollTop = saved;
    };

    restore();

    let ticking = false;
    scroller.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            sessionStorage.setItem(KEY, String(scroller.scrollTop));
            ticking = false;
        });
    });

    window.addEventListener('pagehide', () => {
        sessionStorage.setItem(KEY, String(scroller.scrollTop));
    });
})();

