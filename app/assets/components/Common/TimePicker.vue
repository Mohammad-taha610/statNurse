<script lang="ts">
import {computed, defineComponent, PropType} from 'vue';
import {PartialTimeObj} from '@vuepic/vue-datepicker';

export default defineComponent({
  props: {
    modelValue: Object as PropType<PartialTimeObj>,
    label: String
  },
  setup(props, {emit}) {
    const updateModelValue = (value: any) => {
      emit('update:modelValue', value)
    }
    const internalTime = computed({
      get: () => props.modelValue,
      set: (time) => {
        updateModelValue(time)
      }
    })

    return {
      updateModelValue,
      internalTime
    }
  }
})

</script>

<template>
  <div class="flex flex-row items-center">
    <v-icon class="text-gray-500 mr-3">las la-clock</v-icon>
    <div class="flex flex-col">
      <label class="text-xs text-gray-500">{{ label }}</label>
      <VueDatePicker
          v-model="internalTime"
          time-picker
          minutes-increment="15"
          :start-time="{hours: 0, minutes: 0}"
          :transitions="{
            menuAppearTop:true
          }"
          hide-input-icon
          mode-height="125"
          :is24="false"
          input-class-name="time-picker pl-0 pr-12"
      >
      </VueDatePicker>
    </div>
  </div>
</template>

<style>
.time-picker {
  border-top: none;
  border-right: none;
  border-left: none;
  border-radius: 0;
  @apply border-gray-500;
}
</style>
