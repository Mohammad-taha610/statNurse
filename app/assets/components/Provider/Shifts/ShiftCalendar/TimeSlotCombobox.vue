<script lang="ts">

import {computed, defineComponent, onMounted, PropType, Ref, ref, toRefs, watch} from 'vue';
import {getProviderTimeslots} from '../../../../services/ProviderService';
import {Provider} from '../../../../types/member/Provider';
import PresetShiftTime from '../../../../types/shifts/PresetShiftTime';
import {getShiftCategories} from '../../../../services/ShiftService';
import {ShiftCategory} from '../../../../types/shifts/ShiftCategory';
import VueDatePicker, {PartialTimeObj} from '@vuepic/vue-datepicker';
import TimePicker from '../../../Common/TimePicker.vue';

export default defineComponent({
  components: {TimePicker, VueDatePicker},
  props: {
    provider: {
      type: Object as PropType<Provider | null>,
      required: true
    },
    modelValue: Object as PropType<PresetShiftTime | null>
  },
  setup(props, {emit}) {
    const {modelValue, provider} = toRefs(props);
    const timeslots = ref<PresetShiftTime[]>([]);
    const categories: Ref<ShiftCategory[]> = ref([]);
    const selectedCategory = ref<ShiftCategory | null>(null);
    const customStartTime: Ref<PartialTimeObj | undefined> = ref();
    const customEndTime: Ref<PartialTimeObj | undefined> = ref();
    const customTime: Ref<PresetShiftTime> = ref(
        {
          isCustom: true,
          displayTime: '',
          id: -1,
          shiftCategory: categories.value[0]
        }
    );
    const timeslotRules = [
      (v: any) => !!v || 'Item is required',
      (v: any) => {
        if (v.isCustom) {
          return !!customStartTime.value && !!customEndTime.value || 'Start and end time are required'
        }
        return true;
      }
    ]

    const startTimeRules = [
      (v: any) => !!v || 'Start time is required',
    ]

    const endTimeRules = [
      (v: any) => !!v || 'End time is required',
    ]

    const categoryRules = [
      (v: any) => !!v || 'Category is required'
    ]

    const internalSelectedTimeslot = computed({
      get: () => {
        if (modelValue.value?.isCustom) {
          return customTime.value
        }
        return modelValue.value
      },
      set: val => {
        emit('update:modelValue', val)
      }
    });

    onMounted(async () => {
      categories.value = await getShiftCategories()
    })

    watch(() => props.modelValue, (newTimeSlot, oldTimeslot) => {
      // if it goes from not custom to custom, reset times
      if ((oldTimeslot?.isCustom === undefined || !oldTimeslot.isCustom) && newTimeSlot?.isCustom == true) {
        customStartTime.value = undefined;
        customEndTime.value = undefined;
      }
    }, {immediate: true});
    watch([customStartTime, customEndTime, selectedCategory], ([startTime, endTime]) => {
      if (startTime == null || endTime == null) {
        return;
      }
      // construct display time
      // format it to 00:00
      const getNumberFromPartialObj = (maybeNumber: string | number | undefined): number => {
        if (maybeNumber == undefined) {
          return 0
        }
        if (typeof maybeNumber == 'string') {
          return parseInt(maybeNumber)
        }
        return maybeNumber
      }

      const startHours = getNumberFromPartialObj(startTime.hours)
      const startMinutes = getNumberFromPartialObj(startTime.minutes)
      const endHours = getNumberFromPartialObj(endTime.hours)
      const endMinutes = getNumberFromPartialObj(endTime.minutes)

      const startDisplay = `${startHours.toString().padStart(2, '0')}:${startMinutes.toString().padStart(2, '0')}`
      const endDisplay = `${endHours.toString().padStart(2, '0')}:${endMinutes.toString().padStart(2, '0')}`
      const displayTime = `${startDisplay} - ${endDisplay}`;
      console.log(displayTime)
      if (startTime && endTime) {
        customTime.value = {
          isCustom: true,
          displayTime: displayTime,
          shiftCategory: selectedCategory.value ?? categories.value[0]
        } as PresetShiftTime
        emit('update:modelValue', customTime.value)
      }
    })

    watch(provider, async (provider) => {
      if (provider) {
        const presetShiftTimes = await getProviderTimeslots(provider.id);
        timeslots.value = [...presetShiftTimes, {displayTime: 'Custom', isCustom: true} as PresetShiftTime]
      }
    }, {immediate: true})

    return {
      timeslots,
      internalSelectedTimeslot,
      categories,
      customTime,
      customStartTime,
      customEndTime,
      timeslotRules,
      startTimeRules,
      endTimeRules,
      categoryRules
    }
  }
})
</script>

<template>
  <v-select
      class="w-full"
      label="Select timeslot"
      prepend-icon="las la-calendar"
      :clearable="true"
      clear-icon="las la-times"
      :rules="timeslotRules"
      persistent-clear
      v-model="internalSelectedTimeslot"
      :item-title="(item) => {
        if (item.isCustom == true) {
          return 'Custom'
        }
        return item.displayTime
      }"
      :items="timeslots ?? []"
      variant="underlined"
      return-object
  />
  <div class="flex flex-col" v-if="internalSelectedTimeslot?.isCustom == true">
    <v-select
        :items="categories"
        item-title="name"
        variant="underlined"
        prepend-icon="las la-tag"
        item-value="id"
        :rules="categoryRules"
        label="Category"
    />
    <TimePicker label="Start Time" v-model="customStartTime" />
    <div class="my-2"></div>
    <TimePicker label="End Time" v-model="customEndTime" />
  </div>
</template>

<style>

</style>
