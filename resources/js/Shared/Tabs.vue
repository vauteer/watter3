<script setup>
import { TabGroup, TabList, Tab } from '@headlessui/vue'

let props = defineProps({
    model: Object,
    onClicked: Function,
    selectedIndex: Number,
});

</script>

<template>
    <div>
        <TabGroup :selectedIndex="selectedIndex">
            <TabList class="flex space-x-1 rounded-xl bg-gray-500 p-1">
                <Tab
                    v-for="(value, key) in model"
                    as="template"
                    :key="key"
                    v-slot="{ selected }"
                >
                    <button
                        @click="selected ? null : onClicked(key)"
                        :class="[
                          'w-full rounded-lg py-2.5 text-sm font-medium leading-5 text-gray-700',
                          'ring-white ring-opacity-60 ring-offset-2 ring-offset-blue-400 focus:outline-none focus:ring-2',
                          selected
                            ? 'bg-white shadow'
                            : 'text-blue-100 hover:bg-white/[0.12] hover:text-white',
                        ]"
                    >
                        {{ value }}
                    </button>
                </Tab>
            </TabList>
        </TabGroup>
    </div>
</template>
