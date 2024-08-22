<template>
  <v-container>
    <v-row>
      <v-col cols="6">
        <v-card class="rounded-xl px-4 py-4" elevation="0">
          <div class="flex items-center p-2">
                    <span
                        class="mr-3 bg-red-300/70 text-primary rounded-full p-2 h-12 w-12 text-3xl flex justify-center items-center">
                        <i class="las la-exclamation-triangle text-red-600"></i>
                    </span>
            <div>
              <p class="text-gray-500 text-base font-bold pl-1">Unclaimed Shifts</p>
              <p class="font-bold text-xl p-1">{{ totalUnclaimedShifts }}</p>
            </div>
          </div>
        </v-card>
      </v-col>
      <v-col cols="6">
        <v-card class="rounded-xl px-4 py-4" elevation="0">
          <div class="flex items-center p-2">
                    <span
                        class="mr-3 bg-orange-100/80 text-primary rounded-full p-2 h-12 w-12 text-3xl flex justify-center items-center">
                        <i class="las la-bell text-orange-500 "></i>
                    </span>
            <div>
              <p class="text-gray-500 text-base font-bold pl-1">Shift Requests</p>
              <p class="font-bold text-xl p-1">{{ totalShiftRequests }}</p>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>
    <v-row>
      <v-col cols="6">
        <v-card class="rounded-xl px-4 py-4" elevation="0">
          <div class="flex items-center p-2">
                    <span
                        class="bg-red-300/70 mr-3 bgl-primary text-primary rounded-full p-2 h-12 w-12 text-3xl flex justify-center items-center">
                        <i class="las la-list-alt text-red-600"></i>
                    </span>
            <div>
              <p class="text-gray-500 text-base font-bold pl-1">Unresolved Payments</p>
              <p class="font-bold text-xl p-1">{{ totalUnresolvedPayments }}</p>
            </div>
          </div>
        </v-card>
      </v-col>
      <v-col cols="6">
        <v-card class="rounded-xl px-4 py-4" elevation="0">
          <div class="flex items-center p-2">
                    <span
                        class="bg-green-300/50 mr-3 text-primary rounded-full p-2 h-12 w-12 text-warning text-3xl flex justify-center items-center">
                        <i class="las la-dollar-sign text-green-600"></i>
                    </span>
            <div>
              <p class="text-gray-500 text-base font-bold pl-1">Current Pay Period</p>
              <p class="font-bold text-xl p-1">{{ payPeriod }}</p>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script lang="ts">
import {defineComponent, PropType} from 'vue';
import {DashboardStats} from '../../../types/shifts/DashboardStats';

export default defineComponent({
  name: 'ProviderStats',
  props: {
    stats: {
      type: Array as PropType<DashboardStats[]>,
      default: [],
      required: true,
    },
    payPeriod: {
      type: String as PropType<string>,
      default: '',
      required: true,
    }
  },
  computed: {
    totalUnclaimedShifts() {
      return this.stats.reduce((acc, stat) => acc + stat.unclaimedShifts, 0);
    },
    totalShiftRequests() {
      return this.stats.reduce((acc, stat) => acc + stat.shiftRequests, 0);
    },
    totalUnresolvedPayments() {
      return this.stats.reduce((acc, stat) => acc + stat.unresolvedPayments, 0);
    },
  },
  components: {},
});
</script>

<style scoped>

</style>

