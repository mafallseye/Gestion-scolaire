<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Connexion - EduManager" />

        <div class="mb-8 text-center">
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Bienvenue !</h1>
            <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-2 italic">
                Espace Académique Numérique
            </p>
        </div>

        <div v-if="status" class="mb-6 p-4 rounded-2xl bg-emerald-50 text-sm font-medium text-emerald-600 border border-emerald-100">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="email" value="Adresse Email" class="text-[10px] font-black uppercase text-slate-400 ml-1 mb-1" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full rounded-2xl border-slate-100 bg-slate-50/50 px-4 py-3 focus:ring-indigo-500 focus:bg-white transition-all shadow-sm"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nom@exemple.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Mot de passe" class="text-[10px] font-black uppercase text-slate-400 ml-1 mb-1" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full rounded-2xl border-slate-100 bg-slate-50/50 px-4 py-3 focus:ring-indigo-500 focus:bg-white transition-all shadow-sm"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <label class="flex items-center cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded-lg border-slate-200 text-indigo-600 focus:ring-indigo-500" />
                    <span class="ms-2 text-[11px] font-bold text-slate-500 uppercase tracking-tighter">Rester connecté</span>
                </label>

                <Link v-if="canResetPassword" :href="route('password.request')" class="text-[11px] font-black text-indigo-600 hover:text-slate-900 transition-colors uppercase tracking-tighter">
                    Mot de passe oublié ?
                </Link>
            </div>

            <div class="mt-8">
                <PrimaryButton
                    class="w-full flex justify-center py-4 bg-indigo-600 hover:bg-slate-900 rounded-2xl shadow-xl shadow-indigo-100 text-[10px] font-black uppercase tracking-[0.2em] transition-all transform active:scale-95 text-white"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Connexion...' : 'Se connecter' }}
                </PrimaryButton>
            </div>
        </form>

        <div class="mt-10 text-center border-t border-slate-50 pt-6">
            <p class="text-[9px] text-slate-300 font-black uppercase tracking-widest">
                EduManager v2.0 • Système Sécurisé
            </p>
        </div>
    </GuestLayout>
</template>
