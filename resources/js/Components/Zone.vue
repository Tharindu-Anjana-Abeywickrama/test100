<template>
  <div>
    <h1>Zones</h1>
    <ul>
      <li v-for="zone in zones" :key="zone.id">
        {{ zone.name }}
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      zones: []
    };
  },
  mounted() {
    axios.get('/api/zones')
      .then(response => {
        this.zones = response.data;
      })
      .catch(error => {
        console.error('Error fetching zones:', error);
      });
  }
};
</script>

<style scoped>
/* Add your styles here */
</style>