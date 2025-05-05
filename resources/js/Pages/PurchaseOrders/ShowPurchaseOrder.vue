<template>
    <Head title="Purchase Order Details" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Purchase Order Details
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <!-- Order Details -->
                    <div class="mb-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Order Information</h3>
                                <div class="grid grid-cols-2 gap-2">
                                    <p class="text-gray-600">PO Number:</p>
                                    <p>{{ order.po_number }}</p>
                                    <p class="text-gray-600">Date:</p>
                                    <p>{{ order.date }}</p>
                                    <p class="text-gray-600">Time:</p>
                                    <p>{{ order.time }}</p>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold mb-4">Distributor Information</h3>
                                <div class="grid grid-cols-2 gap-2">
                                    <p class="text-gray-600">Distributor:</p>
                                    <p>{{ order.distributor }}</p>
                                    <p class="text-gray-600">Region:</p>
                                    <p>{{ order.region }}</p>
                                    <p class="text-gray-600">Territory:</p>
                                    <p>{{ order.territory }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-4">Order Items</h3>
                        <table class="w-full table-auto border">
                            <thead class="bg-green-600 text-white">
                                <tr>
                                    <th class="p-2 border">SKU Code</th>
                                    <th class="p-2 border">SKU Name</th>
                                    <th class="p-2 border">Quantity</th>
                                    <th class="p-2 border">Unit Price</th>
                                    <th class="p-2 border">Discount</th>
                                    <th class="p-2 border">Discounted Price</th>
                                    <th class="p-2 border">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in order.items" :key="item.id">
                                    <td class="p-2 border">{{ item.sku_code }}</td>
                                    <td class="p-2 border">{{ item.sku.sku_name }}</td>
                                    <td class="p-2 border text-right">{{ item.quantity }}</td>
                                    <td class="p-2 border text-right">{{ formatCurrency(item.unit_price) }}</td>
                                    <td class="p-2 border text-right">{{ item.discount }} %</td>
                                    <td class="p-2 border text-right">{{ formatCurrency(item.discounted_price) }}</td>
                                    <td class="p-2 border text-right">{{ formatCurrency(item.total_price) }}</td>
                                </tr>
                                <tr class="bg-gray-100 font-semibold">
                                    <td colspan="6" class="p-2 border text-right">Total Amount:</td>
                                    <td class="p-2 border text-right">{{ formatCurrency(order.total_amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-6">
                        <button @click="goBack" class="bg-gray-500 text-white px-4 py-2 rounded">
                            Back to List
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    order: Object
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'LKR'
    }).format(value);
};

const goBack = () => {
    window.history.back();
};
</script>