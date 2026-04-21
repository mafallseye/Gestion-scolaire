<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';// <--- Vérifie cet import !

const props = defineProps({ classes: Array, subjects: Array });

const classForm = useForm({ nom_classe: '', niveau: '',  subject_ids: []  });
const subjectForm = useForm({
    name: '',
    ue_code: '',
    ue_nom: '',
    credits: 2,
    coefficient: 1 // Garde le coefficient ici
});
const editingSubjectId = ref(null);

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




const submitClass = () => classForm.post(route('classes.store'), { onSuccess: () => classForm.reset() });
// const submitSubject = () => subjectForm.post(route('subjects.store'), { onSuccess: () => subjectForm.reset() });


const deleteClass = (id) => {
    if (confirm('Supprimer cette classe ? Cela supprimera aussi les élèves liés.')) {
        router.delete(route('classes.destroy', id), {
            preserveScroll: true,
        });
    }
};

const deleteSubject = (id) => {
    if (confirm('Supprimer cette matière ?')) {
        router.delete(route('subjects.destroy', id), {
            preserveScroll: true,
        });
    }
};

const editingClass = ref(null); // Stocke la classe en cours de modification

const editForm = useForm({
    subject_ids: []
});

const openEdit = (classe) => {
    editingClass.value = classe;
    // On pré-remplit avec les IDs des matières déjà liées à cette classe
    editForm.subject_ids = classe.subjects ? classe.subjects.map(s => s.id) : [];
};

const updateSubjects = () => {
    editForm.post(route('classes.update-subjects', editingClass.value.id), {
        onSuccess: () => editingClass.value = null
    });
};


</script>

<template>
    <Head title="Structure Académique" />
    <AuthenticatedLayout>
        <div class="space-y-8">
            <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Configuration Académique</h1>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- BLOC : GESTION DES CLASSES -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
                    <h2 class="font-bold text-indigo-600 mb-6 flex items-center gap-2 text-sm uppercase tracking-widest">
                        <span>🏫</span> Liste des Classes
                    </h2>

                    <form @submit.prevent="submitClass" class="grid grid-cols-2 gap-3 mb-8">
                        <input v-model="classForm.nom_classe" placeholder="Nom (ex: L3 Info)" class="rounded-xl border-gray-100 bg-gray-50 text-sm">
                                                     <select v-model="classForm.niveau" class="w-full rounded-xl border-gray-100 bg-gray-50 text-sm">
                                    <option value="">Niveau</option>
                                    <option value="Licence 1">Licence 1</option>
                                    <option value="Licence 2">Licence 2</option>
                                    <option value="Licence 3">Licence 3</option>
                                    <option value="Master 1">Master 1</option>
                                    <option value="Master 2">Master 2</option>
                                </select>
                                <!-- Dans ton formulaire submitClass, avant le bouton -->
<div class="col-span-2 mt-4">
    <label class="text-[10px] font-black uppercase text-slate-400 ml-1 mb-2 block">
        📚 Sélectionner les matières de cette classe
    </label>
    <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-3 bg-slate-50 rounded-xl border border-slate-100">
        <label v-for="s in subjects" :key="s.id" class="flex items-center gap-2 p-2 hover:bg-white rounded-lg transition cursor-pointer">
            <input
                type="checkbox"
                :value="s.id"
                v-model="classForm.subject_ids"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            >
            <span class="text-xs font-bold text-slate-600">{{ s.name }}</span>
        </label>
    </div>
</div>

<button class="col-span-2 bg-indigo-600 text-white py-3 rounded-xl font-bold text-xs uppercase shadow-lg shadow-indigo-100 mt-2">
    Créer la Classe
</button>


                        <!-- <button class="col-span-2 bg-indigo-600 text-white py-2 rounded-xl font-bold text-xs uppercase">Créer la Classe</button> -->
                    </form>

                    <!-- Dans la boucle des Classes -->
<div v-for="c in classes" :key="c.id" class="p-4 bg-slate-50 rounded-2xl flex justify-between items-center group">
    <div>
        <p class="font-black text-slate-700">{{ c.nom_classe }}</p>
        <p class="text-[10px] text-slate-400 font-bold uppercase">{{ c.niveau }}</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="bg-white px-3 py-1 rounded-full text-[10px] font-black shadow-sm text-indigo-500 border">
            {{ c.students_count }} ÉTUDIANT(S)
        </span>
        <button @click="deleteClass(c.id)" class="text-rose-500 font-bold ml-4">
    🗑️ Supprimer
</button>
    </div>
