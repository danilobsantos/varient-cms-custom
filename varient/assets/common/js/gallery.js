const app = {
    msnry: null,
    lightbox: null,

    init: () => {
        const container = document.getElementById('images-container');
        if (!container) return;

        app.initLightbox();

        // Initialize Masonry for ALL images at once
        imagesLoaded(container, function () {
            app.msnry = new Masonry(container, {
                itemSelector: '.masonry-grid-item',
                columnWidth: '.masonry-grid-sizer',
                percentPosition: true,
                gutter: 24,
                transitionDuration: 0
            });

            container.classList.remove('is-loading');

            // Simple staggered reveal for all items
            const items = container.querySelectorAll('.masonry-grid-item');
            items.forEach((item, index) => {
                const img = item.querySelector('img');
                if (img) img.classList.add('loaded');
                // Quick 20ms stagger
                setTimeout(() => {
                    item.classList.add('is-visible');
                }, index * 20);
            });
        });
    },

    filterImages: (category, btnElement) => {
        // UI Update
        document.querySelectorAll('.btn-filter').forEach(btn => btn.classList.remove('active'));
        btnElement.classList.add('active');

        const items = document.querySelectorAll('.masonry-grid-item');

        // Show/Hide Logic
        items.forEach(item => {
            const itemCat = item.dataset.category;
            if (category === 'All' || itemCat === category) {
                item.style.display = 'block';
                item.classList.add('glightbox');
            } else {
                item.style.display = 'none';
                item.classList.remove('glightbox');
            }
        });

        // Re-layout Masonry to fill gaps
        if (app.msnry) app.msnry.layout();

        // Update Lightbox to ignore hidden items
        app.initLightbox();
    },

    initLightbox: () => {
        if (app.lightbox) app.lightbox.destroy();
        app.lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            autoplayVideos: true,
            zoomable: true
        });
    }
};

document.addEventListener('DOMContentLoaded', app.init);