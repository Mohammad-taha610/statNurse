<script lang="ts">
import {defineComponent, Ref, ref, watch} from 'vue';
import {PayrollPayment} from '../../../types/payroll/PayrollPayment';
import {VDataTable} from 'vuetify/labs/VDataTable';
import {Provider} from '../../../types/member/Provider';
import {Shift} from '../../../types/shifts/Shift';
import {DateTime} from 'luxon';
import RequestChangePopup from './RequestChangePopup.vue';

export default defineComponent({
  components: {
    RequestChangePopup,
    VDataTable
  },
  props: {
    payments: Array<PayrollPayment>,
    shouldAllowRequestChange: Boolean
  },
  setup(props) {
    const headers = [
      {
        title: 'Nurse Name',
        value: 'nurseName',
        key: 'nurseName',
        width: '200px'
      },
      {
        title: 'Shift Time',
        value: 'shiftTime',
        key: 'shiftTime',
        width: '200px'
      },
      {
        title: 'Clocked Hours',
        value: 'clockedHours',
        key: 'clockedHours',
        width: '200px'
      },
      {
        title: 'Hourly Rate',
        value: 'billRate',
        key: 'billRate'
      },
      {
        title: 'Amount',
        value: 'billAmount',
        key: 'billAmount'
      },
      {
        title: 'Type',
        value: 'type',
        key: 'type'
      },
      {
        title: 'Status',
        value: 'status',
        key: 'status'
      },
      {
        title: 'Actions',
        value: 'actions',
        key: 'actions'
      }
    ];
    const getStatusColor = (status: string) => {
      switch (status.toLowerCase()) {
        case 'unresolved':
          return 'red';
        case 'resolved':
        case 'approved':
          return 'green';
        case 'pending':
        case 'change requested':
          return 'orange';
        default:
          return 'grey';
      }
    };
    const getFormattedShiftTime = (shift: Shift) => {
      // converts tart to end to user timezone and format start -end with luxon
      const start = DateTime.fromISO(shift.start).toLocal();
      const end = DateTime.fromISO(shift.end).toLocal();
      return `${start.toLocaleString(DateTime.TIME_SIMPLE)} - ${end.toLocaleString(DateTime.TIME_SIMPLE)}`;
    }
    const getFormattedHours = (hours: string) => {
      try {
        const hoursFloat = parseFloat(hours);
        return hoursFloat.toFixed(2) + ' hours';
      } catch (e) {
        return hours;
      }
    }

    return {
      headers,
      getStatusColor,
      getFormattedShiftTime,
      getFormattedHours
    }
  }
})

</script>

<template>
  <VDataTable class="overflow-x-scroll" :headers="headers" :items="payments">
    <template v-slot:item.actions="{ item }">
      <div v-if="shouldAllowRequestChange">
        <RequestChangePopup :payment="item.raw"/>
      </div>
    </template>
    <template v-slot:item.clockedHours="{ item }">
      <div class="flex flex-col w-fit">
        <span class="text-xs text-gray-500">{{ getFormattedHours(item.raw.clockedHours) }}</span>
        <span>{{ item.raw.actualClockIn }} - {{ item.raw.actualClockOut }}</span>
      </div>
    </template>
    <template v-slot:item.billRate="{ item }">
      <span>${{ item.raw.billRate }}</span>
    </template>
    <template v-slot:item.billAmount="{ item }">
      <span>${{ item.raw.billAmount }}</span>
    </template>
    <template v-slot:item.status="{ item }">
      <v-chip :color="getStatusColor(item.raw.status)">{{ item.raw.status }}</v-chip>
    </template>
    <template v-slot:item.shiftTime="{ item }">
      <span class="text-xs text-gray-500">{{ item.raw.date }}</span><br/>
      <span class="text-md">{{ getFormattedShiftTime(item.raw.shift) }}</span>
    </template>
    <template v-slot:item.nurseName="{ item }">
      <a class="text-blue" :href="item.raw.nurseRoute">{{ item.raw.nurseName }}</a>
    </template>
  </VDataTable>
</template>

<style scoped>

</style>
