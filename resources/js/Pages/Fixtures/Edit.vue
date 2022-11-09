<script setup>
import { computed, ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm, Head } from "@inertiajs/inertia-vue3";
import MyTextInput from '@/Shared/MyTextInput.vue';
import MyButton from '@/Shared/MyButton.vue';

let props = defineProps({
    origin: String,
    fixture: Object,
    scorePattern: String,
    placeholder: String,
});

let form = useForm({
    score: '',
});

onMounted(() => {
    if (props.fixture !== undefined) {
        form.score = props.fixture.score;
    }
    document.getElementById('score').focus();
});

let submit = () => {
    form.put(route('fixtures.update', props.fixture.id));
};

</script>

<template>
    <Head title="Ergebnis" />

    <div>
        <button
            tabindex="-1"
            class="hidden md:block fixed z-20 inset-0 h-full w-full bg-black opacity-50 cursor-default"
        ></button>
        <div class="relative z-30 w-full max-w-xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2">
            <div class="sm:px-2 lg:px-4 sm:py-2 lg:py-4">
                <div class="font-medium text-lg text-center text-gray-900 ml-3 mb-4">
                    <div>{{ fixture.team1 }}</div>
                    <div>gegen</div>
                    <div>{{ fixture.team2 }}</div>
                </div>

                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg sm:px-2 lg:px-4 bg-white">
                    <form @submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
                        <div class="space-y-8 divide-y divide-gray-200 my-3 mx-2">
                            <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                                <MyTextInput class="sm:col-span-6" v-model="form.score" :error="form.errors.score"
                                           id="score" label="Ergebnis" :regex="scorePattern" :placeholder="placeholder"/>
                            </div>
                            <div class="py-5">
                                <div class="flex justify-between">
                                    <div class="w-full flex justify-end">
                                        <MyButton theme="abort" @click="Inertia.get(origin)">
                                            Abbrechen
                                        </MyButton>
                                        <MyButton type="submit" class="ml-2">Speichern</MyButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
