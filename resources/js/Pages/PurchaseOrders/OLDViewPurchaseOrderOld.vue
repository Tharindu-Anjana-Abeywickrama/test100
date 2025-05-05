
<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue'; 



const props = defineProps({
    initialOrders: Array,
    pagination: {
        type: Object,
        default: () => ({
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
        })
    }
});

const filters = ref({
    region: '',
    territory: '',
    po_no: '',
    from: '',
    to: ''
}) 

const pagination = ref(props.pagination || {
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
});

watch(() => props.pagination, (newPagination) => {
    if (newPagination) {
        pagination.value = newPagination;
    }
});

// Ensure pagination is not undefined before accessing its properties
if (!pagination.value) {
    pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0
    };
}
const orders = ref(props.initialOrders || [])
const regions = ref([])
const territories = ref([])
const selectedOrders = ref([])

const fetchOrders = async (page = 1) => { 
    
    try {
        const response = await axios.get('/purchase-orders', { 
            params: { ...filters.value, page } 
        });
        pagination.value = response.data.pagination || {
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0
        };
        orders.value = response.data.data;
    } catch (error) {
        console.error('Error fetching orders:', error);
    }
}

const fetchRegions = async () => {
    const response = await axios.get(`/getRegionsAll`);
    regions.value = response.data;
}

const fetchTerritories = async () => {
    const response = await axios.get(`/getTerritories`);
    territories.value = response.data;
}

const bulkExport = async (format) => {
    try {
        const response = await axios.post('/bulk-export-purchase-orders', {
            ids: selectedOrders.value.join(','),
            format
        }, {
            responseType: 'blob'
        });
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `Bulk_PO_Export.${format === 'excel' ? 'xlsx' : 'pdf'}`);
        document.body.appendChild(link);
        link.click();
    } catch (error) {
        console.error('Error exporting orders:', error);
    }
}

onMounted(() => {
    fetchRegions();
    fetchTerritories();
})
</script>

<template>

    <Head title="View Purchase Order" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                View Purchase Orders
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                    <!-- Filters -->
                    <div class="grid grid-cols-6 gap-2 mb-4">
                        <select v-model="filters.region" class="border p-2">
                            <option value="">Select Region</option>
                            <option v-for="region in regions" :key="region.id" :value="region.id">{{ region.code
                                        }}
                                    </option>
                        </select>

                        <select v-model="filters.territory" class="border p-2">
                            <option value="">Select Territory</option>
                            <option v-for="territory in territories" :key="territory.id" :value="territory.id">
                                        {{ territory.code }}
                                    </option>
                        </select>

                        <input v-model="filters.po_no" type="text" placeholder="PO NO" class="border p-2" />
                        <input v-model="filters.from" type="date" class="border p-2" />
                        <input v-model="filters.to" type="date" class="border p-2" />

                        <button @click="fetchOrders" class="bg-green-600 text-white p-2 rounded">
                            Search
                        </button>
                    </div>
                    <button @click="bulkExport('excel')" class="bg-blue-500 text-white px-4 py-2 rounded">
                        Export Selected to Excel
                    </button>
                    <button @click="bulkExport('pdf')" class="bg-blue-500 text-white px-4 py-2 rounded">
                        Export Selected to PDF
                    </button>
                    <!-- Table -->
                    <table class="w-full table-auto border mb-4">
                        <thead class="bg-green-600 text-white">
                            <tr>
                                <th class="p-2 border">Select</th>
                                <th class="p-2 border">Region</th>
                                <th class="p-2 border">Territory</th>
                                <th class="p-2 border">Distributor</th>
                                <th class="p-2 border">PO Number</th>
                                <th class="p-2 border">Date</th>
                                <th class="p-2 border">Time</th>
                                <th class="p-2 border">Total Amount</th>
                                <th class="p-2 border">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(order, index) in orders" :key="index">
                                <td class="p-2 border">
                                    <input type="checkbox" v-model="selectedOrders" :value="order.id" />
                                </td>
                                <td class="p-2 border">{{ order.region }}</td>
                                <td class="p-2 border">{{ order.territory }}</td>
                                <td class="p-2 border">{{ order.distributor }}</td>
                                <td class="p-2 border">{{ order.po_number }}</td>
                                <td class="p-2 border">{{ order.date }}</td>
                                <td class="p-2 border">{{ order.time }}</td>
                                <td class="p-2 border">{{ order.total_amount }}</td>
                                <td class="p-2 border">
                                    <a 
                                        :href="`/purchase-orders/${order.id}/details`" 
                                        class="bg-green-400 px-3 py-1 text-white rounded mr-2 inline-block"
                                    >
                                        View
                                    </a>
                                    <a 
                                        :href="`/export-purchase-order/${order.id}`" 
                                        class="bg-blue-400 px-3 py-1 text-white rounded inline-block"
                                        target="_blank"
                                    >
                                        Export
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                   
                    <Pagination :pagination="pagination" @page-changed="fetchOrders" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
