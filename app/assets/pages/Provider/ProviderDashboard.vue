<template>
  <ProviderLayout>
    <ProviderStats :stats="data?.dashboardStats ?? []" :payPeriod="data?.currentPayPeriod.display ?? []"/>
    <UpcomingShiftsTable :total-pages="data.totalPages" :shifts="data?.shifts ?? []" />
  </ProviderLayout>
</template>

<script lang="ts">
import { defineComponent, provide, ref, PropType } from 'vue'
import ProviderLayout from '../../components/Provider/ProviderLayout.vue';
import {Shift} from '../../types/shifts/Shift';
import {DashboardStats} from '../../types/shifts/DashboardStats';
import UpcomingShiftsTable from '../../components/Provider/Dashboard/UpcomingShiftsTable.vue';
import ProviderStats from '../../components/Provider/Stats/ProviderStats.vue'
import {UserProp} from '../../utils/props';


interface ProviderDashboardData {
  totalPages: number;
  shifts: Shift[];
  dashboardStats: DashboardStats[];
  currentPayPeriod: {
    display: string;
  };
}

export default defineComponent({
  components: {UpcomingShiftsTable, ProviderStats, ProviderLayout},
  props: {
    user: UserProp,
    data: {
      type: Object as PropType<ProviderDashboardData>,
      required: true
    }
  },
  setup(props) {
    provide('user', props.user);
  },
})
</script>

<style scoped>

</style>
