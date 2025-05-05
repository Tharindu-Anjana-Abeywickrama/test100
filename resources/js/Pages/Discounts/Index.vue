<script setup>
import { ref, onMounted } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import axios from 'axios';

const props = defineProps({
  discounts: Array,
  skus: Array
});

const discounts = ref(props.discounts || []);
const skus = ref(props.skus || []);

const form = ref({
  id: null,
  label_name: '',
  purches_product_id: null,
  discount: 0
});

const showModal = ref(false);
const isEdit = ref(false);

const fetchDiscounts = async () => {
  try {
    // Make an API call to get the latest discounts
    const response = await axios.get('/discounts/get');
    discounts.value = response.data;
  } catch (error) {
    console.error('Error fetching discounts:', error);
  }
};

const fetchSkus = async () => {
  try {
    // Use props.skus directly instead of making an API call
    // This data is already loaded from the server during page load
    if (props.skus && props.skus.length > 0) {
      skus.value = props.skus;
    } else {
      // Fallback to API call if props are empty
      const response = await axios.get('/skus');
      skus.value = response.data;
    }
  } catch (error) {
    console.error('Error processing SKUs:', error);
  }
};

const openModal = (discount = null) => {
  if (discount) {
    form.value = {
      id: discount.id,
      label_name: discount.label_name,
      purches_product_id: discount.purches_product_id,
      discount: discount.discount
    };
    isEdit.value = true;
  } else {
    form.value = {
      id: null,
      label_name: '',
      purches_product_id: null,
      discount: 0
    };
    isEdit.value = false;
  }
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  form.value = {
    id: null,
    label_name: '',
    purches_product_id: null,
    discount: 0
  };
};

const submitForm = async () => {
  try {
    let response;
    if (isEdit.value) {
      response = await axios.put(`/discounts/${form.value.id}`, form.value);
      if (response.data.success) {
        // Update the discount in the existing array
        const index = discounts.value.findIndex(d => d.id === response.data.discount.id);
        if (index !== -1) {
          discounts.value[index] = response.data.discount;
        }
        alert('Discount updated successfully!');
      }
    } else {
      response = await axios.post('/discounts', form.value);
      if (response.data.success) {
        // Add the new discount to the array
        discounts.value.push(response.data.discount);
        alert('Discount saved successfully!');
      }
    }
    closeModal();
  } catch (error) {
    console.error('Error saving discount:', error);
  }
};

const deleteDiscount = async (id) => {
  if (!confirm('Are you sure you want to delete this discount?')) return;
  
  try {
    const response = await axios.delete(`/discounts/${id}`);
    if (response.data.success) {
      // Remove the discount from the array
      discounts.value = discounts.value.filter(discount => discount.id !== id);
      alert('Discount deleted successfully!');
    }
  } catch (error) {
    console.error('Error deleting discount:', error);
  }
};

onMounted(() => {
  fetchSkus();
  fetchDiscounts();
});
</script>
<template>
  <Head title="Discount Management" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Discount Management
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          <div class="flex justify-between mb-6">
            <h3 class="text-lg font-semibold">Manage Discounts</h3>
            <button 
              @click="openModal()" 
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
            >
              Add New Discount
            </button>
          </div>

          <!-- Discounts Table -->
          <div class="overflow-x-auto mt-6">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label Name</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount (%)</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="discount in discounts" :key="discount.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">{{ discount.label_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ discount.sku ? discount.sku.sku_name : 'N/A' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ discount.discount }}%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button 
                      @click="openModal(discount)" 
                      class="text-indigo-600 hover:text-indigo-900 mr-3"
                    >
                      Edit
                    </button>
                    <button 
                      @click="deleteDiscount(discount.id)" 
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </td>
                </tr>
                <tr v-if="discounts.length === 0">
                  <td colspan="4" class="px-6 py-4 text-center text-gray-500">No discounts found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal for Add/Edit Discount -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">{{ isEdit ? 'Edit Discount' : 'Add New Discount' }}</h3>
          <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label for="label_name" class="block text-sm font-medium text-gray-700">Label Name:</label>
            <input 
              type="text" 
              v-model="form.label_name" 
              id="label_name" 
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
              required 
            />
          </div>
          <div>
            <label for="purches_product_id" class="block text-sm font-medium text-gray-700">Purchase Product:</label>
            <select 
              v-model="form.purches_product_id" 
              id="purches_product_id" 
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
              required
            >
              <option value="" disabled>Select a product</option>
              <option v-for="sku in skus" :key="sku.id" :value="sku.id">
                {{ sku.sku_name }}
              </option>
            </select>
          </div>
          <div>
            <label for="discount" class="block text-sm font-medium text-gray-700">Discount (%):</label>
            <input 
              type="number" 
              v-model="form.discount" 
              id="discount" 
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
              required 
              min="0" 
              max="100" 
            />
          </div>
          <div class="pt-4 flex justify-end space-x-3">
            <button 
              type="button"
              @click="closeModal"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
            >
              {{ isEdit ? 'Update' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>



<style scoped>
/* Most styles are now handled by Tailwind classes in the template */
</style>