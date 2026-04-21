<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';

const props = defineProps({
    classes: Array,
    subjects: Array,
    students: Array,
    filters: Object
});

const form = useForm({
    subject_id: props.filters.subject_id || '',
    semestre: props.filters.semestre || 'Semestre 1',
    classe_id: props.filters.classe_id || '',
    notes: {} // Format: { student_id: { note1: x, note2: y, comp: z } }
});

// Fonction pour préparer l'objet notes sans écraser les données existantes
const prepareNotesObject = () => {
    props.students.forEach(student => {
        // Correction ici : on accède à .grades[0] s'il existe
        const existingGrade = (student.grades && student.grades.length > 0) ? student.grades[0] : {};

        form.notes[student.id] = {
            note1: existingGrade.note1 ?? '',
            note2: existingGrade.note2 ?? '',
            // On utilise note_composition pour être raccord avec le controller
            note_composition: existingGrade.note_composition ?? ''
        };
    });
};


// Initialisation au montage et quand la liste d'étudiants change
onMounted(prepareNotesObject);
watch(() => props.students, prepareNotesObject, { deep: true });

// Rechargement de la page quand on change de classe, matière ou semestre
// Modifie ta fonction updateFilters pour inclure le semestre
const updateFilters = () => {
    router.get(route('grades.mass-entry'), {
        classe_id: form.classe_id,
        subject_id: form.subject_id,
        semestre: form.semestre // On envoie le semestre choisi
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const submit = () => {
    if (!form.subject_id) return alert('Veuillez sélectionner une matière');

    form.post(route('grades.mass-store'), {
        onSuccess: () => alert('Notes synchronisées avec succès !'),
        preserveScroll: true
    });
};
</script>

<template>

    <Head title="Saisie des Notes" />
    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                <h2 class="font-black text-slate-800 uppercase text-lg mb-6">🎯 Saisie Rapide par Classe</h2>

                <!-- Sélecteurs de Filtres -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div>
                        <div class="bg-indigo-50 p-2 rounded-2xl border border-indigo-100">
                             <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Classe</label>
                        <select v-model="form.classe_id" @change="updateFilters"
                            class="w-full rounded-xl border-none bg-transparent text-sm font-bold text-indigo-700 focus:ring-0">
                            <option value="">Choisir...</option>
                            <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.nom_classe }}</option>
                        </select>
                        </div>

                    </div>
                    <div>
                         <div class="bg-indigo-50 p-2 rounded-2xl border border-indigo-100">
                               <label class="text-[10px] font-black uppercase text-indigo-400 ml-2">Matière</label>
                        <select v-model="form.subject_id" @change="updateFilters"
                            class="w-full rounded-xl border-none bg-transparent text-sm font-bold text-indigo-700 focus:ring-0">
                            <option value="">Sélectionner EC...</option>
                            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                         </div>

                    </div>
                    <div>
                        <div class="bg-indigo-50 p-2 rounded-2xl border border-indigo-100">
                            <label class="text-[10px] font-black uppercase text-indigo-400 ml-2">Période
                                Scolaire</label>
                            <select v-model="form.semestre" @change="updateFilters"
                                class="w-full rounded-xl border-none bg-transparent text-sm font-bold text-indigo-700 focus:ring-0">
                                <option value="Semestre 1">Premier semestre (S1)</option>
                                <option value="Semestre 2">Second semestre (S2)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Liste des Étudiants -->
                <div v-if="students.length > 0" class="space-y-2">
                    <div class="grid grid-cols-5 gap-4 px-4 py-2 text-[10px] font-black uppercase text-slate-400">
                        <div class="col-span-2">Étudiant</div>
                        <div class="text-center">Note 1</div>
                        <div class="text-center">Note 2</div>
                        <div class="text-center">Examen</div>
                    </div>

                    <div v-for="student in students" :key="student.id"
                        class="grid grid-cols-5 items-center p-3 bg-slate-50 rounded-2xl gap-4 hover:bg-indigo-50/50 transition">

                        <div class="col-span-2">
                            <p class="font-bold text-slate-700 leading-tight">{{ student.nom }} {{ student.prenom }}</p>
                            <p class="text-[9px] text-slate-400">{{ student.matricule }}</p>
                        </div>

                        <!-- On vérifie que l'objet notes existe pour cet ID avant d'afficher l'input -->
                        <template v-if="form.notes[student.id]">
                            <input type="number" step="0.01" min="0" max="20" v-model="form.notes[student.id].note1"
                                class="rounded-xl border-gray-200 text-sm text-center focus:ring-indigo-500 focus:border-indigo-500">

                            <input type="number" step="0.01" min="0" max="20" v-model="form.notes[student.id].note2"
                                class="rounded-xl border-gray-200 text-sm text-center focus:ring-indigo-500 focus:border-indigo-500">

                            <input type="number" step="0.01" min="0" max="20"
                                v-model="form.notes[student.id].note_composition"
                                class="rounded-xl border-indigo-200 text-sm text-center bg-white font-black text-indigo-700 shadow-inner">

                        </template>
                    </div>

                    <button @click="submit" :disabled="form.processing"
                        class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase shadow-lg shadow-indigo-100 mt-6 transition hover:bg-indigo-700 disabled:opacity-50">
                        {{ form.processing ? 'Enregistrement...' : '✅ Enregistrer toutes les notes' }}
                    </button>
                </div>

                <div v-else class="text-center py-16 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-100">
                    <span class="text-4xl">🔎</span>
                    <p class="mt-4 text-slate-400 font-medium italic">Sélectionnez une classe et une matière pour
                        commencer la saisie.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
