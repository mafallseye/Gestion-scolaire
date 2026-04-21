<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

// 1. État de la sidebar (Réduite ou non)
const isCollapsed = ref(false);

// 2. Calcul automatique de l'année scolaire (Session)
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth();
const academicSession = computed(() => {
    return currentMonth >= 8
        ? `${currentYear} - ${currentYear + 1}`
        : `${currentYear - 1} - ${currentYear}`;
});

// Récupérer l'utilisateur connecté
const user = computed(() => usePage().props.auth.user);
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex font-sans overflow-hidden">

        <!-- SIDEBAR DYNAMIQUE -->
        <aside
            :class="isCollapsed ? 'w-20' : 'w-72'"
            class="bg-slate-900 text-white flex-shrink-0 hidden md:flex flex-col shadow-2xl border-r border-slate-800 transition-all duration-300 ease-in-out relative"
        >
            <!-- BOUTON COLLAPSE (Petit bouton rond sur le bord) -->
            <button
                @click="isCollapsed = !isCollapsed"
                class="absolute -right-3 top-24 bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center border-2 border-slate-900 z-50 hover:bg-indigo-500 transition-all shadow-lg"
            >
                <span class="text-[10px] transform transition-transform" :class="isCollapsed ? 'rotate-180' : ''">◀</span>
            </button>

            <!-- LOGO -->
            <div class="p-6 flex items-center gap-3 border-b border-slate-800/50 overflow-hidden min-h-[80px]">
                <div class="bg-indigo-600 p-2.5 rounded-xl shadow-lg shadow-indigo-500/20 flex-shrink-0">
                    <span class="text-xl">🎓</span>
                </div>
                <span v-if="!isCollapsed" class="text-xl font-black tracking-tight text-white whitespace-nowrap">
                    Edu<span class="text-indigo-400">Manager</span>
                </span>
            </div>

            <!-- INFOS SESSION (Masquées si réduit) -->
            <div v-if="!isCollapsed" class="px-6 py-6 border-b border-slate-800/30 bg-slate-800/20">
                <p class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mb-1">Session Active</p>
                <div class="flex items-center gap-3">
                    <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-sm font-medium text-slate-300">{{ academicSession }}</span>
                </div>
            </div>

            <!-- NAVIGATION -->
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto custom-scrollbar">
                <p v-if="!isCollapsed" class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Menu</p>

                <!-- LIEN : TABLEAU DE BORD (Commun) -->
                <Link :href="route('dashboard')"
                    :class="route().current('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/50' : 'text-slate-400 hover:bg-slate-800'"
                    class="flex items-center gap-4 p-3 rounded-xl transition-all group"
                >
                    <span class="text-lg flex-shrink-0">📊</span>
                    <span v-if="!isCollapsed" class="text-sm font-semibold whitespace-nowrap">Tableau de Bord</span>
                </Link>

                <!-- SECTION ADMINISTRATEUR -->
                <div v-if="user.role === 'admin'" class="pt-4 space-y-1 border-t border-slate-800/50 mt-4">
                    <p v-if="!isCollapsed" class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Administration</p>

                    <Link :href="route('users.index')"
                        :class="route().current('users.index') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800'"
                        class="flex items-center gap-4 p-3 rounded-xl transition-all group">
                        <span class="text-lg flex-shrink-0">👥</span>
                        <span v-if="!isCollapsed" class="text-sm font-semibold whitespace-nowrap">Gérer Utilisateurs</span>
                    </Link>

                    <Link :href="route('academic.index')"
                        :class="route().current('academic.index') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800'"
                        class="flex items-center gap-4 p-3 rounded-xl transition-all group">
                        <span class="text-lg flex-shrink-0">🏫</span>
                        <span v-if="!isCollapsed" class="text-sm font-semibold whitespace-nowrap">Classes & Matières</span>
                    </Link>
                    <Link :href="route('admin.lessons.index')" :class="route().current('admin.lessons.index') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800'"
                        class="flex items-center gap-4 p-3 rounded-xl transition-all group">
                        <span class="text-lg flex-shrink-0">🏫</span>
                        <span v-if="!isCollapsed" class="text-sm font-semibold whitespace-nowrap"> Gestion des Cours</span>
                    </Link>

                    <Link :href="route('timetable.index')"
                        :class="route().current('timetable.index') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800'"
                        class="flex items-center gap-4 p-3 rounded-xl transition-all group">
                        <span class="text-lg flex-shrink-0">📅</span>
                        <span v-if="!isCollapsed" class="text-sm font-semibold whitespace-nowrap">Emplois du Temps</span>
                    </Link>
                </div>

                <!-- SECTION PÉDAGOGIE (ADMIN & TEACHER) -->
                <div v-if="user.role === 'admin' || user.role === 'teacher'" class="pt-4 space-y-1 border-t border-slate-800/50 mt-4">
                    <p v-if="!isCollapsed" class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Pédagogie</p>

                    <Link :href="route('grades.mass-entry')"
                        :class="route().current('grades.mass-entry') ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800'"
                        class="flex items-center gap-4 p-3 rounded-xl transition-all group">
                        <span class="text-lg flex-shrink-0">📝</span>
                        <span v-if="!isCollapsed" class="text-sm font-semibold whitespace-nowrap">Saisir les Notes</span>
                    </Link>
                </div>

                <!-- SECTION ÉTUDIANT -->
            <!-- SECTION ÉTUDIANT -->
