<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    students: Array,
    availableUsers: Array,
    subjects: Array,
    stats: Object,
    filters: Object,
    classes: Array
});

const activeTab = ref('student');

// --- LOGIQUE ÉTUDIANT ---
const isEditing = ref(false);
const currentStudentId = ref(null);
const search = ref(props.filters.search || '');
const studentForm = useForm({ matricule: '', nom: '', prenom: '', classe_id: '' });

const submitStudent = () => {
    const action = isEditing.value ? 'patch' : 'post';
    const url = isEditing.value ? route('students.update', currentStudentId.value) : route('students.store');
    studentForm[action](url, { onSuccess: () => { isEditing.value = false; studentForm.reset(); } });
};

const editStudent = (student) => {
    activeTab.value = 'student';
    isEditing.value = true;
    currentStudentId.value = student.id;
    studentForm.matricule = student.matricule;
    studentForm.nom = student.nom;
    studentForm.prenom = student.prenom;
    studentForm.classe_id = student.classe_id;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const deleteStudent = (id) => {
    if (confirm('⚠️ Supprimer cet étudiant et ses notes ?')) {
        router.delete(route('students.destroy', id), { preserveScroll: true });
    }
};

const linkAccount = (studentId, userId) => {
    if(userId && confirm('Lier ce compte utilisateur à cet étudiant ?')) {
        router.patch(route('students.linkUser', studentId), { user_id: userId }, {
            preserveScroll: true,
            onSuccess: () => alert('Compte lié avec succès !')
        });
    }
};

// --- LOGIQUE NOTES ---
const gradeForm = useForm({
    student_id: '', subject_id: '', note1: '', note2: '',
    note_composition: '', note_rattrapage: '', semestre: 'Semestre 1'
});

const shouldShowRattrapage = computed(() => {
    const n1 = parseFloat(gradeForm.note1) || 0;
    const n2 = parseFloat(gradeForm.note2) || 0;
    const comp = parseFloat(gradeForm.note_composition) || 0;
    const moyenneActuelle = (((n1 + n2) / 2) + comp) / 2;
    return moyenneActuelle > 0 && moyenneActuelle < 10;
});

const submitGrade = () => gradeForm.post(route('grades.store'), { onSuccess: () => gradeForm.reset() });

// --- LOGIQUE CONFIG ---
const subjectForm = useForm({ name: '', ue_code: '', ue_nom: '', credits: 2 });
// const submitSubject = () => subjectForm.post(route('subjects.store'), { onSuccess: () => subjectForm.reset() });

const classForm = useForm({ nom_classe: '', niveau: '',subject_ids: []  });
const submitClass = () => classForm.post(route('classes.store'), { onSuccess: () => classForm.reset() });

// --- FILTRES ---
watch(search, (val) => {
    router.get(route('dashboard'), { search: val }, { preserveState: true, preserveScroll: true, replace: true });
});

const downloadBulletin = (id, sem = null) => {
    window.open(route('students.bulletin', id) + (sem ? `?semestre=${sem}` : ''), '_blank');
};
const sortByMoyenne = (order) => {
    router.get(route('dashboard'), {
        search: search.value,
        sort: order
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const openEditSubject = (s) => {
    editingSubjectId.value = s.id;
    subjectForm.name = s.name;
    subjectForm.ue_code = s.ue_code;
    subjectForm.ue_nom = s.ue_nom;
    subjectForm.credits = s.credits;
    subjectForm.coefficient = s.coefficient;
    // On scrolle vers le haut pour voir le formulaire
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const submitSubject = () => {
    if (editingSubjectId.value) {
        // Mode Modification
        subjectForm.put(route('subjects.update', editingSubjectId.value), {
            onSuccess: () => {
                subjectForm.reset();
                editingSubjectId.value = null;
            }
        });
    } else {
        // Mode Ajout
        subjectForm.post(route('subjects.store'), {
            onSuccess: () => subjectForm.reset()
        });
    }
};

const downloadAnnualBulletin = (id) => {
    // Cela ouvre le PDF dans un nouvel onglet et lance le téléchargement
    window.open(route('students.annual-bulletin', id), '_blank');
};

</script>

<template>
    <Head title="Dashboard Scolaire" />
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto p-6 space-y-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- TOTAL ÉTUDIANTS -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border-b-4 border-indigo-500 flex items-center gap-4 transition hover:shadow-md">
        <div class="h-12 w-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-2xl">👥</div>
        <div>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Effectif Total</p>
            <p class="text-2xl font-black text-slate-800">{{ stats.total }}</p>
            <p class="text-[8px] text-slate-400 font-bold italic">Inscrits sur la plateforme</p>
        </div>
    </div>

    <!-- MOYENNE ÉCOLE -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border-b-4 border-emerald-500 flex items-center gap-4 transition hover:shadow-md">
        <div class="h-12 w-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-2xl">📈</div>
        <div>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Performance Globale</p>
            <p class="text-2xl font-black text-emerald-600">{{ stats.moyenne_generale }}<span class="text-sm">/20</span></p>
            <p class="text-[8px] text-emerald-400 font-bold italic">Moyenne pondérée annuelle</p>
        </div>
    </div>

    <!-- ALERTES ETUDIANTS -->
    <div class="bg-white p-6 rounded-3xl shadow-sm border-b-4 border-rose-500 flex items-center gap-4 transition hover:shadow-md">
        <div class="h-12 w-12 bg-rose-50 rounded-2xl flex items-center justify-center text-2xl">⚠️</div>
        <div>
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Suivi des Risques</p>
            <p class="text-2xl font-black text-rose-600">{{ stats.eleves_a_risque }}</p>
            <p class="text-[8px] text-rose-400 font-bold italic">Moyennes < 10.00 (Rattrapages)</p>
        </div>
    </div>
</div>


            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- COLONNE GAUCHE -->
               <div class="lg:col-span-4 space-y-4">
    <!-- NAVIGATION ONGLET LMD -->
    <div class="bg-white p-2 rounded-2xl shadow-sm flex justify-between border border-gray-100 ring-1 ring-slate-50">
        <button @click="activeTab = 'student'" :class="activeTab === 'student' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="flex-1 px-3 py-2 rounded-xl text-[9px] font-black uppercase transition-all tracking-widest">Scolarité</button>
        <button @click="activeTab = 'grade'" :class="activeTab === 'grade' ? 'bg-emerald-600 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="flex-1 px-3 py-2 rounded-xl text-[9px] font-black uppercase transition-all tracking-widest mx-1">Notes</button>
        <button @click="activeTab = 'config'" :class="activeTab === 'config' ? 'bg-amber-500 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="flex-1 px-3 py-2 rounded-xl text-[9px] font-black uppercase transition-all tracking-widest">Config</button>
    </div>

    <!-- ONGLET 1 : INSCRIPTION & PROFIL -->
    <div v-if="activeTab === 'student'" class="bg-white p-6 rounded-3xl shadow-sm border border-indigo-50 animate-in fade-in duration-300">
        <h3 class="font-black text-indigo-700 uppercase text-xs mb-4 italic flex items-center gap-2">
            <span class="p-1.5 bg-indigo-50 rounded-lg text-indigo-500 text-sm">👤</span>
            {{ isEditing ? 'Mise à jour Profil' : 'Inscription LMD' }}
        </h3>
        <form @submit.prevent="submitStudent" class="space-y-3">
            <input v-model="studentForm.matricule" placeholder="N° Matricule (ex: 2024-L3I)" class="w-full rounded-xl border-gray-100 bg-slate-50 text-sm focus:ring-indigo-500">
            <input v-model="studentForm.nom" placeholder="Nom de l'étudiant" class="w-full rounded-xl border-gray-100 bg-slate-50 text-sm uppercase">
            <input v-model="studentForm.prenom" placeholder="Prénom" class="w-full rounded-xl border-gray-100 bg-slate-50 text-sm capitalize">
            <select v-model="studentForm.classe_id" class="w-full rounded-xl border-gray-100 bg-slate-50 text-sm font-bold text-slate-500">
                <option value="">Affectation Classe</option>
                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.nom_classe }}</option>
            </select>
            <button type="submit" class="w-full bg-indigo-600 text-white py-3.5 rounded-2xl font-black uppercase text-[10px] shadow-lg shadow-indigo-100 transition hover:bg-indigo-700">
                {{ isEditing ? '💾 Sauvegarder les modifications' : '✅ Valider l\'inscription' }}
            </button>
        </form>
    </div>

    <!-- ONGLET 2 : SAISIE DES NOTES (MOTEUR LMD) -->
    <div v-if="activeTab === 'grade'" class="bg-white p-6 rounded-3xl shadow-sm border border-emerald-50 animate-in fade-in duration-300">
        <h3 class="font-black text-emerald-700 uppercase text-xs mb-4 italic flex items-center gap-2">
            <span class="p-1.5 bg-emerald-50 rounded-lg text-emerald-500 text-sm">🎯</span>
            Saisie des Performances
        </h3>
        <form @submit.prevent="submitGrade" class="space-y-4">
            <select v-model="gradeForm.student_id" class="w-full rounded-xl border-gray-100 bg-slate-50 text-xs font-bold">
                <option value="">Sélectionner l'étudiant</option>
                <option v-for="s in students" :key="s.id" :value="s.id">{{ s.nom }} {{ s.prenom }}</option>
            </select>

            <select v-model="gradeForm.subject_id" class="w-full rounded-xl border-gray-100 bg-slate-50 text-xs font-bold">
                <option value="">Sélectionner EC (Élément Constitutif)</option>
                <option v-for="sub in subjects" :key="sub.id" :value="sub.id">{{ sub.name }}</option>
            </select>

         <div class="p-4 bg-emerald-50/50 rounded-3xl border border-emerald-100 space-y-4">
    <!-- Header de la section Saisie -->
    <div class="flex justify-between items-center px-1">
        <span class="text-[9px] font-black uppercase text-emerald-600 tracking-widest italic">Calcul des Performances</span>
        <div class="bg-white px-3 py-1 rounded-full border border-emerald-100 shadow-sm">
            <select v-model="gradeForm.semestre" class="border-none bg-transparent text-[10px] font-black text-emerald-700 uppercase p-0 focus:ring-0 cursor-pointer">
                <option value="Semestre 1">S1</option>
                <option value="Semestre 2">S2</option>
            </select>
        </div>
    </div>

    <!-- Grille de saisie des 3 Notes -->
    <div class="grid grid-cols-2 gap-3">
        <!-- Bloc Contrôle Continu (Note 1 & Note 2) -->
        <div class="col-span-2 grid grid-cols-2 gap-2 p-3 bg-white/50 rounded-2xl border border-emerald-50">
            <div class="space-y-1">
                <label class="text-[8px] font-black text-slate-400 uppercase ml-2 italic">Note CC 1</label>
                <input v-model="gradeForm.note1" type="number" step="0.01" placeholder="0.00"
                    class="w-full rounded-xl border-slate-100 bg-white text-sm text-center font-bold text-slate-700 shadow-sm focus:ring-emerald-500">
            </div>
            <div class="space-y-1">
                <label class="text-[8px] font-black text-slate-400 uppercase ml-2 italic">Note CC 2</label>
                <input v-model="gradeForm.note2" type="number" step="0.01" placeholder="0.00"
                    class="w-full rounded-xl border-slate-100 bg-white text-sm text-center font-bold text-slate-700 shadow-sm focus:ring-emerald-500">
            </div>
            <p class="col-span-2 text-[7px] text-center text-slate-400 uppercase font-bold tracking-tighter">La moyenne CC sera calculée automatiquement</p>
        </div>

        <!-- Bloc Examen Final (Composition) -->
        <div class="col-span-2 space-y-1 mt-1">
            <label class="text-[8px] font-black text-indigo-500 uppercase ml-3 tracking-widest">Examen Final (Composition)</label>
            <div class="relative">
                <input v-model="gradeForm.note_composition" type="number" step="0.01" placeholder="Note d'examen"
                    class="w-full rounded-2xl border-indigo-200 bg-indigo-50/50 py-3 text-md text-center font-black text-indigo-700 shadow-inner focus:ring-indigo-500">
                <span class="absolute right-4 top-3 opacity-20 text-lg">📝</span>
            </div>
        </div>
    </div>
</div>


            <!-- Alerte de Rattrapage Dynamique -->
            <div v-if="shouldShowRattrapage" class="p-4 bg-amber-50 rounded-2xl border-2 border-dashed border-amber-200 animate-bounce-short">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-lg">⚠️</span>
                    <p class="text-[9px] font-black text-amber-700 uppercase">Échec détecté - Session de rattrapage</p>
                </div>
                <input v-model="gradeForm.note_rattrapage" type="number" step="0.01" placeholder="Note de rattrapage" class="w-full rounded-xl border-amber-200 bg-white text-sm font-black text-amber-700">
            </div>

            <button type="submit" class="w-full bg-emerald-600 text-white py-3.5 rounded-2xl font-black uppercase text-[10px] shadow-lg shadow-emerald-100 transition hover:bg-emerald-700">
                🚀 Synchroniser les résultats
            </button>
        </form>
    </div>

    <!-- ONGLET 3 : CONFIGURATION STRUCTURELLE -->
    <div v-if="activeTab === 'config'" class="space-y-4 animate-in fade-in duration-300">
        <!-- FORM MATIERE -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-amber-50">
            <h3 class="font-black text-amber-600 uppercase text-xs mb-4 italic flex items-center gap-2">
                <span class="p-1.5 bg-amber-50 rounded-lg text-amber-500 text-sm">📚</span>
                Référentiel Matière (EC)
            </h3>
            <form @submit.prevent="submitSubject" class="space-y-3">
                <input v-model="subjectForm.name" placeholder="Nom de la matière" class="w-full rounded-xl border-gray-100 bg-slate-50 text-sm">

                <div class="grid grid-cols-3 gap-2">
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-slate-400 uppercase ml-1">Code UE</label>
                        <input v-model="subjectForm.ue_code" class="w-full rounded-xl border-gray-100 bg-slate-50 text-xs p-2 text-center">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-emerald-500 uppercase ml-1">Crédits</label>
                        <input v-model="subjectForm.credits" type="number" class="w-full rounded-xl border-emerald-100 bg-emerald-50 text-xs p-2 text-center font-black text-emerald-700">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[8px] font-black text-amber-500 uppercase ml-1">Poids (Coef)</label>
                        <input v-model="subjectForm.coefficient" type="number" class="w-full rounded-xl border-amber-100 bg-amber-50 text-xs p-2 text-center font-black text-amber-600">
                    </div>
                </div>
                <input v-model="subjectForm.ue_nom" placeholder="Nom de l'Unité d'Enseignement rattachée" class="w-full rounded-xl border-gray-100 bg-slate-50 text-[10px]">

                <button type="submit" :class="editingSubjectId ? 'bg-indigo-600' : 'bg-amber-500'" class="w-full text-white py-3 rounded-2xl font-black uppercase text-[10px] transition shadow-md">
                    {{ editingSubjectId ? '💾 Mettre à jour l\'EC' : '➕ Ajouter au programme' }}
                </button>
            </form>
        </div>

        <!-- FORM CLASSE -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
            <h3 class="font-black text-indigo-600 uppercase text-xs mb-4 italic flex items-center gap-2">
                <span class="p-1.5 bg-indigo-50 rounded-lg text-indigo-500 text-sm">🏫</span>
                Définition de Classe
            </h3>
            <form @submit.prevent="submitClass" class="space-y-3">
                <input v-model="classForm.nom_classe" placeholder="Libellé de la classe" class="w-full rounded-xl border-gray-100 bg-slate-50 text-sm">
                <select v-model="classForm.niveau" class="w-full rounded-xl border-gray-100 bg-slate-50 text-sm font-bold text-slate-500">
                    <option value="">Niveau académique</option>
                    <option value="Licence 1">Licence 1</option>
                    <option value="Licence 2">Licence 2</option>
                    <option value="Licence 3">Licence 3</option>
                    <option value="Master 1">Master 1</option>
                    <option value="Master 2">Master 2</option>
                </select>
                <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-2xl font-black uppercase text-[10px] hover:bg-indigo-600 transition shadow-lg">
                    🏗️ Créer la structure
                </button>
            </form>
        </div>
    </div>
</div>


                <!-- TABLEAU DROITE -->
             <div class="lg:col-span-8 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- BARRE DE RECHERCHE ET TRI -->
    <div class="p-6 border-b border-gray-50 flex flex-col md:flex-row justify-between items-center gap-4 bg-gray-50/20">
        <div class="relative w-full md:w-72">
            <span class="absolute left-4 top-2.5 text-gray-400">🔍</span>
            <input v-model="search" type="text" placeholder="Rechercher un étudiant..."
                   class="w-full rounded-full border-gray-100 bg-white pl-10 pr-6 py-2 text-sm shadow-sm focus:ring-2 focus:ring-indigo-100 transition-all italic">
        </div>

        <div class="flex items-center gap-2">
            <span class="text-[10px] font-black text-gray-400 uppercase mr-2 tracking-tighter">Trier par mérite :</span>
            <button @click="sortByMoyenne('avg_desc')"
                    class="flex items-center gap-1 bg-white border border-emerald-100 text-emerald-600 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase shadow-sm hover:bg-emerald-50 transition-all">
                <span>📈</span> Top
            </button>
            <button @click="sortByMoyenne(null)"
                    class="flex items-center gap-1 bg-white border border-slate-100 text-slate-400 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase shadow-sm hover:bg-slate-50 transition-all">
                <span>🔄</span> Reset
            </button>
        </div>
    </div>

    <!-- TABLEAU DES DONNÉES -->
    <div class="overflow-x-auto p-4">
        <table class="w-full text-left border-separate border-spacing-y-3">
            <thead>
                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-5">
                    <th class="pb-2 pl-5">Étudiant / Matricule</th>
                    <th class="pb-2 text-center">Parcours (LMD)</th>
                    <th class="pb-2 text-center">Accès Système</th>
                    <th class="pb-2 text-center">Bulletins</th>
                    <th class="pb-2 text-right pr-5">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="student in students" :key="student.id" class="group bg-white hover:bg-indigo-50/40 transition-all shadow-sm rounded-3xl">
                    <!-- IDENTITÉ -->
                    <td class="p-4 rounded-l-3xl border-y border-l border-slate-50">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 text-white flex items-center justify-center font-black text-xs shadow-md ring-2 ring-white">
                                {{ student.nom.charAt(0) }}
                            </div>
                            <div class="flex flex-col min-w-0">
                                <span class="text-xs font-black text-slate-700 uppercase truncate">{{ student.nom }} {{ student.prenom }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-bold text-slate-400 italic">{{ student.matricule }}</span>
                                    <span class="h-1 w-1 bg-slate-200 rounded-full"></span>
                                    <span class="text-[9px] font-black text-indigo-500 uppercase">{{ student.classe?.nom_classe || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- STATS LMD DYNAMIQUES -->
                  <td class="p-4 border-y border-slate-50">
    <div class="flex items-center justify-center gap-3 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm max-w-fit mx-auto group hover:border-indigo-100 transition-all">

        <!-- Moyenne Annuelle : Plus lisible -->
        <div class="text-center px-3 border-r border-slate-100">
            <p class="text-[7px] font-bold text-slate-400 uppercase tracking-wider">Annuel</p>
            <p :class="parseFloat(student.average) >= 10 ? 'text-emerald-600' : 'text-rose-500'"
               class="text-sm font-black tracking-tight">
                {{ student.average || '0.00' }}
            </p>
        </div>

        <!-- Détails Semestres : Couleurs dynamiques -->
        <div class="flex gap-4 px-1">
            <!-- Semestre 1 -->
            <div class="text-center">
                <p class="text-[7px] font-bold text-slate-400 uppercase">S1</p>
                <p class="text-[10px] font-bold text-slate-700">{{ student.detail_s1?.avg || '0.00' }}</p>
                <p :class="student.detail_s1?.credits >= 30 ? 'text-emerald-500' : 'text-slate-400'"
                   class="text-[9px] font-black">
                    {{ student.detail_s1?.credits || 0 }}<span class="text-[7px] ml-0.5">ECTS</span>
                </p>
            </div>

            <!-- Semestre 2 -->
            <div class="text-center border-l border-slate-100 pl-4">
                <p class="text-[7px] font-bold text-slate-400 uppercase">S2</p>
                <p class="text-[10px] font-bold text-slate-700">{{ student.detail_s2?.avg || '0.00' }}</p>
                <p :class="student.detail_s2?.credits >= 30 ? 'text-emerald-500' : 'text-slate-400'"
                   class="text-[9px] font-black">
                    {{ student.detail_s2?.credits || 0 }}<span class="text-[7px] ml-0.5">ECTS</span>
                </p>
            </div>
        </div>

        <!-- Badge Crédits Totaux : Design "Capsule" -->
        <div :class="[
                student.credits >= 60 ? 'bg-emerald-600' :
                (student.credits >= 42 ? 'bg-amber-500' : 'bg-slate-800')
             ]"
             class="px-3 py-1.5 rounded-xl shadow-sm transition-all duration-300 transform group-hover:scale-105 min-w-[50px]">
            <p class="text-[6px] font-black text-white/80 uppercase text-center leading-none mb-1">Total</p>
            <p class="text-[11px] font-black text-white text-center leading-none">
                {{ student.credits }}<span class="text-[8px] opacity-60">/60</span>
            </p>
        </div>
    </div>
</td>


                    <!-- ACCÈS ÉTUDIANT (LIAISON COMPTE) -->
                    <td class="p-4 text-center border-y border-slate-50">
                        <div v-if="!student.user_id" class="flex justify-center">
                            <select @change="linkAccount(student.id, $event.target.value)"
                                    class="text-[9px] font-black border-rose-100 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl py-1.5 px-3 uppercase w-32 shadow-sm transition-all cursor-pointer">
                                <option value="">Lier compte</option>
                                <option v-for="user in availableUsers" :key="user.id" :value="user.id">{{ user.email }}</option>
                            </select>
                        </div>
                        <div v-else class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-xl border border-emerald-100">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            <span class="text-[9px] font-black uppercase tracking-wider">Actif</span>
                        </div>
                    </td>

                    <!-- BULLETINS PDF -->
                  <!-- BULLETINS PDF -->
<td class="p-4 text-center border-y border-slate-50">
    <div class="flex justify-center gap-2">
        <!-- Bouton S1 -->
        <button
            @click="student.detail_s1?.avg > 0 ? downloadBulletin(student.id, 1) : null"
            :class="[
                'h-8 w-8 flex items-center justify-center rounded-xl transition-all shadow-sm',
                student.detail_s1?.avg > 0
                    ? 'bg-white border border-slate-100 hover:bg-indigo-600 hover:text-white cursor-pointer'
                    : 'bg-gray-50 border border-gray-100 text-gray-300 cursor-not-allowed opacity-50'
            ]"
            :title="student.detail_s1?.avg > 0 ? 'Bulletin Semestre 1' : 'Aucune note enregistrée en S1'"
        >
            <span class="text-[10px] font-black">S1</span>
        </button>

        <!-- Bouton S2 -->
        <button
            @click="student.detail_s2?.avg > 0 ? downloadBulletin(student.id, 2) : null"
            :class="[
                'h-8 w-8 flex items-center justify-center rounded-xl transition-all shadow-sm',
                student.detail_s2?.avg > 0
                    ? 'bg-white border border-slate-100 hover:bg-indigo-600 hover:text-white cursor-pointer'
                    : 'bg-gray-50 border border-gray-100 text-gray-300 cursor-not-allowed opacity-50'
            ]"
            :title="student.detail_s2?.avg > 0 ? 'Bulletin Semestre 2' : 'Aucune note enregistrée en S2'"
        >
            <span class="text-[10px] font-black">S2</span>
        </button>

        <button @click="downloadAnnualBulletin(student.id)"
        class="h-8 px-2 flex items-center justify-center bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-500 hover:text-white transition-all shadow-sm"
        title="Bulletin Annuel">
    <span class="text-[9px] font-black">ANNUEL</span>
</button>

    </div>
</td>


                    <!-- ACTIONS -->
                    <td class="p-4 text-right rounded-r-3xl border-y border-r border-slate-50">
                        <div class="flex justify-end items-center gap-1 opacity-40 group-hover:opacity-100 transition-opacity">
                            <button @click="editStudent(student)" class="p-2 text-slate-400 hover:text-indigo-600 transition-all" title="Modifier le profil">
                                ✏️
                            </button>
                            <button @click="deleteItem('students', student.id)" class="p-2 text-slate-400 hover:text-rose-600 transition-all" title="Supprimer">
                                🗑️
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
