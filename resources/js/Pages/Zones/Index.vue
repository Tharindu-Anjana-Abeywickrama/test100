<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Get zones from Inertia props
const props = defineProps({
    zones: Array
});

const showModal = ref(false);
const editingZone = ref(null);
const form = ref({
    code: '',
    long_description: '',
    short_description: ''
});

const openModal = (zone = null) => {
    if (zone) {
        editingZone.value = zone;
        form.value = { ...zone };
    } else {
        editingZone.value = null;
        form.value = {
            code: '',
            long_description: '',
            short_description: ''
        };
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.value = {
        code: '',
        long_description: '',
        short_description: ''
    };
    editingZone.value = null;
};

const saveZone = async () => {
    try {
        if (editingZone.value) {
            await axios.put(`/zones/${editingZone.value.id}`, form.value);
        } else {
            await axios.post('/zones', form.value);
        }
        closeModal();
        router.reload(); // Reload page and get updated props
    } catch (error) {
        console.error('Error saving zone:', error);
    }
};

const deleteZone = async (zone) => {
    if (confirm('Are you sure you want to delete this zone?')) {
        try {
            await axios.delete(`/zones/${zone.id}`);
            router.reload(); // Reload page and get updated props
        } catch (error) {
            console.error('Error deleting zone:', error);
        }
    }
};
</script>

<template>
    <Head title="Zone Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Zone Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between mb-6">
                            <h3 class="text-lg font-semibold">Zones</h3>
                            <button
                                @click="openModal()"
                                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                            >
                                Add New Zone
                            </button>
                        </div>

                        <!-- Zones Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Long Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Short Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <!-- Loop over zones from props -->
                                    <tr v-for="zone in props.zones" :key="zone.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ zone.code }}</td>
                                        <td class="px-6 py-4">{{ zone.long_description }}</td>
                                        <td class="px-6 py-4">{{ zone.short_description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button
                                                @click="openModal(zone)"
                                                class="text-blue-600 hover:text-blue-900 mr-4"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="deleteZone(zone)"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Modal for Add/Edit Zone -->
                        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                            <div class="bg-white p-8 rounded-lg w-full max-w-md">
                                <h3 class="text-lg font-semibold mb-4">
                                    {{ editingZone ? 'Edit Zone' : 'Add New Zone' }}
                                </h3>
                                <form @submit.prevent="saveZone" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Code</label>
                                        <input
                                            type="text"
                                            v-model="form.code"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Long Description</label>
                                        <input
                                            type="text"
                                            v-model="form.long_description"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Short Description</label>
                                        <input
                                            type="text"
                                            v-model="form.short_description"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        >
                                    </div>
                                    <div class="flex justify-end space-x-3">
                                        <button
                                            type="button"
                                            @click="closeModal"
                                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                        >
                                            {{ editingZone ? 'Update' : 'Save' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
