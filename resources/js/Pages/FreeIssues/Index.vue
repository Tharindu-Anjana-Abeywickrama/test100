<script setup>
import { ref, onMounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';



// Define props
const props = defineProps(['issues', 'skus']);

// Local states
const issues = ref(props.issues);
const skus = ref(props.skus);

const form = ref({
    lable_name: '',
    free_issue_type: '',
    purches_product_id: '',
    free_product_id: '',
    purches_qty: '',
    free_qty: '',
    created_by: ''
});

const showModal = ref(false);
const isEdit = ref(false);

// Save or update free issue
const saveFreeIssue = async () => {
    try {
        let response;
        if (isEdit.value && form.value.id) {
            response = await axios.put(`/free-issues/${form.value.id}`, form.value);
            if (response.data && response.data.issues) {
                issues.value = response.data.issues;
                alert('Free Issue Updated Successfully!');
            }
        } else {
            response = await axios.post('/free-issues', form.value, {
                headers: {
                    Accept: 'application/json'
                }
            });
            if (response.data && response.data.issues) {
                issues.value = response.data.issues;
                alert('Free Issue Created Successfully!');
            } else {
                // Fallback to fetching issues if response doesn't contain issues
                await fetchIssues();
                alert('Free Issue Created Successfully!');
            }
        }
        showModal.value = false;
        resetForm();
    } catch (error) {
        console.error('Error saving free issue:', error);
        if (error.response && error.response.status === 422) {
            alert(error.response.data.error || 'Validation error occurred');
        }
    }
};

// Reset form
const resetForm = () => {
    form.value = {
        lable_name: '',
        free_issue_type: '',
        purches_product_id: '',
        free_product_id: '',
        purches_qty: '',
        free_qty: '',
        created_by: ''
    };
    isEdit.value = false;
};

// Edit issue
const editIssue = (issue) => {
    form.value = {
        id: issue.id,
        lable_name: issue.lable_name,
        free_issue_type: issue.free_issue_type,
        purches_product_id: issue.purches_product_id,
        free_product_id: issue.free_product_id,
        purches_qty: issue.purches_qty,
        free_qty: issue.free_qty
    };
    isEdit.value = true;
    showModal.value = true;
};

// Delete issue
const deleteIssue = async (id) => {
    if (confirm('Are you sure you want to delete this free issue?')) {
        try {
            const response = await axios.delete(`/free-issues/${id}`);
            if (response.data && response.data.issues) {
                issues.value = response.data.issues;
                alert('Free Issue Deleted Successfully!');
            } else {
                await fetchIssues(); // Fallback to fetching issues if response doesn't contain issues
            }
        } catch (error) {
            console.error('Error deleting free issue:', error);
        }
    }
};

// Fetch issues
const fetchIssues = async () => {
    try {
        const response = await axios.get('/free-issues');
        // Check if response contains data property and it's an array
        if (response.data && Array.isArray(response.data.issues)) {
            issues.value = response.data.issues;
            console.log('Issues fetched successfully:', issues.value);
        } else if (response.data && Array.isArray(response.data)) {
            // Fallback for direct array response
            issues.value = response.data;
            console.log('Issues fetched successfully:', issues.value);
        } else {
            console.error('Invalid response format:', response.data);
        }
    } catch (error) {
        console.error('Error fetching issues:', error);
    }
};

// Fetch SKUs
const fetchSkus = async () => {
    try {
        const response = await axios.get('/skus');
        skus.value = response.data;
    } catch (error) {
        console.error('Error fetching skus:', error);
    }
};

onMounted(() => {
    // Initialize with props data
    issues.value = props.issues || [];
    skus.value = props.skus || [];
    
    // Fetch latest data
    fetchIssues();
    fetchSkus();
});
</script>

<template>

    <Head title="Free Issues Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Free Issues Management</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <h3 class="text-lg font-semibold">Free Issues</h3>
                        <button @click="showModal = true"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Add New Free Issue
                        </button>
                    </div>

                    <!-- Free Issues Table -->
                    <table class="w-full table-auto border mt-4">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">Label Name</th>
                                <th class="px-4 py-2">Free Issue Type</th>
                                <th class="px-4 py-2">Purches Product</th>
                                <th class="px-4 py-2">Free Product</th>
                                <th class="px-4 py-2">Purches Qty</th>
                                <th class="px-4 py-2">Free Qty</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(issue, index) in issues" :key="issue.id" class="border-t">
                                <td class="px-4 py-2">{{ index + 1 }}</td>
                                <td class="px-4 py-2">{{ issue.lable_name }}</td>
                                <td class="px-4 py-2">{{ issue.free_issue_type }}</td>
                                <td class="px-4 py-2">{{ issue.purches_product?.sku_name || 'N/A' }}</td>
                                <td class="px-4 py-2">{{ issue.free_product?.sku_name || 'N/A' }}</td>
                                <td class="px-4 py-2">{{ issue.purches_qty }}</td>
                                <td class="px-4 py-2">{{ issue.free_qty }}</td>
                                <td class="px-4 py-2 flex space-x-2">
                                    <button @click="editIssue(issue)"
                                        class="px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500">Edit</button>
                                    <button @click="deleteIssue(issue.id)"
                                        class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Modal -->
                    <div v-if="showModal"
                        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-8 rounded-lg w-full max-w-md">
                            <h3 class="text-lg font-semibold mb-4">{{ isEdit ? 'Edit Free Issue' : 'Add New Free Issue'
                                }}</h3>
                            <form @submit.prevent="saveFreeIssue" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Label Name</label>
                                    <input type="text" v-model="form.lable_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Free Issue Type</label>
                                    <select v-model="form.free_issue_type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required>
                                        <option value="" disabled>Select type</option>
                                        <option value="Flat">Flat</option>
                                        <option value="Multiple">Multiple</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Purches Product</label>
                                    <select v-model="form.purches_product_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required>
                                        <option value="" disabled>Select product</option>
                                        <option v-for="sku in skus" :key="sku.id" :value="sku.id">{{ sku.sku_name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Free Product</label>
                                    <select v-model="form.free_product_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required>
                                        <option value="" disabled>Select product</option>
                                        <option v-for="sku in skus" :key="sku.id" :value="sku.id">{{ sku.sku_name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Purches Qty</label>
                                    <input type="number" v-model="form.purches_qty"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Free Qty</label>
                                    <input type="number" v-model="form.free_qty"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required />
                                </div>

                                <div class="flex justify-end space-x-3">
                                    <button type="button" @click="showModal = false; resetForm()"
                                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
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
