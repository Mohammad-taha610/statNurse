<script lang="ts">
import VueDatePicker from '@vuepic/vue-datepicker';
import {defineComponent, PropType, ref} from 'vue';
import {requestPayrollChange} from '../../../services/PayrollService';
import {PayrollPayment} from '../../../types/payroll/PayrollPayment';
import {DateTime} from 'luxon';

export default defineComponent({
  components: {
    VueDatePicker
  },
  props: {
    payment: Object as PropType<PayrollPayment>
  },
  setup(props) {
    if (!props.payment) {
      return {}
    }
    const start = DateTime.fromFormat(props.payment?.actualClockIn, 'hh:mm a')
    const end = DateTime.fromFormat(props.payment?.actualClockOut, 'hh:mm a')

    const startTime = ref({
      hours: start.hour,
      minutes: start.minute
    });
    const endTime = ref({
      hours: end.hour,
      minutes: end.minute
    });
    const description = ref("")

    const submitRequest = async () => {
      const clockInUtc = DateTime.fromObject({
        hour: startTime.value.hours,
        minute: startTime.value.minutes,
      }).toUTC().toISO().toString()

      const clockOutUtc = DateTime.fromObject({
        hour: endTime.value.hours,
        minute: endTime.value.minutes,
      }).toUTC().toISO().toString()

      await requestPayrollChange(
          props.payment?.id,
          description.value,
          clockInUtc,
          clockOutUtc
      )
      window.location.reload()
    }

    return {
      startTime,
      endTime,
      description,
      submitRequest
    }
  }
})
</script>

<template>
  <v-dialog width="500">
    <template v-slot:activator="{ props }">
      <v-btn v-bind="props" text="Request Change"></v-btn>
    </template>

    <template v-slot:default="{ isActive }">
      <v-card style=" overflow: visible">
        <v-card-title class="bg-red px-5 py-5">
          <span class="text-3xl">Request Change</span>
        </v-card-title>
        <v-card-text>
          <span class="text-sm text-gray-500">
            Please enter a description below of which details of this payment should be changed and why.
          </span>
          <div class="mb-1 mt-5">
            <span class="text-xs">Start Time</span>
          </div>
          <div class="mb-2">
            <VueDatePicker
                :transitions="{
                  menuAppearTop: true
                }"
                time-picker
                :start-time="{hours: 0, minutes: 0}"
                :is24="false"
                v-model="startTime"/>
          </div>
          <div class="mb-1 mt-3">
            <span class="text-xs">End Time</span>
          </div>
          <div class="mb-3">
            <VueDatePicker
                time-picker
                :start-time="{hours: 0, minutes: 0}"
                :is24="false"
                v-model="endTime"/>
          </div>
          <div class="mb-1 mt-3">
            <span class="text-xs">Description (Required)</span>
          </div>
          <v-text-field type="text" variant="outlined" label="Description" class="mb-2" v-model="description"/>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
              text="Cancel"
              @click="isActive.value = false"
          />
          <v-btn
              text="Submit Request"
              @click="submitRequest"
              color="red"
          />
        </v-card-actions>
      </v-card>
    </template>
  </v-dialog>
</template>

<style scoped>

</style>
