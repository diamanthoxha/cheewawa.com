// ChiLove — small front-end touches.
document.querySelectorAll('a[href^="/#"], a[href^="#"]').forEach((a) => {
    a.addEventListener('click', (e) => {
        const hash = a.getAttribute('href').split('#')[1];
        if (!hash) return;
        const el = document.getElementById(hash);
        if (el) {
            e.preventDefault();
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('nav')?.classList.remove('open');
            history.replaceState(null, '', '#' + hash);
        }
    });
});
