<script lang="ts">
import {defineComponent} from 'vue';
import {VDataTable} from 'vuetify/labs/VDataTable';

export default defineComponent({
  components: {
    VDataTable
  },
  props: {
    payments: Array
  },
  setup(props) {
    console.log(props)
    const headers = [
      {
        title: 'Nurse Name',
        value: 'nurseName',
        key: 'nurseName'
      },
      {
        title: 'Hourly Rate',
        value: 'rate',
        key: 'rate'
      },
      {
        title: 'Clocked Hours',
        value: 'clockedHours',
        key: 'clockedHours'
      },
      {
        title: 'Has Unresolved Payments',
        value: 'hasUnresolvedPayments',
        key: 'hasUnresolvedPayments'
      },
      {
        title: 'Bonus Total',
        value: 'bonusTotal',
        key: 'bonusTotal'
      },
      {
        title: 'Payment Total',
        value: 'payTotal',
        key: 'payTotal'
      }
    ]

    return {
      headers
    }
  }
})



</script>

<template>
  <VDataTable class="overflow-x-scroll" :headers="headers" :items="payments">
    <template v-slot:item.rate="{ item }">
      <span>${{ item.raw.payRate }}</span>
    </template>
    <template v-slot:item.amount="{ item }">
      <span>${{ item.raw.amount }}</span>
    </template>
    <template v-slot:item.clockedHours="{ item }">
      <span>{{ item.raw.clockedHours.toFixed(2) }}</span>
    </template>
    <template v-slot:item.nurseName="{ item }">
      <a class="text-blue" :href="item.raw.nurseRoute">{{ item.raw.nurse.fullName }}</a>
    </template>
    <template v-slot:item.bonusTotal="{ item }">
      <span>${{item.raw.payBonus?.toFixed(2) }}</span>
    </template>
    <template v-slot:item.payTotal="{ item }">
      <span>${{ item.raw.payTotal?.toFixed(2) }}</span>
    </template>
  </VDataTable>
</template>

<style scoped>

</style>
