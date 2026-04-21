<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({ users: Array });

const changeRole = (userId, newRole) => {
    router.patch(route('users.updateRole', userId), { role: newRole });
};
</script>

<template>
    <Head title="Gestion des Utilisateurs" />
    <AuthenticatedLayout>
        <div class="py-12 px-8 max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Gestion des Comptes Utilisateurs</h1>
            <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="px-6 py-4">Nom / Email</th>
                            <th class="px-6 py-4">Rôle Actuel</th>
                            <th class="px-6 py-4 text-center">Modifier le Rôle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="user in users" :key="user.id">
                            <td class="px-6 py-4">
                                <div class="font-bold">{{ user.name }}</div>
                                <div class="text-xs text-gray-400">{{ user.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs font-bold bg-gray-100 uppercase">{{ user.role }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <select @change="changeRole(user.id, $event.target.value)" class="text-sm rounded-lg border-gray-200">
                                    <option :selected="user.role === 'student'" value="student">Étudiant</option>
                                    <option :selected="user.role === 'teacher'" value="teacher">Enseignant</option>
                                    <option :selected="user.role === 'admin'" value="admin">Administrateur</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
