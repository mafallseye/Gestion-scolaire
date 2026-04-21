<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    classes: Array,
    subjects: Array,
    schedule: Object, // Les cours groupés par jour
    filters: Object
});

const days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

const form = useForm({
    classe_id: props.filters.classe_id || '',
    subject_id: '',
    day: 'Lundi',
    start_time: '08:00',
    end_time: '10:00',
    room: ''
});

const submit = () => {
    form.post(route('timetable.store'), {
        onSuccess: () => {
            form.reset('subject_id', 'start_time', 'end_time', 'room');
            alert('Cours ajouté !');
        }
    });
};

const deleteCourse = (id) => {
    if (confirm('Supprimer ce cours ?')) {
        router.delete(route('timetable.destroy', id));
    }
};

const changeClasse = (id) => {
    router.get(route('timetable.index'), { classe_id: id }, { preserveState: true });
};
</script>

<template>
    <Head title="Emploi du Temps" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto p-6 space-y-8">

            <!-- SECTION 1 : FILTRE ET AJOUT (ADMIN) -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 space-y-6">
                <div class="flex justify-between items-center border-b pb-4">
                    <h2 class="font-black text-slate-800 uppercase tracking-tight text-xl">📅 Gestion du Planning</h2>
<div v-if="filters.classe_id" class="flex justify-end mb-4">
    <a :href="route('timetable.download', filters.classe_id)"
       class="inline-flex items-center gap-2 bg-emerald-600 text-white px-6 py-2 rounded-xl font-black uppercase text-[10px] shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition">
        <span>🖨️</span> Télécharger le Planning (PDF)
    </a>
</div>

                    <select @change="changeClasse($event.target.value)" v-model="form.classe_id" class="rounded-2xl border-slate-200 bg-slate-50 font-bold text-indigo-600">
                        <option value="">Choisir une classe...</option>
                        <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.nom_classe }}</option>
                    </select>
                </div>

                <!-- Formulaire d'ajout rapide -->
                <form v-if="form.classe_id && $page.props.auth.user.role === 'admin'" @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Jour</label>
                        <select v-model="form.day" class="w-full rounded-xl border-slate-100 bg-slate-50 text-sm">
                            <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Matière (EC)</label>
                        <select v-model="form.subject_id" class="w-full rounded-xl border-slate-100 bg-slate-50 text-sm">
                            <option value="">Choisir...</option>
                            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Début</label>
                        <input type="time" v-model="form.start_time" class="w-full rounded-xl border-slate-100 bg-slate-50 text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Fin</label>
                        <input type="time" v-model="form.end_time" class="w-full rounded-xl border-slate-100 bg-slate-50 text-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Salle</label>
                        <input type="text" v-model="form.room" placeholder="Ex: Salle 102" class="w-full rounded-xl border-slate-100 bg-slate-50 text-sm">
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white py-3 rounded-xl font-black uppercase text-[10px] hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">Ajouter</button>
                </form>
            </div>

            <!-- SECTION 2 : AFFICHAGE EN COLONNES (L'EMPLOI DU TEMPS) -->
            <div v-if="form.classe_id" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div v-for="day in days" :key="day" class="space-y-4">
                    <!-- En-tête du jour -->
                    <div class="bg-slate-800 text-white p-3 rounded-2xl text-center shadow-md">
                        <p class="text-[10px] font-black uppercase tracking-widest">{{ day }}</p>
                    </div>

                    <!-- Liste des cours du jour -->
                    <div class="space-y-3 min-h-[200px]">
                        <div v-for="course in schedule[day]" :key="course.id"
                             class="group bg-white p-4 rounded-3xl shadow-sm border-t-4 border-indigo-500 hover:shadow-xl transition-all relative">

                            <p class="text-[10px] font-black text-indigo-600 uppercase leading-tight">{{ course.subject.name }}</p>

                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-[9px] font-bold text-slate-400 italic">
                                    {{ course.start_time.substring(0, 5) }} - {{ course.end_time.substring(0, 5) }}
                                </span>
                            </div>

                            <div v-if="course.room" class="mt-3 inline-flex items-center gap-1 bg-slate-100 px-2 py-1 rounded-lg">
                                <span class="text-[8px] font-black text-slate-500 uppercase tracking-tighter">📍 {{ course.room }}</span>
                            </div>

                            <!-- Bouton supprimer au survol -->
                            <button v-if="$page.props.auth.user.role === 'admin'"
        @click="deleteCourse(course.id)" class="absolute -top-2 -right-2 bg-rose-500 text-white w-6 h-6 rounded-full opacity-0 group-hover:opacity-100 transition shadow-lg flex items-center justify-center text-xs">✕</button>
                        </div>

                        <!-- Si aucun cours ce jour-là -->
                        <div v-if="!schedule[day] || schedule[day].length === 0" class="border-2 border-dashed border-slate-100 rounded-3xl py-10 text-center">
                            <span class="text-slate-200 text-xs italic">Repos</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- État vide -->
            <div v-else class="text-center py-20 bg-white rounded-3xl border border-slate-100">
                <p class="text-3xl">🗓️</p>
                <p class="text-slate-400 font-bold mt-4 italic">Veuillez sélectionner une classe pour afficher ou modifier son emploi du temps.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
