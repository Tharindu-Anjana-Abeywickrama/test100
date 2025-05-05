<template>
    <div class="p-4">
      <h2 class="text-xl font-bold mb-4">Zone Registration</h2>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div>
          <label class="block">Zone Code</label>
          <input v-model="form.code" class="border p-2 rounded w-full" />
        </div>
        <div>
          <label class="block">Long Description</label>
          <input v-model="form.long_description" class="border p-2 rounded w-full" />
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
          Save Zone
        </button>
      </form>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        form: {
          code: '',
          long_description: '',
        }
      }
    },
    methods: {
      async submitForm() {
        try {
          await axios.post('/api/zones', this.form);
          alert('Zone saved successfully!');
          this.form = { code: '', long_description: '' }; // Reset form
        } catch (error) {
          alert('Error saving zone: ' + error.response.data.message);
        }
      }
    }
  }
  </script>