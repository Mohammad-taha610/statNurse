<template>
  <v-text-field
      label="Search Nurses"
      variant="solo-filled"
      single-line
      v-model="search"
      hide-details
      density="compact"
      flat
      rounded
      append-inner-icon="las la-search"
      @click:append-inner="searchNurse"
      @click:append="searchNurse"
      @keydown.enter="searchNurse"
  />
</template>

<script lang="ts">
import {defineComponent, ref, watch} from 'vue';

export default defineComponent({
  setup() {
    // if route is like executive/providers/nurse_list?search=Jessica%20Ray
    // set search to param
    // else set search to empty string
    const search = ref('');
    watch (() => window.location.pathname, (newVal) => {
      if (newVal === '/executive/providers/nurse_list') {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('search');
        if (searchParam) {
          search.value = searchParam;
        }
      }
      else {
        search.value = '';
      }
    }, {immediate: true});
    const searchNurse = () => {
      window.location.href = `/executive/providers/nurse_list?search=${search.value}`
    }
    return {
      search,
      searchNurse
    }
  }
});
</script>

<style scoped>

</style>
