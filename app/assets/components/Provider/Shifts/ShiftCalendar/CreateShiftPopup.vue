<script lang="ts">
import {defineComponent, inject, onMounted, PropType, Ref, ref, watch} from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import ProviderCombobox from '../../../Common/ProviderCombobox.vue';
import NstMemberUser from '../../../../types/member/NstMemberUser';
import {Provider} from '../../../../types/member/Provider';
import TimeSlotMenu from './TimeSlotCombobox.vue';
import PresetShiftTime from '../../../../types/shifts/PresetShiftTime';
import PremiumRateMenu from './PremiumRateCombobox.vue';
import TimeSlotCombobox from './TimeSlotCombobox.vue';
import NurseSelectionMenu from './NurseSelectionCombobox.vue';
import {Nurse} from '../../../../types/member/Nurse';
import {DateTime} from 'luxon';
import {getProviderCredentials, getProviderPayRates, providerCreateShift} from '../../../../services/ProviderService';
import NurseCredential from '../../../../types/member/NurseCredential';
import colors from '../../../../utils/colors';
import {VForm} from "vuetify/components";
import {Rates} from '../../../../types/payroll/Rates';
import premiumRateCombobox from './PremiumRateCombobox.vue';
import {formatCurrency} from '../../../../utils/currency';

interface CalculatedPayRate {
  credential: string,
  rate: number,
}

