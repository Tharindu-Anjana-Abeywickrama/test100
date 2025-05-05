<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps(['users', 'territories']);

const form = ref({
    id: null,
    name: '',
    nic: '',
    address: '',
    mobile: '',
    email: '',
    gender: '',
    territory_id: '',
    username: '',
    password: ''
});

const showModal = ref(false);
const isEdit = ref(false);

// Save or update user
const saveUser = () => {
    if (isEdit.value && form.value.id) {
        router.put(`/users/${form.value.id}`, form.value, {
            onSuccess: () => {
                showModal.value = false;
                resetForm();
            }
        });
    } else {
        router.post('/users', form.value, {
            onSuccess: () => {
                showModal.value = false;
                resetForm();
            }
        });
    }
};

// Delete user
const deleteUser = (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/users/${id}`);
    }
};

// Edit user
const editUser = (user) => {
    form.value = {
        id: user.id,
        name: user.name,
        nic: user.nic,
        address: user.address,
        mobile: user.mobile,
        email: user.email,
        gender: user.gender,
        territory_id: user.territory_id,
        username: user.username,
        password: ''
    };
    isEdit.value = true;
    showModal.value = true;
};

const resetForm = () => {
    form.value = {
        id: null,
        name: '',
        nic: '',
        address: '',
        mobile: '',
        email: '',
        gender: '',
        territory_id: '',
        username: '',
        password: ''
    };
    isEdit.value = false;
};
</script>

<template>
    <Head title="User Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                User Management
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between mb-6">
                        <h3 class="text-lg font-semibold">Users</h3>
                        <button
                            @click="showModal = true"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                        >
                            Add New User
                        </button>
                    </div>

                    <!-- Users Table -->
                    <table class="w-full table-auto border mt-4">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">NIC</th>
                                <th class="px-4 py-2">Mobile</th>
                                <th class="px-4 py-2">Email</th>
                                <th class="px-4 py-2">Territory</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(user, index) in props.users" :key="user.id" class="border-t">
                                <td class="px-4 py-2">{{ index + 1 }}</td>
                                <td class="px-4 py-2">{{ user.name }}</td>
                                <td class="px-4 py-2">{{ user.nic }}</td>
                                <td class="px-4 py-2">{{ user.mobile }}</td>
                                <td class="px-4 py-2">{{ user.email }}</td>
                                <td class="px-4 py-2">{{ user.territory?.code }}</td>
                                <td class="px-4 py-2 flex space-x-2">
                                    <button @click="editUser(user)" class="px-2 py-1 bg-yellow-400 text-white rounded hover:bg-yellow-500">Edit</button>
                                    <button @click="deleteUser(user.id)" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- User Form Modal -->
                    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-8 rounded-lg w-full max-w-md">
                            <h3 class="text-lg font-semibold mb-4">{{ isEdit ? 'Edit User' : 'Add New User' }}</h3>
                            <form @submit.prevent="saveUser" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input
                                        type="text"
                                        v-model="form.name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">NIC</label>
                                    <input
                                        type="text"
                                        v-model="form.nic"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <input
                                        type="text"
                                        v-model="form.address"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Mobile</label>
                                    <input
                                        type="text"
                                        v-model="form.mobile"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email</label>
                                    <input
                                        type="email"
                                        v-model="form.email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Gender</label>
                                    <select v-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="" disabled>Select gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Territory</label>
                                    <select v-model="form.territory_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="" disabled>Select territory</option>
                                        <option v-for="territory in props.territories" :key="territory.id" :value="territory.id">
                                            {{ territory.code }}
                                        </option>
                                    </select>
                                </div>

                                <div style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700">Username</label>
                                    <input
                                        type="text"
                                        v-model="form.email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Password</label>
                                    <input
                                        type="password"
                                        v-model="form.password"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        :required="!isEdit"
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