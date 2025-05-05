<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps(['products']);

const form = ref({
    id: null,
    sku_code: '',
    sku_name: '',
    mrp: '',
    distributor_price: '',
    weight_volume: '',
    weight_unit: ''
});

const showModal = ref(false);
const isEdit = ref(false);

const saveProduct = () => {
    if (isEdit.value && form.value.id) {
        router.put(`/products/${form.value.id}`, {
            sku_code: form.value.sku_code,
            sku_name: form.value.sku_name,
            mrp: form.value.mrp,
            distributor_price: form.value.distributor_price,
            weight_volume: form.value.weight_volume,
            weight_unit: form.value.weight_unit
        }, {
            onSuccess: () => {
                showModal.value = false;
                resetForm();
            }
        });
    } else {
        router.post('/products', {
            sku_code: form.value.sku_code,
            sku_name: form.value.sku_name,
            mrp: form.value.mrp,
            distributor_price: form.value.distributor_price,
            weight_volume: form.value.weight_volume,
            weight_unit: form.value.weight_unit
        }, {
            onSuccess: () => {
                showModal.value = false;
                resetForm();
            }
        });
    }
};

const editProduct = (product) => {
    form.value = { ...product };
    isEdit.value = true;
    showModal.value = true;
};

const deleteProduct = (id) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(`/products/${id}`);
    }
};

const resetForm = () => {
    form.value = {
        id: null,
        sku_code: '',
        sku_name: '',
        mrp: '',
        distributor_price: '',
        weight_volume: '',
        weight_unit: ''
    };
    isEdit.value = false;
};
</script>

<template>
    <Head title="Product Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Product Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <h3 class="text-lg font-semibold">Products</h3>
                        <button
                            @click="showModal = true"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                        >
                            Add New Product
                        </button>
                    </div>

                    <!-- Products Table -->
                    <table class="w-full table-auto border mt-4">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">SKU Code</th>
                                <th class="px-4 py-2">Sku Name</th>
                                <th class="px-4 py-2">MRP</th>
                                <th class="px-4 py-2">Distributor Price</th>
                                <th class="px-4 py-2">Weight</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(product, index) in props.products" :key="product.id" class="border-t">
                                <td class="px-4 py-2">{{ index + 1 }}</td>
                                <td class="px-4 py-2">{{ product.sku_code }}</td>
                                <td class="px-4 py-2">{{ product.sku_name }}</td>
                                <td class="px-4 py-2">{{ product.mrp }}</td>
                                <td class="px-4 py-2">{{ product.distributor_price }}</td>
                                <td class="px-4 py-2">{{ product.weight_volume }} {{ product.weight_unit }}</td>
                                <td class="px-4 py-2 flex space-x-2">
                                    <button @click="editProduct(product)" class="px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500">Edit</button>
                                    <button @click="deleteProduct(product.id)" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Product Form Modal -->
                    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-8 rounded-lg w-full max-w-md">
                            <h3 class="text-lg font-semibold mb-4">{{ isEdit ? 'Edit Product' : 'Add New Product' }}</h3>
                            <form @submit.prevent="saveProduct" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">SKU Code</label>
                                    <input
                                        type="text"
                                        v-model="form.sku_code"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input
                                        type="text"
                                        v-model="form.sku_name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">MRP</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        v-model="form.mrp"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Distributor Price</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        v-model="form.distributor_price"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Weight</label>
                                    <div class="flex space-x-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            v-model="form.weight_volume"
                                            class="mt-1 block w-2/3 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        />
                                        <select
                                            v-model="form.weight_unit"
                                            class="mt-1 block w-1/3 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        >
                                            <option value="g">g</option>
                                            <option value="kg">kg</option>
                                            <option value="ml">ml</option>
                                            <option value="l">l</option>
                                        </select>
                                    </div>
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