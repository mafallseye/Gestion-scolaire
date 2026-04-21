<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classes: Array,
    subjects: Array, // Toutes les matières envoyées par le controller
    lessons: Array, //
    students: Array,
    filters: Object
});

const selectedClasse = ref('');

// Filtrer les matières selon la classe sélectionnée
const filteredSubjects = computed(() => {
    const classe = props.classes.find(c => Number(c.id) === Number(selectedClasse.value));
    return (classe && classe.subjects) ? classe.subjects : [];
});



const form = useForm({
    title: '',
    subject_id: '',
    file: null,
});



const submit = () => {
    form.post(route('lessons.store'), {
        forceFormData: true, // <--- AJOUTE ÇA pour l'envoi du PDF
        onSuccess: () => {
            form.reset();
            alert('Cours ajouté et historique mis à jour !');
        },
          preserveScroll: true,
        preserveState: false,
    });
};
</script>

<template>
    <Head title="Gestion des Cours" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto p-6 space-y-6">
            <h2 class="text-2xl font-black text-slate-800">📚 Gestion des Supports de Cours</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- FORMULAIRE DE DÉPÔT -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 h-fit">
                    <h3 class="font-black text-slate-800 uppercase text-xs mb-6">📤 Publier un nouveau PDF</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Sélection de la Classe -->
                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-400 ml-1">1. Choisir la Classe</label>
                            <select v-model="selectedClasse" class="w-full rounded-xl border-slate-200 text-sm mt-1">
                                <option value="">Choisir...</option>
                                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.nom_classe }}</option>
                            </select>
                        </div>

                        <!-- Sélection de la Matière (Dépend de la classe) -->
                        <div v-if="selectedClasse">
                           <div class="bg-indigo-50 p-2 rounded-2xl border border-indigo-100 mb-4">
    <label class="text-[10px] font-black uppercase text-indigo-400 ml-2">Matière (EC)</label>
    <select v-model="form.subject_id"
            class="w-full rounded-xl border-none bg-transparent text-sm font-bold text-indigo-700 focus:ring-0">
        <option value="">Sélectionner EC...</option>
        <!-- Correction ici : on retire le </div> qui était en trop -->
        <option v-for="s in filteredSubjects" :key="s.id" :value="s.id">
            {{ s.name }}
        </option>
    </select>
</div>

                        </div>

                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-400 ml-1">3. Titre du chapitre</label>
                            <input v-model="form.title" type="text" placeholder="Ex: Introduction à l'algorithmique" class="w-full rounded-xl border-slate-200 text-sm mt-1" />
                        </div>

                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-400 ml-1">4. Fichier (PDF)</label>
                            <input type="file" @input="form.file = $event.target.files[0]" class="block w-full text-xs text-slate-500 mt-1" />
                        </div>

                        <button type="submit" :disabled="form.processing" class="w-full bg-indigo-600 text-white py-3 rounded-xl text-[10px] font-black uppercase hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                            {{ form.processing ? 'Envoi en cours...' : 'Publier le cours' }}
                        </button>
                    </form>
                </div>

                <!-- LISTE DES DERNIERS COURS PUBLIÉS -->
                <div class="md:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50">
                        <h3 class="font-black text-slate-800 uppercase text-xs">Historique des publications</h3>
                    </div>
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase">
                            <tr>
                                <th class="p-4">Titre</th>
                                <th class="p-4">Matière</th>
                                <th class="p-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="lesson in lessons" :key="lesson.id" class="text-sm hover:bg-slate-50 transition">
                                <td class="p-4 font-bold text-slate-700">{{ lesson.title }}</td>
                                <td class="p-4"><span class="bg-slate-100 px-3 py-1 rounded-full text-[10px] font-black uppercase">{{ lesson.subject.name }}</span></td>
                                <td class="p-4">
                                    <a :href="'/storage/' + lesson.file_path" target="_blank" class="text-indigo-600 font-black text-[10px] uppercase hover:underline">Voir PDF</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