<div v-if="user.role === 'student'" class="pt-4 space-y-1 border-t border-slate-800/50 mt-4">
    <p v-if="!isCollapsed" class="px-4 py-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Espace Personnel</p>

    <!-- LIEN MODIFIÉ : Pointe maintenant vers ton relevé détaillé -->
    <Link :href="route('student.grades')"
        :class="route().current('student.grades') ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-900/50' : 'text-emerald-400 hover:bg-slate-800'"
        class="flex items-center gap-4 p-3 rounded-xl transition-all group">
        <span class="text-lg flex-shrink-0">📊</span>
        <span v-if="!isCollapsed" class="text-sm font-semibold whitespace-nowrap">Mon Relevé Détaillé</span>
    </Link>

    <Link :href="route('timetable.index')"
        :class="route().current('timetable.index') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-400 hover:bg-slate-800'"
        class="flex items-center gap-4 p-3 rounded-xl transition-all group">
        <span class="text-lg flex-shrink-0">🗓️</span>
        <span v-if="!isCollapsed" class="text-sm font-semibold whitespace-nowrap">Mon Emploi du Temps</span>
    </Link>
</div>

            </nav>

            <!-- FOOTER SIDEBAR -->
            <div class="p-4 border-t border-slate-800/50">
                <Link :href="route('logout')" method="post" as="button"
                    class="w-full flex items-center gap-4 p-3 text-rose-400 hover:bg-rose-500/10 rounded-xl transition-all font-bold">
                    <span class="text-lg flex-shrink-0">🚪</span>
                    <span v-if="!isCollapsed" class="text-sm whitespace-nowrap">Quitter la session</span>
                </Link>
            </div>
        </aside>

        <!-- CONTENU DROIT -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- TOPBAR -->
            <header class="h-20 bg-white border-b border-slate-100 flex items-center justify-between px-8 shadow-sm z-10">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Portail Académique Officiel</span>
                    <h1 class="text-sm font-bold text-slate-400 italic">République du Sénégal</h1>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-black text-slate-800 uppercase">{{ user.name }}</p>
                        <p class="text-[9px] uppercase font-bold text-indigo-500 px-2 py-0.5 rounded-lg bg-indigo-50 inline-block">
                            {{ user.role }}
                        </p>
                    </div>
                    <div class="h-10 w-10 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center font-black text-indigo-600 shadow-inner">
                        {{ user.name.charAt(0) }}
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT -->
            <main class="flex-1 overflow-y-auto bg-slate-50 p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
</style>
