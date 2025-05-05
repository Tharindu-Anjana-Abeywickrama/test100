<script setup>
import { ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps(['territories']);

const zones = ref([]);
const regions = ref([]);
const form = ref({
    id: null,
    zone_id: '',
    region_id: '',
    territory_code: '',
    territory_name: ''
});
const showModal = ref(false);
const isEdit = ref(false);

// Fetch zones
const fetchZones = async () => {
    try {
        const response = await axios.get('/getZones');
        zones.value = response.data;
    } catch (error) {
        console.error('Error fetching zones:', error);
    }
};

// Fetch regions based on selected zone
const fetchRegions = async () => {
    if (form.value.zone_id) {
        try {
            const response = await axios.get(`/getRegions/${form.value.zone_id}`);
            regions.value = response.data;
        } catch (error) {
            console.error('Error fetching regions:', error);
        }
    } else {
        regions.value = [];
    }
};

// Watch for zone changes
watch(() => form.value.zone_id, () => {
    form.value.region_id = '';
    fetchRegions();
});

// Save or update
const saveTerritory = async () => {
    try {
        if (isEdit.value && form.value.id) {
            await axios.put(`/territories/${form.value.id}`, form.value);
        } else {
            await axios.post('/territories', form.value);
        }
        showModal.value = false;
        resetForm();
        router.reload();
    } catch (error) {
        console.error('Error saving territory:', error);
    }
};

// Edit territory
const editTerritory = async (territory) => { 
    // First set the zone_id
    form.value.zone_id = territory.zone_id;
    
    // Wait for regions to load
    await fetchRegions();
    
    // Then set the rest of the form data
    form.value = {
        id: territory.id,
        zone_id: territory.zone_id,
        region_id: territory.region_id,
        territory_code: territory.code,
        territory_name: territory.name
    };
    
    isEdit.value = true;
    showModal.value = true;
};

// Delete territory
const deleteTerritory = async (id) => {
    if (confirm('Are you sure you want to delete this territory?')) {
        try {
            await axios.delete(`/territories/${id}`);
            router.reload();
        } catch (error) {
            console.error('Error deleting territory:', error);
        }
    }
};

const resetForm = () => {
    form.value = {
        id: null,
        zone_id: '',
        region_id: '',
        territory_code: '',
        territory_name: ''
    };
    isEdit.value = false;
};

onMounted(() => {
    fetchZones();
});
</script>

<template>
    <Head title="Territory Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Territory Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <h3 class="text-lg font-semibold">Territories</h3>
                        <button
                            @click="showModal = true"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                        >
                            Add New Territory
                        </button>
                    </div>

                    <!-- Territory Table -->
                    <table class="w-full table-auto border mt-4">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">Territory Code</th>
                                <th class="px-4 py-2">Territory Name</th>
                                <th class="px-4 py-2">Zone</th>
                                <th class="px-4 py-2">Region</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(territory, index) in props.territories" :key="territory.id" class="border-t">
                                <td class="px-4 py-2">{{ index + 1 }}</td>
                                <td class="px-4 py-2">{{ territory.code }}</td>
                                <td class="px-4 py-2">{{ territory.name }}</td>
                                <td class="px-4 py-2">{{ territory.zone?.code }}</td>
                                <td class="px-4 py-2">{{ territory.region?.code }}</td>
                                <td class="px-4 py-2 flex space-x-2">
                                    <button @click="editTerritory(territory)" class="px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500">Edit</button>
                                    <button @click="deleteTerritory(territory.id)" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Territory Form Modal -->
                    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-8 rounded-lg w-full max-w-md">
                            <h3 class="text-lg font-semibold mb-4">{{ isEdit ? 'Edit Territory' : 'Add New Territory' }}</h3>
                            <form @submit.prevent="saveTerritory" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Select Zone</label>
                                    <select v-model="form.zone_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="" disabled>Select a zone</option>
                                        <option v-for="zone in zones" :key="zone.id" :value="zone.id">{{ zone.code }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Select Region</label>
                                    <select v-model="form.region_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="" disabled>Select a region</option>
                                        <option v-for="region in regions" :key="region.id" :value="region.id">{{ region.code }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Territory Code</label>
                                    <input
                                        type="text"
                                        v-model="form.territory_code"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Territory Name</label>
                                    <input
                                        type="text"
                                        v-model="form.territory_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <button
                                        type="button"
                                        @click="showModal = false; resetForm()"
                                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                    >
                                        {{ isEdit ? 'Update' : 'Save' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>