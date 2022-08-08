<script setup>
import { computed, ref, onMounted } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { useForm } from "@inertiajs/inertia-vue3";
import Layout from '@/Shared/Layout.vue';
import EditTitle from '@/Shared/EditTitle.vue'
import TextInput from '@/Shared/TextInput.vue';
import AbortButton from '@/Shared/AbortButton.vue';
import SubmitButton from '@/Shared/SubmitButton.vue';

let props = defineProps({
    fixture: Object,
    scorePattern: String|null,
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
    form.put(`/tournaments/fixtures/${props.fixture.id}`);
};

</script>

<template>
    <Layout>
        <div>
            <button
                tabindex="-1"
                class="hidden md:block fixed z-20 inset-0 h-full w-full bg-black opacity-50 cursor-default"
            ></button>
            <div class="relative z-30 w-full max-w-xl mx-auto bg-gray-100 text-gray-900 text-sm sm:rounded sm:border sm:shadow sm:overflow-hidden mt-2">
                <div class="sm:px-2 lg:px-4 sm:py-2 lg:py-4">
                    <EditTitle class="ml-3 mb-4">Ergebnis bearbeiten</EditTitle>

                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg sm:px-2 lg:px-4 bg-white">
                        <form @submit.prevent="submit" class="space-y-8 divide-y divide-gray-200">
                            <div class="space-y-8 divide-y divide-gray-200 my-3 mx-2">
                                <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                                    <TextInput class="sm:col-span-6" v-model="form.score" :error="form.errors.score"
                                               id="score" label="Ergebnis" :regex="null"/>
                                </div>
                                <div class="py-5">
                                    <div class="flex justify-between">
                                        <div class="w-full flex justify-end">
                                            <AbortButton :href="route('tournaments.show', fixture.tournament_id)">
                                                Abbrechen
                                            </AbortButton>
                                            <SubmitButton class="ml-2">Speichern</SubmitButton>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>
