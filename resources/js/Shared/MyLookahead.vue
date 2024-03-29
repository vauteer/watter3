<script setup>
import { computed, ref } from "vue";
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/24/outline';
import {
    Combobox,
    ComboboxButton,
    ComboboxInput,
    ComboboxLabel,
    ComboboxOption,
    ComboboxOptions,
} from '@headlessui/vue';
import { throttle, debounce } from "lodash";

let props = defineProps({
    label: String,
    error: String,
    options: Array,
    nullable: {
        type: Boolean,
        default: true,
    },
    id: String,
    optionsUp: {
        type: Boolean,
        default: false,
    },
    lowHeight: {
        type: Boolean,
        default: false,
    }
});

const filteredOptions = computed(() => {
    if (query.value.length < 3)  {
        return [];
    } else {
        const filter = query.value.toLowerCase();

        const result = props.options.filter((option) => {
            return option.name.toLowerCase().includes(filter)
        });

        if (result.length === 0) {
            return [{ id: 0, name: query.value }];
        }

        return result;
    }
});

const query = ref('')

</script>

<template>
    <Combobox as="div" :nullable="nullable">
        <ComboboxLabel class="block text-sm font-medium text-gray-700 ml-2">{{ label }}</ComboboxLabel>
        <div class="relative mt-1">
            <ComboboxInput
                class="w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                @change="query = $event.target.value"
                :display-value="(option) => option?.name"
                autocomplete="off"
                :id="id"
                :placeholder="error"
                :required="!nullable"
                :class="{'border-red-400': error}"
            />
            <ComboboxButton
                class="absolute inset-y-0 right-0 flex items-center rounded-r-md px-2 focus:outline-none">
                <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true"/>
            </ComboboxButton>
            <ComboboxOptions v-if="filteredOptions.length > 0"
                             class="absolute z-10 mt-1 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
                             :class="[lowHeight ? 'max-h-28' : 'max-h-60', { 'bottom-full' : optionsUp }]"
            >
                <ComboboxOption v-for="option in filteredOptions" :key="option.id"
                                :value="option"
                                v-slot="{ active, selected }">
                    <li :class="['relative cursor-default select-none py-2 pl-3 pr-9',
                                    active ? 'bg-indigo-600 text-white' : 'text-gray-900']">
                        <span :class="['block truncate', selected && 'font-semibold']">
                          {{ option.name }}
                        </span>

                        <span v-if="selected"
                              :class="['absolute inset-y-0 right-0 flex items-center pr-4', active ? 'text-white' : 'text-indigo-600']"
                        >
                            <CheckIcon class="h-5 w-5" aria-hidden="true"/>
                        </span>
                    </li>
                </ComboboxOption>
            </ComboboxOptions>
        </div>
        <div v-if="error" class="block text-xs font-medium text-red-500 mt-1">{{ error }}</div>
    </Combobox>
</template>

