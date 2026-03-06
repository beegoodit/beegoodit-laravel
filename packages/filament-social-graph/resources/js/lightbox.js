(function () {
    const OVERLAY_ID = 'fsg-lightbox';
    const SELECTOR = '[data-lightbox]';
    const GROUP_SELECTOR = '[data-lightbox-group]';

    let urls = [];
    let currentIndex = 0;

    function getOverlay() {
        return document.getElementById(OVERLAY_ID);
    }

    function showImage(index) {
        const overlay = getOverlay();
        if (!overlay || index < 0 || index >= urls.length) return;
        currentIndex = index;
        const url = urls[currentIndex];
        const img = overlay.querySelector('[data-fsg-lightbox-image]');
        if (img) img.src = url;
    }

    function openGallery(urlsList, index) {
        const overlay = getOverlay();
        if (!overlay) return;
        urls = urlsList;
        currentIndex = index;
        const prevBtn = overlay.querySelector('[data-fsg-lightbox-prev]');
        const nextBtn = overlay.querySelector('[data-fsg-lightbox-next]');
        const hasMultiple = urls.length > 1;
        if (prevBtn) {
            prevBtn.hidden = !hasMultiple;
        }
        if (nextBtn) {
            nextBtn.hidden = !hasMultiple;
        }
        showImage(currentIndex);
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function goPrev() {
        if (urls.length <= 1) return;
        currentIndex = (currentIndex - 1 + urls.length) % urls.length;
        showImage(currentIndex);
    }

    function goNext() {
        if (urls.length <= 1) return;
        currentIndex = (currentIndex + 1) % urls.length;
        showImage(currentIndex);
    }

    function close() {
        const overlay = getOverlay();
        if (!overlay) return;
        overlay.hidden = true;
        document.body.style.overflow = '';
    }

    function init() {
        const overlay = getOverlay();
        if (!overlay) return;

        overlay.querySelectorAll('[data-fsg-lightbox-backdrop], [data-fsg-lightbox-close]').forEach(function (el) {
            el.addEventListener('click', close);
        });

        const prevBtn = overlay.querySelector('[data-fsg-lightbox-prev]');
        const nextBtn = overlay.querySelector('[data-fsg-lightbox-next]');
        if (prevBtn) prevBtn.addEventListener('click', goPrev);
        if (nextBtn) nextBtn.addEventListener('click', goNext);

        document.addEventListener('keydown', function (e) {
            const overlayEl = getOverlay();
            if (!overlayEl || overlayEl.hidden) return;
            if (e.key === 'Escape') {
                close();
            } else if (e.key === 'ArrowLeft') {
                goPrev();
            } else if (e.key === 'ArrowRight') {
                goNext();
            }
        });

        document.addEventListener('click', function (e) {
            const link = e.target.closest(SELECTOR);
            if (!link) return;
            e.preventDefault();
            const url = link.getAttribute('href') || link.getAttribute('data-src');
            if (!url) return;

            const container = link.closest(GROUP_SELECTOR);
            if (container) {
                const links = container.querySelectorAll(SELECTOR);
                const urlList = Array.from(links).map(function (l) {
                    return l.getAttribute('href') || l.getAttribute('data-src') || '';
                }).filter(Boolean);
                const idx = Array.from(links).indexOf(link);
                openGallery(urlList, idx >= 0 ? idx : 0);
            } else {
                openGallery([url], 0);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
