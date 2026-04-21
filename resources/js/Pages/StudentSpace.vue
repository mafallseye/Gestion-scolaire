<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    student: Object,

});

const selectedSemestre = ref(1);

// 1. VERIFICATION SI LE SEMESTRE SELECTIONNÉ CONTIENT DES DONNÉES
const semestreActuelValide = computed(() => {
    const data = selectedSemestre.value === 1 ? props.student.detail_s1 : props.student.detail_s2;
    // On considère le semestre comme "existant" si la moyenne est supérieure à 0
    return data && parseFloat(data.avg) > 0;
});

// 2. LOGIQUE DYNAMIQUE POUR LE BLOC DE MOYENNE (HAUT)
const moyenneAffichée = computed(() => {
    // On vérifie si le S2 a une moyenne réelle (différente de 0.00)
    const hasS2 = props.student.detail_s2 && parseFloat(props.student.detail_s2.avg) > 0;

    if (hasS2) {
        // AFFICHE MOYENNE ANNUELLE SI S2 EXISTE
        return {
            valeur: props.student.average,
            label: 'Moyenne Annuelle (LMD)',
            info: 'Calculée sur S1 + S2'
        };
    } else {
        // AFFICHE S1 SI S2 EST VIDE
        return {
            valeur: props.student.detail_s1?.avg || '0.00',
            label: 'Moyenne Semestre 1',
            info: 'Semestre 2 non encore délibéré'
        };
    }
});

// Vérifie si le bloc de téléchargement doit apparaître pour le semestre sélectionné
// const semestreActuelValide = computed(() => {
//     const data = selectedSemestre.value === 1 ? props.student.detail_s1 : props.student.detail_s2;
//     return data && parseFloat(data.avg) > 0;
// });


const download = (semestre) => {
    if (!semestreActuelValide.value) return;
    window.location.href = `/students/${props.student.id}/bulletin?semestre=${semestre}`;
};

const viewDetails = () => {
    router.get(route('student.grades'), { semestre: 'Semestre ' + selectedSemestre.value });
};

const downloadAnnual = () => {
    // On appelle la route que nous avons créée précédemment
    window.location.href = route('students.annual-bulletin', props.student.id);
};
// 2. Calcul automatique de l'année scolaire (Session)
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth();
const academicSession = computed(() => {
    return currentMonth >= 8
        ? `${currentYear} - ${currentYear + 1}`
        : `${currentYear - 1} - ${currentYear}`;
});
</script>

