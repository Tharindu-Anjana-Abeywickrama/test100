<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';


const zones = ref([]);
const regions = ref([]);
const territories = ref([]);

const form = ref({
    zone_id: '',
    region_id: '',
    territory_id: '',
    distributor_id: '',
    date: '',
    po_no: '',
    remark: '',
    items: []
});

const props = defineProps({
    skus: {
        type: Array,
        required: true
    }
});

const orderItems = ref(props.skus);

const calculateTotal = (item) => {
    item.total_price = item.enter_qty * item.discounted_price;
    return item.total_price;
};

const submitOrder = () => {
    const filteredItems = orderItems.value.filter(item => item.enter_qty > 0);
    form.value.items = filteredItems;

    axios.post('/purchase-orders', form.value)
        .then(response => {
            console.log('Order submitted successfully:', response.data);
            alert('Order submitted successfully!');
        })
        .catch(error => {
            console.error('Error submitting order:', error);
            alert('Failed to submit order. Please try again.');
        });
};

const distributors = ref([]);

const fetchgetPOnumber = async () => {
    try {
        const response = await axios.get('/getPOnumber');
      
        form.value.po_no = response.data.po_number;
    } catch (error) {
        console.error('Error fetching zones:', error);
    }
};

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

// Fetch territories based on selected region
const fetchTerritories = async () => {
    if (form.value.region_id) {
        try {
            const response = await axios.get(`/getTerritories/${form.value.region_id}`);
            territories.value = response.data;
        } catch (error) {
            console.error('Error fetching regions:', error);
        }
    } else {
        territories.value = [];
    }
}

onMounted(() => {
    fetchZones();
    fetchgetPOnumber();
    watch(() => form.value.zone_id, (newZoneId) => {
        if (newZoneId) {
            fetchRegions();
        } else {
            regions.value = [];
        }
    });

    watch(() => form.value.region_id, (newRegionId) => {
        if (newRegionId) {
            fetchTerritories();
        } else {
            territories.value = [];
        }
    });

    axios.get('/getDistributors')
        .then(response => {
            distributors.value = response.data;
        })
        .catch(error => {
            console.error('Error fetching distributors:', error);
        });
});
</script>

<template>

    <Head title="Purchase Order" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Add Individual Purchase Order
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submitOrder">
                        <div class="grid grid-cols-4 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Zone</label>
                                <select v-model="form.zone_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Select</option>
                                    <option v-for="zone in zones" :key="zone.id" :value="zone.id">{{ zone.code }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Region</label>
                                <select v-model="form.region_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Select</option>
                                    <option v-for="region in regions" :key="region.id" :value="region.id">{{ region.code
                                        }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Territory</label>
                                <select v-model="form.territory_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Select</option>
                                    <option v-for="territory in territories" :key="territory.id" :value="territory.id">
                                        {{ territory.code }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Distributor</label>
                                <select v-model="form.distributor_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option v-for="distributor in distributors" :key="distributor.id"
                                        :value="distributor.id">
                                        {{ distributor.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" v-model="form.date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">PO No</label>
                                <input type="text" v-model="form.po_no"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Remark</label>
                                <input type="text" v-model="form.remark"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                            </div>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU CODE
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU NAME
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">UNIT
                                        PRICE</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dic %</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actual price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">AVG QTY
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ENTER
                                        QTY</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">UNITS
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">TOTAL
                                        PRICE</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in orderItems" :key="item.sku_code"> 
                                    <td v-show='false' class="px-6 py-4 whitespace-nowrap">{{ item.sku_id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.sku_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.sku_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.unit_price }}</td> 
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.discount }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.discounted_price }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.avg_qty }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="number" v-model="item.enter_qty" @input="calculateTotal(item)"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.units }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ item.total_price }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                                ADD PO
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>