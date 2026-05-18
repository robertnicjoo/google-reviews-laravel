<template>
    <div v-if="payload && !payload.ok && showErrors" class="nxgr nxgr-error">{{ payload.message }}</div>
    <section v-else-if="payload && payload.ok" class="nxgr nxgr-widget">
        <div class="nxgr-summary">
            <div class="nxgr-place-mark"><span /></div>
            <div class="nxgr-summary-copy">
                <h2>{{ place.name || 'Google Reviews' }}</h2>
                <div class="nxgr-rating-line"><strong>{{ Number(place.rating || 0).toFixed(1) }}</strong><Stars :rating="place.rating" /></div>
                <p>Based on {{ Number(place.review_count || 0).toLocaleString() }} reviews</p>
                <div class="nxgr-powered"><span>powered by</span><b><span class="nxgr-g-blue">G</span><span class="nxgr-g-red">o</span><span class="nxgr-g-yellow">o</span><span class="nxgr-g-blue">g</span><span class="nxgr-g-green">l</span><span class="nxgr-g-red">e</span></b></div>
                <a class="nxgr-review-button" :href="place.google_maps_uri || 'https://www.google.com/maps'" target="_blank" rel="noreferrer">review us on Google <span>G</span></a>
            </div>
        </div>

        <div v-if="reviews.length" class="nxgr-carousel">
            <button class="nxgr-nav" type="button" @click="move(-1)">‹</button>
            <article class="nxgr-review is-active">
                <div class="nxgr-review-head">
                    <div class="nxgr-avatar">
                        <img v-if="current.avatar" :src="current.avatar" alt="">
                        <span v-else>{{ (current.author || 'G').charAt(0) }}</span>
                    </div>
                    <div><h3>{{ current.author }}</h3><time>{{ current.relative_time }}</time></div>
                    <a class="nxgr-google-mark" :href="current.google_maps_uri || '#'" target="_blank" rel="noreferrer">G</a>
                </div>
                <Stars :rating="current.rating" />
                <p>{{ current.text }}</p>
            </article>
            <button class="nxgr-nav" type="button" @click="move(1)">›</button>
            <div class="nxgr-dots"><button v-for="(_, index) in reviews" :key="index" :class="{ 'is-active': index === active }" @click="active = index" /></div>
        </div>
    </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import Stars from './Stars.vue';

const props = defineProps({
    source: { type: String, default: '' },
    location: { type: String, default: '' },
    placeId: { type: String, default: '' },
    endpoint: { type: String, default: '/nicxon-google-reviews/data' },
    showErrors: { type: Boolean, default: false },
});

const payload = ref(null);
const active = ref(0);
const place = computed(() => payload.value?.place || {});
const reviews = computed(() => payload.value?.reviews || []);
const current = computed(() => reviews.value[active.value] || {});

onMounted(async () => {
    ensureGoogleReviewsCss();

    try {
        const selectedSource = props.source || props.location || props.placeId;
        const url = selectedSource ? `${props.endpoint}?source=${encodeURIComponent(selectedSource)}` : props.endpoint;
        const response = await fetch(url);
        payload.value = await response.json();
    } catch {
        payload.value = { ok: false, message: 'Google Reviews could not be loaded.' };
    }
});

function move(step) {
    active.value = (active.value + step + reviews.value.length) % reviews.value.length;
}

function ensureGoogleReviewsCss() {
    if (document.querySelector('link[data-nxgr-css]')) return;

    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/nicxon-google-reviews/assets/widget.css';
    link.dataset.nxgrCss = 'true';
    document.head.appendChild(link);
}
</script>