</div>
<!-- Dans ta boucle v-for des classes -->
<div v-for="c in classes" :key="c.id" class="p-4 bg-slate-50 rounded-2xl space-y-4">
    <div class="flex justify-between items-center">
        <div>
            <p class="font-black text-slate-700">{{ c.nom_classe }}</p>
            <p class="text-[10px] text-slate-400 font-bold uppercase">{{ c.niveau }}</p>
        </div>
        <div class="flex gap-2">
            <button @click="openEdit(c)" class="text-[10px] font-black uppercase text-indigo-600 bg-white px-3 py-1 rounded-lg border shadow-sm">
                ⚙️ Modifier UE
            </button>
            <button @click="deleteClass(c.id)" class="text-rose-500 text-xs">🗑️</button>
        </div>
    </div>

    <!-- Petit formulaire de mise à jour qui apparaît si on clique sur Modifier -->
    <div v-if="editingClass && editingClass.id === c.id" class="bg-white p-4 rounded-xl border border-indigo-100 shadow-inner">
        <p class="text-[10px] font-black uppercase text-indigo-400 mb-2">Mises à jour des matières :</p>
        <div class="grid grid-cols-2 gap-2 mb-4">
            <label v-for="s in subjects" :key="s.id" class="flex items-center gap-2 text-xs">
                <input type="checkbox" :value="s.id" v-model="editForm.subject_ids" class="rounded text-indigo-600">
                {{ s.name }}
            </label>
        </div>
        <div class="flex gap-2">
            <button @click="updateSubjects" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-[10px] font-black">VALIDER</button>
            <button @click="editingClass = null" class="text-slate-400 text-[10px] font-black">ANNULER</button>
        </div>
    </div>
</div>


                </div>

                <!-- BLOC : GESTION DES MATIÈRES -->
              <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
    <h3 class="font-black text-amber-600 uppercase text-xs mb-3 italic">
        {{ editingSubjectId ? '📝 Modifier la matière' : '📚 Nouvelle Matière LMD' }}
    </h3>

  <form @submit.prevent="submitSubject" class="space-y-2 mb-6">
    <input v-model="subjectForm.name" placeholder="Nom de l'élément constitutif (EC)" class="w-full rounded-xl border-gray-100 bg-gray-50 text-sm">

    <div class="grid grid-cols-3 gap-2">
        <!-- Champ Code UE -->
        <div class="flex flex-col gap-1">
            <label class="text-[9px] font-black uppercase text-slate-400 ml-1 italic">Code UE</label>
            <input v-model="subjectForm.ue_code" placeholder="Code" class="rounded-xl border-gray-100 bg-gray-50 text-xs p-2">
        </div>

        <!-- Champ Crédits (LMD) -->
        <div class="flex flex-col gap-1">
            <label class="text-[9px] font-black uppercase text-emerald-500 ml-1 italic">Crédits (LMD)</label>
            <input v-model="subjectForm.credits" type="number" placeholder="Crédits"
                class="rounded-xl border-emerald-100 bg-emerald-50 text-xs p-2 text-center font-bold text-emerald-700">
        </div>

        <!-- Champ Coefficient (Moyenne) -->
        <div class="flex flex-col gap-1">
            <label class="text-[9px] font-black uppercase text-amber-500 ml-1 italic">Coeff (Moyenne)</label>
            <input v-model="subjectForm.coefficient" type="number" placeholder="Coeff"
                class="rounded-xl border-amber-100 bg-amber-50 text-xs p-2 text-center font-bold text-amber-600">
        </div>
    </div>

    <input v-model="subjectForm.ue_nom" placeholder="Nom de l'Unité d'Enseignement (UE)" class="w-full rounded-xl border-gray-100 bg-gray-50 text-sm">

    <div class="flex gap-2">
        <button type="submit"
            :class="editingSubjectId ? 'bg-indigo-600' : 'bg-amber-500'"
            class="w-full text-white py-2 rounded-xl font-black uppercase text-[10px] transition">
            {{ editingSubjectId ? '💾 Enregistrer les modifications' : '➕ Ajouter la matière' }}
        </button>
        <button v-if="editingSubjectId" @click="editingSubjectId = null; subjectForm.reset()" type="button" class="bg-slate-200 text-slate-600 px-4 py-2 rounded-xl font-black uppercase text-[10px]">
            Annuler
        </button>
    </div>
</form>


    <div class="space-y-3">
        <div v-for="s in subjects" :key="s.id" class="p-4 bg-orange-50/30 rounded-2xl flex justify-between items-center border border-transparent hover:border-orange-100 transition text-sm">
            <div class="flex flex-col">
                <span class="font-bold text-slate-700">{{ s.name }}</span>
                <span class="text-[9px] text-slate-400 font-black uppercase">{{ s.ue_code }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-black text-orange-600 bg-white px-3 py-1 rounded-lg border text-[10px]">COEFF: {{ s.coefficient }}</span>
                <span class="font-black text-orange-600 bg-white px-3 py-1 rounded-lg border text-[10px]">CREDITS: {{ s.credits }}</span>

                <!-- BOUTON MODIFIER -->
                <button @click="openEditSubject(s)" class="text-indigo-500 hover:text-indigo-700 transition ml-2">
                    ✏️
                </button>

                <!-- BOUTON SUPPRIMER -->
                <button @click="deleteSubject(s.id)" class="text-rose-500 hover:text-rose-700 transition ml-2">
                    🗑️
                </button>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
