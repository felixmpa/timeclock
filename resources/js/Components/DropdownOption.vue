<script setup>
import { computed } from 'vue';
const props = defineProps({
    value: {
        default: null,
    },
});

</script>

<template>
    <option :value="value" >
        <slot />
    </option>
</template>
