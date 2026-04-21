<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    student: Object,
    gradesByUe: Object, // Les notes groupées par le contrôleur
    filters: Object,
    summary: Object
});

const currentSemestre = ref(props.filters.semestre);

const changeSemestre = () => {
    router.get(route('student.grades'), { semestre: currentSemestre.value }, {
        preserveState: true,
        preserveScroll: true
    });
};


</script>

<template>
    <Head title="Mon Relevé Détaillé" />
    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto p-6 space-y-6">
                <!-- 1. RÉSUMÉ DE PERFORMANCE & STATUT (CADRES DU HAUT) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Moyenne Annuelle -->
                <div class="bg-indigo-600 p-6 rounded-3xl text-white shadow-lg shadow-indigo-100 relative overflow-hidden">
                    <p class="text-[10px] font-black uppercase opacity-70">Moyenne Générale</p>
                    <p class="text-4xl font-black">{{ summary.average }} <span class="text-sm">/ 20</span></p>
                    <span class="absolute -right-4 -bottom-4 text-6xl opacity-10">📈</span>
                </div>

                <!-- Crédits Capitalisés -->
                <div class="bg-emerald-500 p-6 rounded-3xl text-white shadow-lg shadow-emerald-100 relative overflow-hidden">
                    <p class="text-[10px] font-black uppercase opacity-70">Crédits ECTS</p>
                    <p class="text-4xl font-black">{{ summary.credits }} <span class="text-sm">/ 60</span></p>
                    <span class="absolute -right-4 -bottom-4 text-6xl opacity-10">🎓</span>
                </div>

                <!-- Statut de Passage Dynamique -->
                <div :class="{
                    'bg-white border-emerald-500 text-emerald-600': summary.average >= 10,
                    'bg-white border-amber-500 text-amber-600': summary.credits >= 42 && summary.average < 10,
                    'bg-white border-rose-500 text-rose-600': summary.credits < 42
                }" class="p-6 rounded-3xl border-2 shadow-sm flex flex-col justify-center items-center text-center">
                    <p class="text-[10px] font-black uppercase opacity-70 mb-1 tracking-widest">Décision Annuelle</p>
                    <p class="text-xl font-black uppercase leading-tight">
                        {{ summary.average >= 10 ? 'Admis (V.C)' : (summary.credits >= 42 ? 'Passage Cond.' : 'Ajourné') }}
                    </p>
                </div>
            </div>

            <!-- HEADER INTERACTIF -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-black text-slate-800 uppercase italic tracking-tight">📊 Relevé de Notes Détaillé</h2>
                    <p class="text-[10px] font-bold text-slate-400">Consultation des performances par Unité d'Enseignement</p>
                </div>

                <div class="bg-emerald-50 p-2 rounded-2xl border border-emerald-100 flex items-center gap-3">
                    <span class="text-[9px] font-black text-emerald-600 uppercase ml-2">Période :</span>
                    <select v-model="currentSemestre" @change="changeSemestre"
                        class="border-none bg-transparent text-xs font-black text-emerald-700 uppercase focus:ring-0 cursor-pointer">
                        <option value="Semestre 1">Semestre 1 (S1)</option>
                        <option value="Semestre 2">Semestre 2 (S2)</option>
                    </select>
                </div>
            </div>

            <!-- AFFICHAGE PAR UNITÉ D'ENSEIGNEMENT (UE) -->
            <div v-if="Object.keys(gradesByUe).length > 0">
                <div v-for="(grades, ueLabel) in gradesByUe" :key="ueLabel" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-8">
                    <div class="p-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                        <h3 class="font-black text-slate-800 text-[10px] uppercase tracking-widest italic">{{ ueLabel }}</h3>
                        <span class="text-[9px] font-bold text-slate-400 uppercase">Détails des EC</span>
                    </div>

                    <div class="overflow-x-auto">
                     <table class="w-full text-left text-xs">
                            <thead class="bg-white text-[9px] font-black text-slate-400 uppercase border-b">
                                <tr>
                                    <th class="p-4">Matière (EC)</th>
                                    <th class="p-4 text-center">Crédits</th>
                                    <th class="p-4 text-center">CC 1</th>
                                    <th class="p-4 text-center">CC 2</th>
                                    <th class="p-4 text-center">Examen</th>
                                    <th class="p-4 text-center font-bold">Moyenne</th>
                                    <th class="p-4 text-right">Validation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="grade in grades" :key="grade.id" class="hover:bg-slate-50 transition">
                                    <td class="p-4 font-bold text-slate-700">{{ grade.subject.name }}</td>
                                    <td class="p-4 text-center font-black text-slate-300">{{ grade.subject.credits }}</td>
                                    <td class="p-4 text-center">{{ grade.note1 }}</td>
                                    <td class="p-4 text-center">{{ grade.note2 }}</td>
                                    <td class="p-4 text-center">{{ grade.note_composition }}</td>
                                    <td class="p-4 text-center font-black text-indigo-600">{{ grade.moyenne_module }}</td>
                                    <td class="p-4 text-right">
                                        <span v-if="grade.moyenne_module >= 10 || summary.average >= 10"
                                              class="text-emerald-500 font-black text-[9px] uppercase tracking-tighter">
                                            ✅ V.C (Acquis)
                                        </span>
                                        <span v-else class="text-rose-500 font-black text-[9px] uppercase tracking-tighter">
                                            ❌ Non Acquis
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- CAS VIDE -->
            <div v-else class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-slate-100 shadow-sm">
                <span class="text-5xl opacity-20">🔎</span>
                <p class="mt-4 text-slate-400 font-bold italic uppercase text-xs tracking-widest">Aucune donnée disponible pour ce semestre</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
