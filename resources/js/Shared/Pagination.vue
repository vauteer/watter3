<script setup>
import { Link } from '@inertiajs/inertia-vue3';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/outline';

let props = defineProps({
    meta: Object,

    languages: Object,
});
</script>

<template>
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
            <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> Previous </a>
            <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> Next </a>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p v-if="meta.links.length < 8" class="text-sm text-gray-700">
                    {{ meta.from }} bis {{ meta.to }} von {{ meta.total }} Datensätzen
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <div v-for="(link, index) in meta.links">
                        <div v-if="index === 0">
                            <Link :href="link.url" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Previous</span>
                                <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                            </Link>
                        </div>

                        <div v-if="index > 0 && index < (meta.links.length - 1)">
                            <p v-if="index === meta.current_page" aria-current="page" class="z-10 bg-indigo-50 border-indigo-500 text-indigo-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium"> {{ link.label }} </p>
                            <Link v-else :href="link.url" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium"> {{ link.label }} </Link>

                        </div>

                        <div v-if="index === (meta.links.length - 1)">
                            <Link :href="link.url" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <span class="sr-only">Next</span>
                                <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</template>