<template>
    <Head title="Mon Espace Scolaire" />
    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto p-6 space-y-8">

            <!-- CAS : PROFIL NON LIÉ -->
            <div v-if="!student" class="bg-white p-16 rounded-[40px] shadow-sm border border-rose-100 text-center">
                <div class="text-7xl mb-6">🔒</div>
                <h1 class="text-3xl font-black text-slate-800 mb-4">Profil non activé</h1>
                <p class="text-slate-400 max-w-md mx-auto mb-8 font-medium text-sm">Votre compte n'est pas encore rattaché à une fiche étudiante. Contactez l'administration.</p>
            </div>

            <!-- CAS : ÉTUDIANT CONNECTÉ -->
            <div v-else class="space-y-8 animate-in fade-in duration-500">

                <!-- HEADER & STATUT -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Bonjour, {{ student.prenom }} !</h1>
                        <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.2em] mt-1 italic">
                            {{ student.classe?.nom_classe || 'Classe non définie' }} {{ academicSession }}
                        </p>
                    </div>

                    <div v-if="student.status" :class="{
                        'bg-emerald-500 shadow-emerald-100': student.status === 'Admis',
                        'bg-amber-500 shadow-amber-100': student.status === 'Passage Conditionnel',
                        'bg-slate-800 shadow-slate-200': ['Ajourné', 'Incomplet', 'Redoublant'].includes(student.status)
                    }" class="px-6 py-3 rounded-2xl text-white text-[10px] font-black uppercase tracking-widest shadow-xl">
                        Statut : {{ student.status }}
                    </div>
                </div>

                <!-- CARTES DE RÉSUMÉ DYNAMIQUES -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-indigo-600 p-8 rounded-[35px] text-white shadow-2xl shadow-indigo-100 relative overflow-hidden group">
                        <p class="text-[10px] font-black uppercase opacity-60 tracking-widest">{{ moyenneAffichée.label }}</p>
                        <p class="text-5xl font-black mt-2">{{ moyenneAffichée.valeur }} <span class="text-xl opacity-50">/ 20</span></p>
                        <p class="text-[9px] mt-4 font-bold bg-white/10 w-fit px-3 py-1 rounded-full">{{ moyenneAffichée.info }}</p>
                        <span class="absolute -right-4 -bottom-4 text-8xl opacity-10 transform group-hover:scale-110 transition-transform italic font-black">LMD</span>
                    </div>

                    <div class="bg-white p-8 rounded-[35px] border border-slate-100 shadow-sm relative overflow-hidden group">
                        <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Crédits ECTS Capitalisés</p>
                        <p class="text-5xl font-black mt-2 text-slate-800">{{ student.credits || '0' }} <span class="text-xl text-slate-200">/ 60</span></p>
                        <p class="text-[9px] mt-4 font-bold text-emerald-500 bg-emerald-50 w-fit px-3 py-1 rounded-full uppercase">
                            {{ student.credits >= 42 ? 'Seuil de passage validé' : 'Crédits insuffisants' }}
                        </p>
                        <span class="absolute -right-4 -bottom-4 text-8xl opacity-5 transform group-hover:scale-110 transition-transform">🎓</span>
                    </div>
                </div>

                <!-- NAVIGATION INTERNE : CHOIX DU SEMESTRE -->
               <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between">
    <div class="flex gap-2">
        <!-- Bouton S1 : Toujours visible ou selon tes notes -->
        <button @click="selectedSemestre = 1"
                :class="selectedSemestre === 1 ? 'bg-slate-900 text-white shadow-lg' : 'bg-slate-50 text-slate-400'"
                class="px-6 py-2 rounded-2xl text-[10px] font-black uppercase transition-all">
            S1
        </button>

        <!-- Bouton S2 : Ne s'affiche QUE s'il y a des notes en S2 -->
        <button v-if="student.detail_s2?.avg > 0"
                @click="selectedSemestre = 2"
                :class="selectedSemestre === 2 ? 'bg-slate-900 text-white shadow-lg' : 'bg-slate-50 text-slate-400'"
                class="px-6 py-2 rounded-2xl text-[10px] font-black uppercase transition-all">
            S2
        </button>

        <!-- Optionnel : Un bouton S2 grisé si tu préfères le montrer sans cliquer -->
        <button v-else
                class="px-6 py-2 rounded-2xl text-[10px] font-black uppercase bg-slate-50 text-slate-200 cursor-not-allowed"
                title="Notes S2 en cours de saisie">
            S2 (Bientôt)
        </button>
    </div>

    <button @click="viewDetails" class="text-[10px] font-black text-indigo-600 uppercase hover:underline mr-4">
        Détails des notes →
    </button>
</div>

