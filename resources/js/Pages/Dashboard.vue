<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import Timer from '@/Components/Timer.vue'
import { computed } from 'vue';

const currentDate = computed(() => {
    return new Date().toLocaleDateString(['en-US'], {month: 'short', day: '2-digit', year: 'numeric'}) 
});

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Time tracker <span class="bg-gray-200 text-black text-sm px-1 py-1 rounded-lg">{{ currentDate }}</span>    
            </h2>        
            
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <Timer />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
