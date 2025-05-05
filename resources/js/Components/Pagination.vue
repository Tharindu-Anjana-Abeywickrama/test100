<template>
  <div class="pagination">
    <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">
      Previous
    </button>
    
    <button v-for="page in props.pagination.last_page" @click="changePage(page)" :class="{'active': page === props.pagination.current_page}">{{ page }}</button>
    <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">
      Next
    </button>
   
  </div>
  
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  pagination: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['page-changed']);

const changePage = (page) => {
  if (page >= 1 && page <= props.pagination.last_page) {
    emit('page-changed', page);
  }
};
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}
button {
  padding: 5px 10px;
  background-color: gray;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  margin-right: 5px;
}
button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}
button.active {
  background-color: #4CAF50;
}
button.export-pdf {
  background-color: red;
}
</style>