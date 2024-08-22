<template>
  <div class="text-center">
    <v-dialog
        v-model="showDialog"
        width="auto"
    >
      <template v-slot:activator="{ props }"/>

      <v-card width="450px">
        <div class="flex flex-row justify-between py-3 px-3" :style="{'background-color': color}">
          <span class="text-white text-xl">
            [{{ shift?.nurseType }}] - {{ shift?.nurse?.fullName }}
          </span>
          <span v-if="shift?.status != 'Open'" class="text-white text-xl">
          </span>
        </div>
        <v-card-text class="flex flex-column gap-y-1 p-5">
          <span class="text-2xl font-bold" :style="{'color': color}">
            {{ shift?.status }}
          </span>
          <span>{{shift.provider.company}}</span>
          <!-- todo send date in a better way -->
          <span class="text-lg">{{ formattedStart }} - {{ formattedEnd }}</span>

          <a :href="shift?.nurseRoute">
            <span v-if="shift?.nurse">{{ shift?.nurse?.fullName }}</span>
            <v-icon class="text-gray-500 text-xl">las la-user-nurse</v-icon>
          </a>
          <span class="text-gray-500 text-sm">Bonus: {{ shift?.bonus > 0 ? '$' + shift?.bonus : 'None' }}</span>
          <span class="text-gray-500 text-sm">Covid: {{ shift?.isCovid ? 'Yes' : 'No' }}</span>
          <span
              class="text-gray-500 text-sm">Incentive: {{
              shift?.incentive > 1 ? shift?.incentive + 'x' : 'None'
            }}</span>
        </v-card-text>
        <v-card-actions>
          <div class="flex flex-col gap-3 justify-between w-full px-3">
            <div
                v-if="shiftStartsAfterTwoHours(shift)"
                 class="flex flex-row justify-between w-full">
              <v-btn variant="flat" @click="showDialog = false" class="w-1/4">
                <span class="font-semibold">
                  Cancel
                </span>
              </v-btn>
              <ConfirmationDialog :isActive.sync="showDeleteConfirmation" width="300" headerColor="red">
                <template #activator="{ props, on }">
                  <v-btn v-bind="props" v-on="on" @click="showDeleteConfirmation = true" variant="flat"
                         class="w-1/4 text-red">
                    <span class="font-semibold">Delete</span>
                  </v-btn>
                </template>

                <template #title>
                  <span class="text-2xl">Are you sure?</span>
                </template>

                <template #content>
                  <span>Do you wish to <span class="text-red font-bold">DELETE</span> this shift? This action cannot be undone.</span>
                </template>

                <template #actions>
                  <div class="flex flex-row gap-2 justify-end">
                    <v-btn variant="flat" @click="showDeleteConfirmation = false" class="">
                      <span class="font-semibold">Cancel</span>
                    </v-btn>
                    <v-btn variant="flat" @click="deleteShiftClick(shift)" class="text-red">
                      <span class="font-semibold">Yes, delete</span>
                    </v-btn>
                  </div>
                </template>
              </ConfirmationDialog>
            </div>
            <div v-if="shift.status == 'Pending'" class="w-full flex flex-row">
              <ConfirmationDialog :isActive.sync="showApproveConfirmation" width="300"
                                  :headerColor="colors.shiftApproved">
                <template #activator="{ props, on }">
                  <v-btn :color="colors.shiftApproved" v-bind="props" v-on="on" @click="showApproveConfirmation = true"
                         variant="flat" class="w-full text-white">
                    <span class="font-semibold">Approve</span>
                  </v-btn>
                </template>

                <template #title>
                  <span class="text-2xl">Approve Shift</span>
                </template>

                <template #content>
                  <span>Do you wish to <span class="font-bold" :style="'color: ' + colors.shiftApproved">APPROVE</span> {{ shift.nurse.fullName }} for the requested shift?</span>
                </template>

                <template #actions>
                  <div class="flex flex-row gap-2 justify-end">
                    <v-btn variant="flat" @click="showApproveConfirmation = false" class="">
                      <span class="font-semibold">Cancel</span>
                    </v-btn>
                    <v-btn variant="flat" @click="approveShiftClick(shift)" :style="'color: ' + colors.shiftApproved">
                      <span class="font-semibold">Yes, approve</span>
                    </v-btn>
                  </div>
                </template>
              </ConfirmationDialog>
            </div>
          </div>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script lang="ts">

import {computed, defineComponent, PropType, ref, watch} from 'vue';
import {colors, getColorForStatus} from '../../../../../utils/colors';
import {Shift} from '../../../../../types/shifts/Shift';
import {formatDate, useDateFormat} from '@vueuse/shared';
import {approveShift, deleteShift, denyShift} from '../../../../../services/ShiftService';
import ConfirmationDialog from './ConfirmationDialog.vue';


export default defineComponent({
  components: {ConfirmationDialog},
  computed: {
    colors() {
      return colors
    }
  },
  methods: {formatDate, useDateFormat},
  setup(props, {emit}) {
    const shift = computed(() => props.shift)
    const showDialog = ref(props.modelValue);

    const formatDateString = (date: string) => {
      if (date == undefined) return '';
      const dateObject = new Date(date);
      return formatDate(dateObject, 'h:mm a')
    }

    const color = computed(() => {
      return getColorForStatus(shift.value?.status || 'open')
    })

    const formattedStart = ref('')
    const formattedEnd = ref('')
    watch(shift, (newShift: Shift) => {
      formattedStart.value = formatDateString(newShift?.start)
      formattedEnd.value = formatDateString(newShift?.end)
    }, {deep: true, immediate: true})

    watch(() => props.modelValue, (newVal) => {
      showDialog.value = newVal;
    })

    watch(() => showDialog.value, (newVal) => {
      emit('update:modelValue', newVal);
    })

    const deleteShiftClick = async (shift: Shift) => {
      await deleteShift(shift)
      props.refreshCalendar();
    }

    const approveShiftClick = async (shift: Shift) => {
      await approveShift(shift)
      props.refreshCalendar();
    }

    const denyShiftClick = async (shift: Shift) => {
      await denyShift(shift)
      props.refreshCalendar();
    }

    const shiftStartsAfterTwoHours = (shift: Shift) => {
      const twoHoursFromNow = new Date()
      twoHoursFromNow.setHours(twoHoursFromNow.getHours() + 2)
      return new Date(shift.start) > twoHoursFromNow
    }

    return {
      color,
      showDialog,
      formattedStart,
      formattedEnd,
      deleteShiftClick,
      approveShiftClick,
      denyShiftClick,
      showDeleteConfirmation: ref(false),
      showApproveConfirmation: ref(false),
      showDenyConfirmation: ref(false),
      shiftStartsAfterTwoHours
    }
  },
  props: {
    shift: {
      type: Object as PropType<Shift>,
      required: true
    },
    modelValue: {
      type: Boolean,
      required: true
    },
    x: {
      type: Number,
      required: true
    },
    y: {
      type: Number,
      required: true
    },
    refreshCalendar: {
      type: Function,
      required: true
    }
  },
})
</script>
