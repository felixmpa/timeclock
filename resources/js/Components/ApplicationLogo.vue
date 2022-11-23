<script setup>
import { computed } from 'vue';

const props = defineProps(['src']);
const source = computed(() => props.src
    ? window.location.origin + '/img/logo-app-boostbpo.png'
    : window.location.origin +'/img/logo-app-boostbpo.png'
);

</script>
<template>
   
</template>
