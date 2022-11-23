<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/inertia-vue3';
import Timer from '@/Components/Timer.vue'
import { computed } from 'vue';
import ReportTimeTrackerVue from '@/Components/Report/ReportTimeTracker.vue';


const currentDate = computed(() => {
    return new Date().toLocaleDateString(['en-US'], {month: 'short', day: '2-digit', year: 'numeric'}) 
});

</script>

<template>
    <Head title="Report" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Time report
            </h2>        
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        
                        <ReportTimeTrackerVue />

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
