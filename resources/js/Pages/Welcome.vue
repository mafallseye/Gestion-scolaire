<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Accueil - EduManager" />

    <div class="min-h-screen bg-slate-50 font-sans selection:bg-indigo-500 selection:text-white">
        <!-- Barre de Navigation -->
        <nav class="flex justify-between items-center p-8 max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-600 p-2 rounded-xl shadow-lg shadow-indigo-200">
                    <span class="text-xl">🎓</span>
                </div>
                <span class="text-xl font-black tracking-tight text-slate-800">Edu<span class="text-indigo-600">Manager</span></span>
            </div>

            <div v-if="canLogin" class="space-x-4">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')"
                    class="font-black text-sm uppercase text-indigo-600 hover:text-indigo-800 transition">
                    Accéder au Dashboard →
                </Link>

                <template v-else>
                    <Link :href="route('login')" class="font-bold text-sm text-slate-500 hover:text-slate-800">Connexion</Link>
                    <Link v-if="canRegister" :href="route('register')"
                        class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-indigo-600 shadow-xl shadow-slate-200 transition-all">
                        S'inscrire
                    </Link>
                </template>
            </div>
        </nav>

        <!-- Section Hero (Le titre principal) -->
        <main class="max-w-5xl mx-auto mt-16 text-center px-6">
            <div class="inline-block px-4 py-1.5 mb-6 rounded-full bg-indigo-50 border border-indigo-100">
                <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 italic">Plateforme de gestion académique LMD</span>
            </div>

            <h1 class="text-5xl md:text-7xl font-black text-slate-900 leading-tight mb-8">
                Gérez votre établissement <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-emerald-500">avec intelligence.</span>
            </h1>

            <p class="text-slate-500 text-lg md:text-xl max-w-2xl mx-auto font-medium leading-relaxed mb-12">
                Suivi des notes, calcul automatique des moyennes LMD, gestion des emplois du temps et édition des bulletins officiels en quelques clics.
            </p>

            <!-- Boutons d'Action -->
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <Link :href="route('login')" class="bg-indigo-600 text-white px-10 py-5 rounded-3xl font-black uppercase text-xs tracking-widest hover:bg-indigo-700 shadow-2xl shadow-indigo-100 transition-all transform hover:-translate-y-1">
                    Lancer l'application
                </Link>
                <a href="#features" class="bg-white text-slate-800 border border-slate-200 px-10 py-5 rounded-3xl font-black uppercase text-xs tracking-widest hover:bg-slate-50 transition-all">
                    Voir les fonctionnalités
                </a>
            </div>

            <!-- Aperçu Dashboard -->
            <div class="mt-20 relative">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-50 via-transparent to-transparent z-10"></div>
                <div class="bg-white p-4 rounded-[40px] shadow-2xl border border-slate-100 transform -rotate-1">
                    <div class="bg-slate-900 rounded-[32px] overflow-hidden aspect-video flex items-center justify-center border-4 border-slate-800">
                         <!-- Tu pourras mettre une capture d'écran de ton dashboard ici -->
                         <!-- <p class="text-slate-500 italic font-black text-sm uppercase opacity-30">Aperçu du Dashboard EduManager</p> -->
                         <img
            src="/images/Dashboard Scolaire.png"
            alt="Aperçu du Dashboard EduManager"
            class="w-full h-full object-cover opacity-90 hover:opacity-100 transition-opacity duration-300"
         />
                        </div>
                </div>
            </div>
        </main>

        <!-- Section des Fonctionnalités -->
        <section id="features" class="max-w-7xl mx-auto py-32 px-12 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-10 rounded-[40px] shadow-sm border border-slate-100 hover:shadow-xl transition-all">
                <div class="text-3xl mb-4">📊</div>
                <h3 class="font-black text-slate-800 uppercase text-xs mb-3 italic">Calcul LMD Automatisé</h3>
                <p class="text-slate-500 text-sm leading-relaxed font-medium">Gestion intelligente des crédits (ECTS), des compensations semestrielles et des moyennes par UE.</p>
            </div>
            <div class="bg-white p-10 rounded-[40px] shadow-sm border border-slate-100 hover:shadow-xl transition-all">
                <div class="text-3xl mb-4">📅</div>
                <h3 class="font-black text-slate-800 uppercase text-xs mb-3 italic">Plannings Dynamiques</h3>
                <p class="text-slate-500 text-sm leading-relaxed font-medium">Emplois du temps interactifs par classe avec détection des conflits de salles et d'horaires.</p>
            </div>
            <div class="bg-white p-10 rounded-[40px] shadow-sm border border-slate-100 hover:shadow-xl transition-all">
                <div class="text-3xl mb-4">📄</div>
                <h3 class="font-black text-slate-800 uppercase text-xs mb-3 italic">Édition de Bulletins</h3>
                <p class="text-slate-500 text-sm leading-relaxed font-medium">Génération instantanée de relevés de notes officiels au format PDF respectant les normes académiques.</p>
            </div>
        </section>
                <!-- SECTION CONTACT -->
        <section id="contact" class="max-w-5xl mx-auto py-20 px-6">
            <div class="bg-indigo-600 rounded-[50px] p-12 text-center text-white shadow-2xl shadow-indigo-200 relative overflow-hidden">
                <!-- Décoration en arrière-plan -->
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white opacity-10 rounded-full"></div>
                <div class="absolute -bottom-10 -left-10 w-60 h-60 bg-white opacity-5 rounded-full"></div>

                <h2 class="text-3xl font-black uppercase tracking-tight mb-4 italic">Besoin d'assistance technique ?</h2>
                <p class="text-indigo-100 mb-8 max-w-xl mx-auto font-medium">
                    Notre équipe est disponible pour accompagner les administrateurs et enseignants dans la configuration de l'établissement.
                </p>

                <div class="flex flex-col sm:flex-row justify-center gap-6">
                    <a href="mailto:support@edumanager.sn" class="bg-white text-indigo-600 px-8 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest shadow-xl hover:scale-105 transition-transform">
                        ✉️ Contacter le Support
                    </a>
                    <div class="flex items-center justify-center gap-2 text-white/80 font-bold text-sm">
                        <span>📞 +221 33 000 00 00</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER FINAL -->
        <footer class="border-t border-slate-200 bg-white py-12 mt-20">
            <div class="max-w-7xl mx-auto px-12 flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex flex-col items-center md:items-start gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🎓</span>
                        <span class="text-lg font-black text-slate-800">Edu<span class="text-indigo-600">Manager</span></span>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Plateforme de Gestion Académique de Nouvelle Génération</p>
                </div>

                <div class="flex gap-8 text-[10px] font-black uppercase tracking-widest text-slate-500">
                    <a href="#" class="hover:text-indigo-600 transition">Aide en ligne</a>
                    <a href="#" class="hover:text-indigo-600 transition">Mentions Légales</a>
                    <a href="#" class="hover:text-indigo-600 transition">Sécurité des Données</a>
                </div>

                <div class="text-[10px] font-black text-slate-400 uppercase">
                    © 2026 • Projet de Mémoire de Master
                </div>
            </div>
        </footer>

    </div>
</template>
