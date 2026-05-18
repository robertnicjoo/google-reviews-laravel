class NicxonGoogleReviews extends HTMLElement {
    connectedCallback() {
        ensureGoogleReviewsCss();
        this.load();
    }

    async load() {
        const endpoint = this.getAttribute('endpoint') || '/nicxon-google-reviews/data';
        const source = this.getAttribute('source') || this.getAttribute('location') || this.getAttribute('place-id') || '';
        const showErrors = this.hasAttribute('show-errors');

        try {
            const response = await fetch(source ? `${endpoint}?source=${encodeURIComponent(source)}` : endpoint);
            const payload = await response.json();

            if (!payload.ok) {
                this.innerHTML = showErrors ? `<div class="nxgr nxgr-error">${escapeHtml(payload.message || 'Google Reviews could not be loaded.')}</div>` : '';
                return;
            }

            this.render(payload);
        } catch {
            this.innerHTML = showErrors ? '<div class="nxgr nxgr-error">Google Reviews could not be loaded.</div>' : '';
        }
    }

    render(payload) {
        const place = payload.place || {};
        const reviews = payload.reviews || [];
        const review = reviews[0] || {};

        this.innerHTML = `
            <section class="nxgr nxgr-widget">
                <div class="nxgr-summary">
                    <div class="nxgr-place-mark"><span></span></div>
                    <div class="nxgr-summary-copy">
                        <h2>${escapeHtml(place.name || 'Google Reviews')}</h2>
                        <div class="nxgr-rating-line"><strong>${Number(place.rating || 0).toFixed(1)}</strong>${stars(place.rating)}</div>
                        <p>Based on ${Number(place.review_count || 0).toLocaleString()} reviews</p>
                        <div class="nxgr-powered"><span>powered by</span><b><span class="nxgr-g-blue">G</span><span class="nxgr-g-red">o</span><span class="nxgr-g-yellow">o</span><span class="nxgr-g-blue">g</span><span class="nxgr-g-green">l</span><span class="nxgr-g-red">e</span></b></div>
                        <a class="nxgr-review-button" href="${escapeAttr(place.google_maps_uri || 'https://www.google.com/maps')}" target="_blank" rel="noreferrer">review us on Google <span>G</span></a>
                    </div>
                </div>
                ${reviews.length ? `
                    <article class="nxgr-review is-active">
                        <div class="nxgr-review-head">
                            <div class="nxgr-avatar">${review.avatar ? `<img src="${escapeAttr(review.avatar)}" alt="">` : `<span>${escapeHtml((review.author || 'G').charAt(0))}</span>`}</div>
                            <div><h3>${escapeHtml(review.author || 'Google user')}</h3><time>${escapeHtml(review.relative_time || '')}</time></div>
                            <a class="nxgr-google-mark" href="${escapeAttr(review.google_maps_uri || '#')}" target="_blank" rel="noreferrer">G</a>
                        </div>
                        ${stars(review.rating)}
                        <p>${escapeHtml(review.text || '')}</p>
                    </article>
                ` : ''}
            </section>
        `;
    }
}

function stars(rating = 0) {
    return `<span class="nxgr-stars">${[1, 2, 3, 4, 5].map((star) => `<span class="${star <= Math.round(rating) ? 'is-filled' : ''}">★</span>`).join('')}</span>`;
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
}

function escapeAttr(value) {
    return escapeHtml(value).replace(/`/g, '&#096;');
}

function ensureGoogleReviewsCss() {
    if (document.querySelector('link[data-nxgr-css]')) return;

    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/nicxon-google-reviews/assets/widget.css';
    link.dataset.nxgrCss = 'true';
    document.head.appendChild(link);
}

customElements.define('nicxon-google-reviews', NicxonGoogleReviews);
