<script lang="ts">
import {defineComponent} from 'vue';
import PbjReportTable from '../PbjReportTable.vue';
import PayPeriod from '../../../types/shifts/PayPeriod';

export default defineComponent({
  components: {PbjReportTable},
  props: {
    payPeriods: {
      type: Array<PayPeriod>,
      required: true
    }
  }
})

</script>
<template>
  <PbjReportTable title="Review Shifts" :pay-periods="payPeriods" />
</template>
