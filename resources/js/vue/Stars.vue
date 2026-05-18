<template>
    <span class="nxgr-stars" aria-hidden="true">
        <span v-for="star in [1, 2, 3, 4, 5]" :key="star" :class="{ 'is-filled': star <= Math.round(rating || 0) }">★</span>
    </span>
</template>

<script setup>
defineProps({ rating: { type: Number, default: 0 } });
</script>
