<script lang="ts">
import {computed, defineComponent, onMounted, PropType, provide, Ref, ref, watch} from 'vue';
import {UserProp} from '../../utils/props';
import ProviderLayout from '../../components/Provider/ProviderLayout.vue';
import PayPeriod from '../../types/shifts/PayPeriod';
import {getNursePayrollPayments, getPayrollPayments} from '../../services/PayrollService';
import ProviderCombobox from '../../components/Common/ProviderCombobox.vue';
import {Provider} from '../../types/member/Provider';
import ShiftPaymentsTable from '../../components/Provider/CurrentPayPeriod/ShiftPaymentsTable.vue';
import NursePaymentsTable from '../../components/Provider/CurrentPayPeriod/NursePaymentsTable.vue';
import {PaymentElement, PayrollPayment} from '../../types/payroll/PayrollPayment';
import NursePayrollPayment from '../../types/payroll/NursePayrollPayment';
import {DateTime} from 'luxon';

export default defineComponent({
  components: {NursePaymentsTable, ShiftPaymentsTable, ProviderCombobox, ProviderLayout},
  props: {
    user: UserProp,
    payPeriods: Array as PropType<PayPeriod[]>
  },
  setup(props) {
    provide('user', props.user)
    const selectedPayPeriod: Ref<PayPeriod | undefined> = ref(props.payPeriods?.[0]);
    const providerFilter: Ref<Provider | null> = ref(null)
    const tab = ref("shifts")
    const payments: Ref<PayrollPayment[]> = ref([])
    const filteredPayments: Ref<PayrollPayment[]> = ref([])
    const nursePayments: Ref<NursePayrollPayment[]> = ref([])
    const filteredNursePayments: Ref<NursePayrollPayment[]> = ref([])
    const unresolvedOnly: Ref<boolean> = ref(false)
    const days: Ref<DateTime[]> = ref([])
    const selectedDay: Ref<DateTime | undefined> = ref(undefined)
    const isLoading = ref(false)

    watch ([selectedPayPeriod, unresolvedOnly], async () => {
      isLoading.value = true
      if (selectedPayPeriod.value) {
        payments.value = await getPayrollPayments(selectedPayPeriod.value.start, selectedPayPeriod.value.end, unresolvedOnly.value)
        nursePayments.value = await getNursePayrollPayments(selectedPayPeriod.value.start, selectedPayPeriod.value.end, unresolvedOnly.value)
        const start = new Date(selectedPayPeriod.value.start)
        const end = new Date(selectedPayPeriod.value.end)
        end.setDate(end.getDate() + 1)

        const daysArray = []
        for (let day = start; day <= end; day.setDate(day.getDate() + 1)) {
          daysArray.push(DateTime.fromISO(day.toISOString()))
        }
        days.value = daysArray
      }
      isLoading.value = false
    }, {immediate: true})

    watch([providerFilter, payments, selectedDay], async () => {
      if (providerFilter.value == null) {
        filteredPayments.value = payments.value
      }
      else {
        filteredPayments.value = payments.value.filter((payment) => {
          return payment.provider.id === providerFilter.value?.id
        })
      }
       if (selectedDay.value) {
         filteredPayments.value = filteredPayments.value.filter((payment) => {
           return payment.date === selectedDay.value?.toFormat('yyyy-MM-dd')
         })
       }
    }, {immediate: true})

    watch([providerFilter, nursePayments], async () => {
      if (providerFilter.value == null) {
        filteredNursePayments.value = nursePayments.value
      }
      else {
        filteredNursePayments.value = nursePayments.value.filter((payment) => {
          return payment.provider.id === providerFilter.value?.id
        })
      }
    }, {immediate: true})

    const shouldAllowRequestChange = computed(() => {
      const payPeriod = selectedPayPeriod.value;
      const payPeriods = props.payPeriods;
      if (payPeriods) {
        const index = payPeriods.findIndex((period) => {
          return period.start === payPeriod?.start && period.end === payPeriod?.end
        })
        return index < 3
      }
      return false
    })

    return {
      tab,
      days,
      providerFilter,
      selectedDay,
      selectedPayPeriod,
      filteredPayments,
      filteredNursePayments,
      unresolvedOnly,
      shouldAllowRequestChange,
      isLoading
    }
  }
})

</script>

<template>
  <ProviderLayout>
    <v-card class="p-5 rounded-xl" elevation="5">
      <div class="p-5">
        <div class="flex flex-row flex-wrap">
          <v-card-title>
            Pay Period
          </v-card-title>
          <v-spacer/>
          <div class="mt-2 flex flex-col items-end w-full md:w-2/3 lg:w-1/2">
            <ProviderCombobox v-model="providerFilter" :user="user"/>
            <v-combobox
                label="Pay Period"
                variant="underlined"
                v-model="selectedPayPeriod"
                prepend-icon="las la-calendar-week"
                :items="payPeriods"
                return-object
                item-title="display"
                class="w-full mt-3"
            >
            </v-combobox>
            <v-combobox
                label="Day"
                variant="underlined"
                v-model="selectedDay"
                prepend-icon="las la-calendar-week"
                :items="days"
                return-object
                class="w-full"
                :clearable="true"
                clear-icon="las la-times"
                :item-title="(item: any) => item?.toFormat('MM/dd/yyyy')"
            >
            </v-combobox>
            <v-checkbox color="red" label="Unresolved Payments Only" v-model="unresolvedOnly"></v-checkbox>
          </div>
        </div>
      </div>
      <div class="w-full border-b border-gray-800"/>
      <div class="p-5">
        <v-tabs
            v-model="tab"
            color="red"
            align-tabs="start"
        >
          <v-tab value="shifts">Shifts</v-tab>
          <v-tab value="nurses">Nurses</v-tab>
        </v-tabs>
        <v-window v-model="tab">
          <div class="absolute top-1/2 left-1/2">
            <v-progress-circular v-if="isLoading" color="red" size="64" indeterminate></v-progress-circular>
          </div>
          <v-window-item value="shifts">
            <ShiftPaymentsTable :shouldAllowRequestChange="shouldAllowRequestChange" :payments="filteredPayments" />
          </v-window-item>
          <v-window-item value="nurses">
            <NursePaymentsTable :payments="filteredNursePayments" />
          </v-window-item>
        </v-window>
      </div>
    </v-card>
  </ProviderLayout>
</template>

<style scoped>

</style>
