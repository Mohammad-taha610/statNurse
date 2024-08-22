<script lang="ts">
import {defineComponent, inject, Ref, ref, watch} from 'vue';
import {ShiftPayrollPaymentAggregate} from '../../types/shifts/ShiftPayrollPaymentAggregate';
import {VDataTable} from 'vuetify/labs/VDataTable';
import ProviderCombobox from '../Common/ProviderCombobox.vue';
import {Provider} from '../../types/member/Provider';
import {DateTime} from 'luxon';
import PayPeriod from '../../types/shifts/PayPeriod';
import {fetchPbjReportForPeriod} from '../../services/ShiftService';
import {formatCurrency} from '../../utils/currency';

export default defineComponent({
  components: {ProviderCombobox, VDataTable},
  props: {
    payPeriods: {
      type: Array<PayPeriod>,
      required: true
    },
    title: {
      type: String,
    },
    downloadable: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const allShifts: Ref<ShiftPayrollPaymentAggregate[]> = ref([])
    const filteredShifts: Ref<ShiftPayrollPaymentAggregate[]> = ref([])
    const providerFilter: Ref<Provider | null> = ref(null)
    const selectedPayPeriod: Ref<PayPeriod> = ref(props.payPeriods[0])
    const exportCsv = () => {
      const line_items = [];
      let csv = 'Nurse Name, Credentials, Billing Rate, Clocked Hours, Bonus, Holiday Pay, Travel Pay, Total, Date\n';

      for (let i = 0; i < filteredShifts.value.length; i++) {

        const clockedHours1 = filteredShifts.value[i].clockedHours[0].toFixed(2);
        const line = filteredShifts.value[i].nurse.fullName + ", " +
            filteredShifts.value[i].nurse.credentials +
            ", " + filteredShifts.value[i].billRate[0] + ", " + clockedHours1 + ", " +
            filteredShifts.value[i].bonus + ", " +
            filteredShifts.value[i].holidayPay + ", " +
            filteredShifts.value[i].travelPay + ", " +
            filteredShifts.value[i].billTotal[0] + ", " +
            filteredShifts.value[i].date + "\n";

        csv += line;
        if (filteredShifts.value[i].billRate.length > 1) {
          const clockedHours2 = filteredShifts.value[i].clockedHours[1].toFixed(2);
          const line2 = filteredShifts.value[i].nurse.fullName + ", " +
              filteredShifts.value[i].nurse.credentials +
              ", " + filteredShifts.value[i].billRate[1] + ", " + clockedHours2 + ", " +
              filteredShifts.value[i].bonus + ", " +
              filteredShifts.value[i].holidayPay + ", " +
              filteredShifts.value[i].travelPay + ", " +
              filteredShifts.value[i].billTotal[1] + ", " +
              filteredShifts.value[i].date + "\n";
          csv += line2;
        }
      }

      var hiddenElement = document.createElement('a');
      hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);
      hiddenElement.target = '_blank';

      hiddenElement.download = 'PBJ Report ' + selectedPayPeriod.value.display + '.csv';
      hiddenElement.click();
    }


    const headers = [
      {title: 'Nurse Name', value: 'fullName', key: 'fullName'},
      {title: 'Credentials', value: 'nurse.credentials', key: 'nurse.credentials'},
      {title: 'Billing Rate', value: 'billRate', key: 'billRate'},
      {title: 'Clocked Hours', value: 'clockedHours', key: 'clockedHours'},
      {title: 'Bonus', value: 'bonus', key: 'bonus'},
      {title: 'Travel Pay', value: 'travelPay', key: 'travelPay'},
      {title: 'Total', value: 'billTotal', key: 'billTotal'},
      {title: 'Date', value: 'date', key: 'date'},
      {title: 'Facility', value: 'provider.company', key: 'provider.company'}
    ]


    const formatDates = (shifts: ShiftPayrollPaymentAggregate[]) => {
      return shifts.map((shift: ShiftPayrollPaymentAggregate) => {
        return {
          ...shift,
          date: DateTime.fromFormat(shift.date, 'yyyy-MM-dd HH:mm:ss', {zone: 'utc'}).toLocal().toFormat('yyyy-MM-dd')
        }
      })
    }
    const filterByProvider = (provider: Provider | null, shifts: ShiftPayrollPaymentAggregate[]) => {
      return shifts.filter((shift: ShiftPayrollPaymentAggregate) => {
        // if no filter, return all shifts
        if (provider == null) {
          return true
        }
        return provider.id == shift.provider.id;
      })
    }

    watch([providerFilter, allShifts], () => {
      filteredShifts.value = filterByProvider(providerFilter.value, allShifts.value)
    }, {immediate: true});

    watch(selectedPayPeriod, async () => {
      const start = selectedPayPeriod.value.start.toUTC().toFormat('yyyy-MM-dd')
      const end = selectedPayPeriod.value.end.toUTC().toFormat('yyyy-MM-dd')
      const updatedShifts = await fetchPbjReportForPeriod(start, end)
      allShifts.value = formatDates(updatedShifts)
      filteredShifts.value = filterByProvider(providerFilter.value, allShifts.value)
    }, {immediate: true})


    return {
      user: inject('user'),
      headers,
      filteredShifts,
      providerFilter,
      selectedPayPeriod,
      formatCurrency,
      exportCsv
    }
  }
})

</script>

<template>
  <v-card class="rounded-xl px-4 py-4" elevation="0">
    <v-card-title class="border-b border-gray-300">
      <div class="flex flex-row w-full justify-between items-center">
        {{ title }}
        <v-spacer/>
        <v-btn color="blue-grey" append-icon="las la-cloud-download-alt" v-if="downloadable" @click="exportCsv">
          Download Report
        </v-btn>
      </div>
    </v-card-title>
    <div class="flex flex-column justify-between py-5">
        <ProviderCombobox :user="user" v-model="providerFilter"/>
      <v-spacer/>
        <v-combobox
            label="Pay Period"
            variant="underlined"
            v-model="selectedPayPeriod"
            prepend-icon="las la-calendar-week"
            :items="payPeriods"
            return-object
            item-title="display"
        >
        </v-combobox>
    </div>
    <VDataTable :headers="headers" :items="filteredShifts" class="text-xs">
      <template v-slot:item.billRate="{ item }">
        {{formatCurrency(item.raw.billRate)}}
      </template>
      <template v-slot:item.billTotal="{ item }">
        {{formatCurrency(item.raw.billTotal)}}
      </template>
      <template v-slot:item.fullName="{ item }">
        <a :href="item.raw.nurse.nurseRoute" class="text-blue-500">
          {{ item.raw.nurse.fullName }}
        </a>
      </template>
      <!-- format bonus and travel pay as currency -->
      <template v-slot:item.bonus="{ item }">
        {{formatCurrency(item.raw.bonus)}}
      </template>
      <template v-slot:item.travelPay="{ item }">
        {{formatCurrency(item.raw.travelPay)}}
      </template>

    </VDataTable>
  </v-card>
</template>

<style scoped>
.v-list-item {
  font-size: 20px !important;
}
</style>
