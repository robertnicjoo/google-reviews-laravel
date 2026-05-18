import React, { useEffect, useMemo, useState } from 'react';

export default function GoogleReviewsWidget({ source = '', location = '', placeId = '', endpoint = '/nicxon-google-reviews/data', showErrors = false }) {
    const [payload, setPayload] = useState(null);
    const [active, setActive] = useState(0);
    const url = useMemo(() => {
        const selectedSource = source || location || placeId;

        if (! selectedSource) return endpoint;

        return `${endpoint}?source=${encodeURIComponent(selectedSource)}`;
    }, [endpoint, source, location, placeId]);

    useEffect(() => {
        ensureGoogleReviewsCss();
        fetch(url).then((response) => response.json()).then(setPayload).catch(() => {
            setPayload({ ok: false, message: 'Google Reviews could not be loaded.' });
        });
    }, [url]);

    if (!payload) return null;
    if (!payload.ok) return showErrors ? <div className="nxgr nxgr-error">{payload.message}</div> : null;

    const reviews = payload.reviews || [];
    const place = payload.place || {};
    const next = (step) => setActive((current) => (current + step + reviews.length) % reviews.length);

    return (
        <section className="nxgr nxgr-widget">
            <div className="nxgr-summary">
                <div className="nxgr-place-mark"><span /></div>
                <div className="nxgr-summary-copy">
                    <h2>{place.name || 'Google Reviews'}</h2>
                    <div className="nxgr-rating-line"><strong>{Number(place.rating || 0).toFixed(1)}</strong><Stars rating={place.rating} /></div>
                    <p>Based on {Number(place.review_count || 0).toLocaleString()} reviews</p>
                    <div className="nxgr-powered"><span>powered by</span><b><span className="nxgr-g-blue">G</span><span className="nxgr-g-red">o</span><span className="nxgr-g-yellow">o</span><span className="nxgr-g-blue">g</span><span className="nxgr-g-green">l</span><span className="nxgr-g-red">e</span></b></div>
                    <a className="nxgr-review-button" href={place.google_maps_uri || 'https://www.google.com/maps'} target="_blank" rel="noreferrer">review us on Google <span>G</span></a>
                </div>
            </div>
            {reviews.length > 0 && (
                <div className="nxgr-carousel">
                    <button className="nxgr-nav" type="button" onClick={() => next(-1)}>‹</button>
                    <Review review={reviews[active]} />
                    <button className="nxgr-nav" type="button" onClick={() => next(1)}>›</button>
                    <div className="nxgr-dots">{reviews.map((_, index) => <button key={index} className={index === active ? 'is-active' : ''} onClick={() => setActive(index)} />)}</div>
                </div>
            )}
        </section>
    );
}

function Review({ review }) {
    return (
        <article className="nxgr-review is-active">
            <div className="nxgr-review-head">
                <div className="nxgr-avatar">{review.avatar ? <img src={review.avatar} alt="" /> : <span>{(review.author || 'G').charAt(0)}</span>}</div>
                <div><h3>{review.author}</h3><time>{review.relative_time}</time></div>
                <a className="nxgr-google-mark" href={review.google_maps_uri || '#'} target="_blank" rel="noreferrer">G</a>
            </div>
            <Stars rating={review.rating} />
            <p>{review.text}</p>
        </article>
    );
}

function Stars({ rating = 0 }) {
    return <span className="nxgr-stars">{[1, 2, 3, 4, 5].map((star) => <span key={star} className={star <= Math.round(rating) ? 'is-filled' : ''}>★</span>)}</span>;
}

function ensureGoogleReviewsCss() {
    if (document.querySelector('link[data-nxgr-css]')) return;

    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/nicxon-google-reviews/assets/widget.css';
    link.dataset.nxgrCss = 'true';
    document.head.appendChild(link);
}