export default defineComponent({
  methods: {formatCurrency},
  computed: {
    colors() {
      return colors
    }
  },
  props: {
    refreshCalendar: Function as PropType<() => void>,
  },
  components: {NurseSelectionMenu, TimeSlotCombobox, PremiumRateMenu, TimeSlotMenu, ProviderCombobox, VueDatePicker},
  setup(props) {
    const user: NstMemberUser | undefined = inject('user')
    const selectedPremiumRate = ref({text: 'None', value: 1});
    const selectedTimeslot: Ref<PresetShiftTime | null> = ref(null);
    const selectedProvider: Ref<Provider | null> = ref(null);
    const selectedNurse: Ref<Nurse | undefined> = ref();
    const isCovid = ref(false);
    const dates = ref<Date[]>([]);
    const numberOfCopies = ref(1);
    const firstCalendarStartDate = DateTime.local().startOf('month').toJSDate();
    const secondCalendarStartDate = DateTime.local().startOf('month').plus({month: 1}).toJSDate();
    const firstMonthName = DateTime.local().startOf('month').toFormat('MMMM');
    const secondMonthName = DateTime.local().startOf('month').plus({month: 1}).toFormat('MMMM');
    const form: Ref<VForm | null> = ref(null)
    const dialog = ref(false)
    const snackbar = ref(false)
    const payRates: Ref<Rates | null> = ref(null);
    const selectedCredential: Ref<string> = ref('CNA')
    const calculatedPayRates: Ref<CalculatedPayRate[]> = ref([]);

    const refreshCalendar = () => {
      if (props.refreshCalendar != null) {
        props.refreshCalendar()
      }
    }

    const createShift = async () => {
      if (dates.value.length == 0) {
        alert("You must select at least one date")
        return
      }

      if (selectedProvider.value == null) {
        alert("You must select a provider")
        return
      }

      await form.value?.validate();
      const isFormValid = form.value?.isValid

      if (!isFormValid) {
        return
      }
      const days = dates.value.map((date: Date) => {
        return DateTime.fromISO(date.toISOString()).toUTC().toFormat('yyyy-MM-dd');
      })

      const [startTime, endTime] = selectedTimeslot.value?.displayTime.split(' - ') ?? [];
      const data = {
        shift: {
          dates: days,
          provider_id: selectedProvider.value?.id,
          credential: selectedCredential.value,
          selectedTime: {
            id: selectedTimeslot.value?.id,
            start_time: startTime.replace('AM', '').replace('PM', ''),
            end_time: endTime.replace('AM', '').replace('PM', ''),
            human_readable: selectedTimeslot.value?.displayTime,
            text: selectedTimeslot.value?.displayTime,
            category_id: selectedTimeslot.value?.shiftCategory.id,
          },
          isCovid: isCovid.value,
          premiumRate: {
            text: selectedPremiumRate.value.text,
            value: selectedPremiumRate.value.value,
          },
          nurse: {
            value: selectedNurse.value?.id,
            text: selectedNurse.value?.fullName,
            type: selectedNurse.value?.credentials
          },
          copies: numberOfCopies.value,
        }
      }

      try {
        const [status, res] = await providerCreateShift(data)
        if (status == 200) {
          refreshCalendar()
          dialog.value = false;
          //window.location.reload()
        } else {
          console.log(res)
        }
      } catch (e) {
        // show toast
        snackbar.value = true;
      }
    }

    const clearDates = () => {
      dates.value = [];
    }


    const credentialTypes: Ref<NurseCredential[]> = ref([]);
    watch(selectedProvider, async (newVal) => {
      if (newVal) {
        payRates.value = await getProviderPayRates(newVal.id);
        console.log(payRates.value)
        credentialTypes.value = await getProviderCredentials(newVal.id)
      }
    }, {immediate: true});

    const onCredentialClicked = (credential: NurseCredential) => {
      selectedCredential.value = credential.name;
    }

    watch(dialog, (newVal) => {
      if (newVal) {
        selectedProvider.value = null;
        clearDates()
      }
    });

    // calculate actual rates for shift
    watch([payRates, selectedPremiumRate, selectedCredential], ([newPayRate, newPremiumRate, newSelectedCredential]) => {
      if (!newPayRate) return;
      const currentCredential = selectedCredential.value;
      const currentPremiumRate = selectedPremiumRate.value.value;
      let allRates: CalculatedPayRate[] = [];
      if (currentCredential.includes('CNA')) {
        allRates.push(
            {
              credential: 'CNA',
              rate: newPayRate.CNA.standard_bill * currentPremiumRate,
            } as CalculatedPayRate
        )
      }
      if (currentCredential.includes('CMT')) {
        allRates.push(
            {
              credential: 'CMT',
              rate: newPayRate.CMT.standard_bill * currentPremiumRate,
            } as CalculatedPayRate
        )
      }

      if (currentCredential.includes('LPN')) {
        allRates.push(
            {
              credential: 'LPN',
              rate: newPayRate.LPN.standard_bill * currentPremiumRate,
            } as CalculatedPayRate
        )
      }

      if (currentCredential.includes('RN')) {
        allRates.push(
            {
              credential: 'RN',
              rate: newPayRate.RN.standard_bill * currentPremiumRate,
            } as CalculatedPayRate
        )
      }

      calculatedPayRates.value = allRates;
    });

    return {
      onCredentialClicked,
      createShift,
      dates,
      credentialTypes,
      selectedCredential,
      selectedProvider,
      selectedTimeslot,
      selectedPremiumRate,
      selectedNurse,
      numberOfCopies,
      isCovid,
      user,
      firstCalendarStartDate,
      secondCalendarStartDate,
      firstMonthName,
      secondMonthName,
      dialog,
      calculatedPayRates,
      clearDates,
      snackbar,
      form,
      numberOfCopiesRules: [
        (value: any) => {
          if (!value) return 'You must enter the number of copies'
        },
        (value: any) => {
          if (value < 1) return 'You must have at least 1 copy'
        }
      ],
    }
  }
})

</script>

