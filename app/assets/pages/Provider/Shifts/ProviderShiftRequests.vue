<template>
  <ProviderLayout>
    <v-card class="rounded-xl px-4 py-4" elevation="0">
      <v-card-title class="border-b border-gray-300">
        Upcoming Shift Requests
      </v-card-title>
      <VDataTable :sort-by="[{key: 'date', order: 'asc'}]" :headers="headers" :items="shifts">
        <template v-slot:item.actions="{ item }">
          <v-btn icon="las la-check" @click="onApproveClick(item.selectable)" variant="plain" color="green" rounded="false" />
          <v-btn icon="las la-trash-alt" @click="onDenyClick(item.selectable)" variant="plain" color="red" />
          <a :href="item.raw.shiftRoute">
            <v-btn icon="las la-edit" variant="plain" color="blue"/>
          </a>
        </template>
      </VDataTable>
    </v-card>
  </ProviderLayout>
</template>
<script lang="ts">


import NstMemberUser from '../../../types/member/NstMemberUser';
import {defineComponent, PropType, provide, Ref, ref, watch} from 'vue';
import ProviderLayout from '../../../components/Provider/ProviderLayout.vue';
import {Shift} from '../../../types/shifts/Shift';
import {VDataTable} from 'vuetify/labs/VDataTable';
import {approveShift, denyShift} from '../../../services/ShiftService';
import {UserProp} from '../../../utils/props';
import {DateTime} from 'luxon';

export default defineComponent({
  components: {ProviderLayout, VDataTable},
  props: {
    user: UserProp,
    shifts: Object as PropType<Shift[]>
  },
  setup(props) {
    const shifts: Ref<Shift[]> = ref([]);

    watch (() => props.shifts, (newShifts) => {
      shifts.value = newShifts.map((shift: Shift) => {
        const startTime = DateTime.fromFormat(shift.startTime, 'h:mm a', {zone: 'UTC'}).toLocal().toLocaleString(DateTime.TIME_SIMPLE)
        const endTime = DateTime.fromFormat(shift.endTime, 'h:mm a', {zone: 'UTC'}).toLocal().toLocaleString(DateTime.TIME_SIMPLE);
        console.log(startTime)
        return {
          ...shift,
          startTime,
          endTime
        }
      });
    }, {immediate: true});

    const onApproveClick = async (shift: Shift) => {
      await approveShift(shift);
      shifts.value = shifts.value.filter((s: Shift) => s.id !== shift.id);
    }

    const onDenyClick = async (shift: Shift) => {
      await denyShift(shift);
      shifts.value = shifts.value.filter((s: Shift) => s.id !== shift.id);
    }
    const headers = [
      {title: 'Nurse Name', value: 'nurseName', key: 'nurseName'},
      {title: 'Start Time', value: 'startTime', key: 'startTime'},
      {title: 'End Time', value: 'endTime', key: 'endTime'},
      {title: 'Date', value: 'date', key: 'date'},
      {title: 'Facility', value: 'provider.company', key: 'provider.company'},
      {title: 'Actions', value: 'actions', key: 'actions', sortable: false},
    ]
    provide('user', props.user)
    return {
      headers,
      shifts,
      onApproveClick,
      onDenyClick
    }
  },
})
</script>
