<script lang="ts">
import {defineComponent, PropType, ref, watch, computed, toRefs} from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import type {ShiftRecurrenceValue} from '../../../../types/types';

export default defineComponent({
  components: {VueDatePicker},
  props: {
    modelValue: {
      type: Object as PropType<ShiftRecurrenceValue>,
      default: () => ({
        selectedDays: [],
        customDates: [],
        endDate: Date.now(),
        selectedRecurrenceType: 'None'
      })
    }
  },
  setup(props, {emit}) {
    const { modelValue } = toRefs(props);

    const selectedRecurrenceType = ref(modelValue.value.selectedRecurrenceType)
    const selectedDays = ref(modelValue.value.selectedDays)
    const customDates = ref(modelValue.value.customDates)
    const endDate = ref(modelValue.value.endDate)

    const shouldShowCalendar = computed(() =>
        selectedRecurrenceType.value === 'Weekly' ||
        selectedRecurrenceType.value === 'Daily'
    );

    watch(
        () => [selectedRecurrenceType.value, selectedDays.value, customDates.value, endDate.value],
        () => {
          emit('update:modelValue', {
            selectedDays: selectedDays.value,
            customDates: customDates.value,
            endDate: endDate.value,
            selectedRecurrenceType: selectedRecurrenceType.value
          });
        }
    );

    const days = [
      'Sun',
      'Mon',
      'Tue',
      'Wed',
      'Thu',
      'Fri',
      'Sat'
    ]

    return {
      shouldShowCalendar,
      selectedRecurrenceType,
      selectedDays,
      days,
      customDates,
      endDate
    }
  }
})
</script>


<template>
  <div class="flex flex-col">
    <div class="flex flex-row items-center">
      <div class="w-1/2">
        <v-combobox
            label="Recurrence Type"
            :items="['None', 'Daily', 'Weekly', 'Custom']"
            v-model="selectedRecurrenceType"
            variant="underlined"
            class="w-full"
            prepend-icon="las la-redo-alt"
        />
      </div>
      <div v-if="selectedRecurrenceType == 'Weekly'" class="w-1/2">
        <v-combobox
            label="Recurrence Options"
            :items="days"
            v-model="selectedDays"
            variant="underlined"
            class="w-full"
            prepend-icon="las la-list"
            :multiple="true"
        />
      </div>
      <div v-else-if="selectedRecurrenceType == 'Custom'" class="w-1/2 ml-1">
        <v-input prepend-icon="las la-calendar" hide-details >
          <VueDatePicker
              v-model="customDates"
              hide-input-icon
              multi-dates
              input-class-name="border-0 border-b"
              :enable-time-picker="false"
              :day-class="() => 'text-xs'"
              :month-change-on-scroll="false"
          />
        </v-input>
      </div>
    </div>
    <div v-if="shouldShowCalendar" class="w-1/2">
      <v-input label="End Date" prepend-icon="las la-calendar">
        <div class="flex flex-col w-full">
          <span class="text-xs text-gray-600">Recurrence End Date</span>
          <VueDatePicker
              v-model="endDate"
              hide-input-icon
              input-class-name="border-0 border-b"
              :enable-time-picker="false"
              :day-class="() => 'text-xs'"
              :month-change-on-scroll="false"
          />
        </div>
      </v-input>
    </div>
  </div>

</template>

<style scoped>

</style>
