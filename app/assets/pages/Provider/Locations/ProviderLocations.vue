<script setup lang="ts">
import ProviderLayout from '../../../components/Provider/ProviderLayout.vue';
import {ProviderLocation} from '../../../types/member/ProviderLocation';
import {UserProp} from '../../../utils/props';
import {provide} from 'vue';
import {VDataTable} from 'vuetify/labs/VDataTable';

const {user, locations} = defineProps({
  locations: {
    type: Array<ProviderLocation>,
    required: true
  },
  user: UserProp
})

provide('user', user)
const sortedLocations = locations.sort((a, b) => {
  if (a.provider.company < b.provider.company) {
    return -1;
  }
  if (a.provider.company > b.provider.company) {
    return 1;
  }
  return 0;
})

const headers = [
  {
    title: 'Facility',
    value: 'provider.company',
    key: 'company',
    width: '300px',
  },
  {
    title: 'Shift Requests',
    value: 'shiftRequestCount',
    key: 'shiftRequestCount',
  },
  {
    title: 'Unclaimed Shifts',
    value: 'unclaimedShiftsCount',
    key: 'unclaimedShiftsCount',
  },
  {
    title: 'Unresolved Payments',
    value: 'unresolvedPaymentCount',
    key: 'unresolvedPaymentCount',
  }

]

console.log(sortedLocations, 'sorted locations')
</script>


<template>
  <ProviderLayout>
    <v-card class="rounded-xl" elevation="0">
      <div class="py-5">
        <div class="flex flex-row justify-center text-xl my-3">
          <span>Current Pay Period: {{ sortedLocations[0]?.currentPayPeriod }}</span>
        </div>
        <VDataTable
            :items="sortedLocations"
            :headers="headers"
        >
          <template v-slot:item.company="{ item }">
            <a :href="item.raw.provider.providerRoute" class="text-xl font-bold mb-3 flex items-center gap-2">
              <span class="text-sm font-bold">{{ item.raw.provider.company }}</span>
            </a>
          </template>
        </VDataTable>
      </div>
    </v-card>
  </ProviderLayout>
</template>

<style scoped>

</style>
