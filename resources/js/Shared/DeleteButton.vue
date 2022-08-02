<script setup>
let props = defineProps({
    onDelete: {
        type: Function,
        default: null,
    }
});
</script>

<template>
    <button
        class="bg-red-500 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        type="button"
        @click="onDelete"
    >
        <slot>Löschen</slot>
    </button>
</template>