<template>
  <v-dialog v-model="dialog" width="580" height="700" class="overflow-scroll">
    <template v-slot:activator="{ props }">
      <v-btn v-bind="props" text="CREATE SHIFT" variant="outlined">
        <v-icon :color="colors.shiftApproved" class="mr-1">mdi mdi-plus-circle</v-icon>
        <span>CREATE SHIFT</span>
      </v-btn>
    </template>

    <template v-slot:default="{ isActive }">
      <v-card class="overflow-scroll">
        <div class="p-5 bg-red">
          <v-card-title>
            <span class="text-xl">
              Create Shift
            </span>
          </v-card-title>
        </div>
        <v-card-text>
          <v-form ref="form" validate-on="submit lazy" @submit.prevent="createShift">
            <div class="flex flex-col justify-start w-fit">
              <div class="flex flex-row w-full gap-2">
                <div class="flex flex-col items-center">
                  <span class="text-xl font-semibold text-[#3d4465]">{{ firstMonthName }}</span>
                  <VueDatePicker
                      v-model="dates"
                      multi-dates
                      :enable-time-picker="false"
                      :inline="true"
                      calendar-class-name=""
                      menu-class-name="border-0"
                      day-class-name="bg-red-500"
                      :day-class="() => 'text-xs'"
                      disable-month-year-select
                      :start-date="firstCalendarStartDate"
                      auto-apply
                      :month-change-on-scroll="false"
                  />
                </div>
                <div class="flex flex-col items-center">
                  <span class="text-xl font-semibold text-[#3d4465]">{{ secondMonthName }}</span>
                  <VueDatePicker
                      v-model="dates"
                      multi-dates
                      :enable-time-picker="false"
                      :inline="true"
                      calendar-class-name=""
                      menu-class-name="border-0"
                      day-class-name="bg-red-500"
                      :day-class="() => 'text-xs'"
                      disable-month-year-select
                      :start-date="secondCalendarStartDate"
                      :month-change-on-scroll="false"
                      auto-apply
                  />
                </div>
              </div>
            </div>
            <div class="flex flex-row mt-5 gap-3">
              <button v-for="credential in credentialTypes" @click.prevent="onCredentialClicked(credential)">
                <v-chip :color="selectedCredential == credential.name ? 'red' : 'default'">
                  {{ credential.name }}
                </v-chip>
              </button>
            </div>
            <div class="mt-5">
              <ProviderCombobox v-model="selectedProvider" :user="user"/>
            </div>
            <div v-if="selectedCredential != null && selectedProvider != null" class="mt-5">
              <div class="grid grid-cols-2  gap-4 auto-rows-min">
                <div class="flex flex-col">
                  <TimeSlotCombobox :provider="selectedProvider" v-model="selectedTimeslot"/>
                </div>
                <div>
                  <PremiumRateMenu v-model="selectedPremiumRate"/>
                </div>
                <div>
                  <NurseSelectionMenu
                      :provider="selectedProvider"
                      :selectedNurseType="selectedCredential ?? ''"
                      v-model="selectedNurse"
                  />
                </div>
                <div>
                  <v-text-field type="number"
                                label="Number of Copies"
                                v-model="numberOfCopies"
                                :rules="numberOfCopiesRules"
                                :min="1"
                                variant="underlined" prepend-icon="las la-copy"/>
                </div>
              </div>
              <!--<v-checkbox label="Is Covid" v-model="isCovid"/>-->
              <div class="flex flex-col">
                <span class="text-sm">Hourly Rates</span>
                <div v-for="payRate in calculatedPayRates">
                  <span class="text-xs" :class="selectedPremiumRate.value != 1 ? 'text-red-500 font-bold' : ''">{{payRate.credential}} {{formatCurrency(payRate.rate)}}</span>
                </div>
              </div>
            </div>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                  text="Close"
                  @click="clearDates(); isActive.value = false"
              />
              <v-btn
                  color="green"
                  type="submit"
                  variant="tonal"
                  text="Submit"/>
            </v-card-actions>
            <!--@click="createShift();"/-->
          </v-form>
        </v-card-text>

      </v-card>
    </template>
  </v-dialog>
  <v-snackbar
      v-model="snackbar"
      multi-line
      variant="elevated"
      color="red"
      class="mb-12"
      timeout="2000"
  >
    A shift for this nurse already exists on one more of the selected dates.
  </v-snackbar>
</template>

<style scoped>

</style>