<!-- BLOC BILAN ANNUEL : S'affiche uniquement si S1 et S2 ont des notes -->
<div v-if="student.detail_s1?.avg > 0 && student.detail_s2?.avg > 0"
     class="bg-gradient-to-r from-amber-400 to-orange-500 rounded-[35px] p-1 shadow-xl shadow-orange-100 animate-in fade-in zoom-in duration-700">
    <div class="bg-white rounded-[32px] p-8 flex flex-col md:flex-row justify-between items-center gap-6">
        <div class="flex items-center gap-6">
            <div class="h-16 w-16 bg-amber-500 rounded-[22px] flex items-center justify-center text-3xl shadow-lg shadow-amber-100">🏆</div>
            <div>
                <h3 class="text-xl font-black text-slate-800 uppercase italic tracking-tighter">Bilan Académique Annuel</h3>
                <p class="text-xs text-slate-400 font-medium">Récapitulatif officiel de l'année 2025-2026</p>
                <div class="flex gap-2 mt-2">
                    <span class="text-[8px] font-black bg-slate-100 px-2 py-0.5 rounded-full text-slate-600 uppercase">Moy : {{ student.average }}</span>
                    <span class="text-[8px] font-black bg-emerald-100 px-2 py-0.5 rounded-full text-emerald-600 uppercase">{{ student.credits }} Crédits</span>
                </div>
            </div>
        </div>

        <!-- Bouton de téléchargement annuel -->
        <button @click="downloadAnnual()"
                class="w-full md:w-fit bg-slate-900 text-white px-10 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl hover:scale-105 transition-all">
            Télécharger le Bilan Annuel
        </button>
    </div>
</div>

                <!-- AFFICHAGE CONDITIONNEL (BULLETIN OU MESSAGE VIDE) -->
                <div v-if="semestreActuelValide" class="bg-white rounded-[35px] border border-slate-100 shadow-sm overflow-hidden animate-in slide-in-from-bottom-4">
                    <div class="p-8 flex flex-col md:flex-row justify-between items-center gap-6">
                        <div class="flex items-center gap-6">
                            <div class="h-16 w-16 bg-slate-900 rounded-[22px] flex items-center justify-center text-3xl shadow-lg">📄</div>
                            <div>
                                <h3 class="text-xl font-black text-slate-800 uppercase italic tracking-tighter">Relevé de notes - Semestre {{ selectedSemestre }}</h3>
                                <p class="text-xs text-slate-400 font-medium">Document officiel généré dynamiquement</p>
                            </div>
                        </div>
                        <button @click="download(selectedSemestre)" class="w-full md:w-fit bg-indigo-600 text-white px-10 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl shadow-indigo-100 hover:bg-slate-900 transition-all">
                            Télécharger PDF
                        </button>
                    </div>
                </div>

                <div v-else class="text-center py-20 bg-white rounded-[35px] border-2 border-dashed border-slate-100 shadow-sm animate-in zoom-in-95">
                    <span class="text-5xl opacity-20">🔎</span>
                    <p class="mt-4 text-slate-400 font-bold italic uppercase text-xs tracking-widest">Aucune donnée disponible pour le semestre {{ selectedSemestre }}</p>
                    <p class="text-[9px] text-slate-300 uppercase font-black mt-2 tracking-tighter">Les résultats seront visibles après délibération du jury</p>
                </div>

                <!-- MODULE DE COURS -->
                <div class="space-y-4 pt-4">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] ml-2 italic">📚 Supports de cours rattachés</h3>
                    <div v-if="student.classe?.subjects" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="subject in student.classe.subjects" :key="subject.id" class="bg-white p-6 rounded-3xl border border-slate-100 group hover:border-indigo-200 transition-all">
                            <p class="text-[10px] font-black text-indigo-500 uppercase mb-3 tracking-tighter">{{ subject.name }}</p>
                            <div v-if="subject.lessons.length > 0" class="space-y-2">
                                <div v-for="lesson in subject.lessons" :key="lesson.id" class="flex justify-between items-center p-3 bg-slate-50 rounded-2xl">
                                    <span class="text-[11px] font-bold text-slate-700 truncate max-w-[150px]">{{ lesson.title }}</span>
                                    <a :href="'/storage/' + lesson.file_path" target="_blank" class="bg-white p-2 rounded-xl shadow-sm text-xs hover:bg-indigo-600 hover:text-white transition-all">⬇️</a>
                                </div>
                            </div>
                            <p v-else class="text-[10px] italic text-slate-300">Support bientôt disponible</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
