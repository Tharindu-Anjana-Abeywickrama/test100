<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    invoice: Object,
});

const processing = ref(false);

const updateInvoiceStatus = (status) => {
    processing.value = true;
    router.put(`/invoice/${props.invoice.id}/status`, { status }, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
};

const getStatusClass = (status) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'paid':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <Head title="Invoice Details" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Invoice Details
                </h2>
                <Link
                    :href="`/invoice`"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition"
                >
                    Back to Invoices
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Invoice Header Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h3 class="text-lg font-medium mb-4">Invoice Information</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Invoice Number</p>
                                        <p class="text-sm font-bold">{{ invoice.invoice_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Date</p>
                                        <p class="text-sm">{{ invoice.date }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Status</p>
                                        <p class="text-sm">
                                            <span :class="[getStatusClass(invoice.status), 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">{{ invoice.status }}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Total Amount</p>
                                        <p class="text-sm font-bold">{{ invoice.total_amount }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium mb-4">Purchase Order Information</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">PO Number</p>
                                        <p class="text-sm font-bold">{{ invoice.purchase_order.po_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Date</p>
                                        <p class="text-sm">{{ invoice.purchase_order.date }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500">Distributor</p>
                                        <p class="text-sm">{{ invoice.purchase_order.distributor }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="mb-6" v-if="invoice.remarks">
                            <h3 class="text-lg font-medium mb-2">Remarks</h3>
                            <div class="p-4 bg-gray-50 rounded-md">
                                <p class="text-sm">{{ invoice.remarks }}</p>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-4">Invoice Items</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU Code</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU Name</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discounted Price</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in invoice.purchase_order.items" :key="item.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.sku_code }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.sku_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.quantity }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.unit_price }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.discount }}%</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.discounted_price }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.total_price }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-right text-sm font-medium">Total Amount:</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">{{ invoice.total_amount }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Update Status -->
                        <div class="mt-8">
                            <h3 class="text-lg font-medium mb-4">Update Invoice Status</h3>
                            <div class="flex space-x-4">
                                <button 
                                    @click="updateInvoiceStatus('pending')"
                                    :disabled="processing || invoice.status === 'pending'"
                                    class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Mark as Pending
                                </button>
                                <button 
                                    @click="updateInvoiceStatus('paid')"
                                    :disabled="processing || invoice.status === 'paid'"
                                    class="px-4 py-2 bg-green-100 text-green-800 rounded-md hover:bg-green-200 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Mark as Paid
                                </button>
                                <button 
                                    @click="updateInvoiceStatus('cancelled')"
                                    :disabled="processing || invoice.status === 'cancelled'"
                                    class="px-4 py-2 bg-red-100 text-red-800 rounded-md hover:bg-red-200 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Mark as Cancelled
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>