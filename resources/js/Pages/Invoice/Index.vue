<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { format } from 'date-fns';
import axios from 'axios';

const props = defineProps({
    invoices: Array,
    purchaseOrders: Array,
});

const invoiceForm = ref({
    purchase_order_id: '',
    invoice_number: '',
    date: format(new Date(), 'yyyy-MM-dd'),
    remarks: '',
    items: [],
    total_amount: 0,
});

const selectedPO = ref(null);
const showInvoiceModal = ref(false);
const processing = ref(false);
const errors = ref({});

const openInvoiceModal = async (purchaseOrder) => {
    selectedPO.value = purchaseOrder;
    invoiceForm.value.purchase_order_id = purchaseOrder.id;
    invoiceForm.value.date = format(new Date(), 'yyyy-MM-dd');
    generateInvoiceNumber();
    
    try {
        const response = await axios.get(`/get-purchase-order-items/${purchaseOrder.id}`);
        invoiceForm.value.items = response.data.purchase_order.items;
        calculateTotalAmount();
        showInvoiceModal.value = true;
    } catch (error) {
        console.error('Error loading purchase order items:', error);
    }
};

const generateInvoiceNumber = async () => {
    try {
        const response = await axios.get('/generate-invoice-number');
        invoiceForm.value.invoice_number = response.data.invoice_number;
    } catch (error) {
        console.error('Error generating invoice number:', error);
    }
};

const createInvoice = () => {
    processing.value = true;
    errors.value = {};
    
    // Ensure total amount is calculated before submission
    calculateTotalAmount();
    
    router.post('/invoice', invoiceForm.value, {
        onSuccess: () => {
            showInvoiceModal.value = false;
            processing.value = false;
            invoiceForm.value = {
                purchase_order_id: '',
                invoice_number: '',
                date: format(new Date(), 'yyyy-MM-dd'),
                remarks: '',
                items: [],
                total_amount: 0,
            };
        },
        onError: (err) => {
            errors.value = err;
            processing.value = false;
        },
    });
};

const updateInvoiceStatus = (invoiceId, status) => {
    router.put(`/invoice/${invoiceId}/status`, { status }, {
        preserveScroll: true,
    });
};

const calculateTotalAmount = () => {
    invoiceForm.value.total_amount = invoiceForm.value.items.reduce((total, item) => {
        return total + (item.invoice_qty * item.discounted_price);
    }, 0);
};

const updateInvoiceQty = (item, value) => {
    // Convert to number and ensure it's not negative
    const qty = Math.max(0, Number(value));
    
    // Ensure invoice quantity doesn't exceed available quantity
    item.invoice_qty = Math.min(qty, item.quantity);
    
    // Recalculate item total and invoice total
    item.invoice_total = item.invoice_qty * item.discounted_price;
    calculateTotalAmount();
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
    <Head title="Invoice Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Invoice Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Invoices List -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="mb-4 text-lg font-medium">Invoices</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distributor</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="invoices.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No invoices found</td>
                                    </tr>
                                    <tr v-for="invoice in invoices" :key="invoice.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ invoice.po_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ invoice.date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ invoice.distributor }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ invoice.total_amount }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span :class="[getStatusClass(invoice.status), 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full']">{{ invoice.status }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="`/invoice/${invoice.id}`" class="text-indigo-600 hover:text-indigo-900 mr-2">View</Link>
                                            <!-- <div class="inline-block relative group">
                                                <button type="button" class="text-gray-600 hover:text-gray-900 focus:outline-none">Update Status</button>
                                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                                                    <button @click="updateInvoiceStatus(invoice.id, 'pending')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">Pending</button>
                                                    <button @click="updateInvoiceStatus(invoice.id, 'paid')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">Paid</button>
                                                    <button @click="updateInvoiceStatus(invoice.id, 'cancelled')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">Cancelled</button>
                                                </div>
                                            </div> -->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Purchase Orders List -->
                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="mb-4 text-lg font-medium">Purchase Orders Available for Invoicing</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distributor</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="purchaseOrders.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No purchase orders available for invoicing</td>
                                    </tr>
                                    <tr v-for="po in purchaseOrders" :key="po.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ po.po_number }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ po.date }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ po.distributor }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ po.total_amount }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button @click="openInvoiceModal(po)" class="text-indigo-600 hover:text-indigo-900">Create Invoice</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Creation Modal -->
        <div v-if="showInvoiceModal" class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

               <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Create Invoice for PO #{{ selectedPO?.po_number }}
                                </h3>
                                <div class="mt-4">
                                    <form @submit.prevent="createInvoice">
                                        <div class="mb-4">
                                            <label for="invoice_number" class="block text-sm font-medium text-gray-700">Invoice Number</label>
                                            <input type="text" id="invoice_number" v-model="invoiceForm.invoice_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                            <p v-if="errors.invoice_number" class="mt-1 text-sm text-red-600">{{ errors.invoice_number }}</p>
                                        </div>
                                        <div class="mb-4">
                                            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                                            <input type="date" id="date" v-model="invoiceForm.date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required />
                                            <p v-if="errors.date" class="mt-1 text-sm text-red-600">{{ errors.date }}</p>
                                        </div>
                                        
                                        <!-- PO Items Table -->
                                        <div class="mb-4">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Purchase Order Items</h4>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU Code</th>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU Name</th>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Qty</th>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Qty</th>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <tr v-for="item in invoiceForm.items" :key="item.id">
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ item.sku_code }}</td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ item.sku_name }}</td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ item.quantity }}</td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                                                                <input 
                                                                    type="number" 
                                                                    v-model="item.invoice_qty" 
                                                                    @input="updateInvoiceQty(item, $event.target.value)" 
                                                                    min="0" 
                                                                    :max="item.quantity" 
                                                                    class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                                                />
                                                            </td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ item.unit_price }}</td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ item.discount }}%</td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ (item.invoice_qty * item.discounted_price).toFixed(2) }}</td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="6" class="px-3 py-2 text-right text-sm font-medium">Total Amount:</td>
                                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-bold">{{ invoiceForm.total_amount.toFixed(2) }}</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                                            <textarea id="remarks" v-model="invoiceForm.remarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                            <p v-if="errors.remarks" class="mt-1 text-sm text-red-600">{{ errors.remarks }}</p>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="createInvoice" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" :disabled="processing">
                            <span v-if="processing">Processing...</span>
                            <span v-else>Create Invoice</span>
                        </button>
                        <button @click="showInvoiceModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>