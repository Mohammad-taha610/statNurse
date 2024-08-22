<script lang="ts">
import ProviderLayout  from '../../../components/Provider/ProviderLayout.vue';
import {defineComponent, PropType, provide} from 'vue';
import {UserProp} from '../../../utils/props';
import {ShiftPayrollPaymentAggregate} from '../../../types/shifts/ShiftPayrollPaymentAggregate';
import ReviewShiftsTable from '../../../components/Provider/Shifts/ReviewShiftsTable.vue';
import PayPeriod from '../../../types/shifts/PayPeriod';
import {DateTime} from 'luxon';

export default defineComponent({
  components: {ReviewShiftsTable, ProviderLayout},
  props: {
    user: UserProp,
    shifts: {
      type: Array<ShiftPayrollPaymentAggregate>,
      required: true
    },
    payPeriods: {
      type: Array<any>,
      required: true
    }
  },
  setup(props) {
    provide('user', props.user)
    const payPeriodsParsed = props.payPeriods.map((payPeriod: any) => {
      return {
        ...payPeriod,
        start: DateTime.fromISO(payPeriod.start),
        end: DateTime.fromISO(payPeriod.end),
      } as PayPeriod
    });
    return {
      payPeriodsParsed,
    }
  }
})
</script>

<template>
  <ProviderLayout>
    <ReviewShiftsTable :pay-periods="payPeriodsParsed" :shifts="shifts" />
  </ProviderLayout>
</template>

<style scoped>

</style>
