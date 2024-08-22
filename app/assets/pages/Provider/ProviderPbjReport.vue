<script lang="ts">
import {defineComponent, provide} from 'vue';
import {UserProp} from '../../utils/props';
import ProviderLayout from '../../components/Provider/ProviderLayout.vue';
import PbjReportTable from '../../components/Provider/PbjReportTable.vue';
import {DateTime} from 'luxon';
import PayPeriod from '../../types/shifts/PayPeriod';

export default defineComponent({
  name: 'ProviderPbjReport',
  components: {PbjReportTable, ProviderLayout},
  props: {
    user: UserProp,
  },
  setup(props) {
    function getCurrentAndLastFourQuarters() {
      const currentDateTime = DateTime.local();
      let currentQuarter = currentDateTime.quarter;
      let currentYear = currentDateTime.year;

      // get start of current quarter
      const currentQuarterStart = currentDateTime.startOf('quarter')

      // get dates for last 12 months starting from current quarter
      const dateDisplayFormat = 'yyyy/MM/dd';
      const quarters: PayPeriod[] = [
        {
          start: currentQuarterStart,
          end: DateTime.local().endOf('day'),
          display: currentQuarterStart.toFormat(dateDisplayFormat) + ' - ' + DateTime.local().endOf('day').toFormat(dateDisplayFormat)
        } as PayPeriod
      ];
      for (let i = 1; i < 5; i++) {
        const start = currentQuarterStart.minus({months: i * 3});
        const end = currentQuarterStart.minus({months: (i * 3) - 3});
        quarters.push({
          start,
          end,
          display: start.toFormat(dateDisplayFormat) + ' - ' + end.toFormat(dateDisplayFormat)
        })
      }
      console.log(quarters)
      return quarters;
    }

    const payPeriods: PayPeriod[] = getCurrentAndLastFourQuarters();
    provide('user', props.user)

    return {
      payPeriods
    }
  }
});
</script>

<template>
  <ProviderLayout>
    <PbjReportTable :downloadable="true" title="PBJ Report" :pay-periods="payPeriods"/>
  </ProviderLayout>
</template>

<style scoped>

</style>
