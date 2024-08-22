<script lang="ts">
import {defineComponent, inject, PropType, Ref, ref, watch} from 'vue';
import {Shift} from '../../../types/shifts/Shift';
import {VDataTable} from 'vuetify/labs/VDataTable'
import {DateTime} from 'luxon';
import {getUpcomingShifts} from '../../../services/ProviderService';
import {Provider} from '../../../types/member/Provider';
import ProviderCombobox from '../../Common/ProviderCombobox.vue';
import NstMemberUser from '../../../types/member/NstMemberUser';

export default defineComponent({
  name: 'UpcomingShiftsTable',
  props: {
    shifts: {
      type: Array as PropType<Shift[]>,
      default: [],
      required: true,
    },
    totalPages: {
      type: Number,
      default: 0,
      required: true,
    },
  },
  components: {
    ProviderCombobox,
    VDataTable,
  },
  setup(props) {
    const page = ref(1)
    const upcomingShifts = ref(props.shifts)
    const loading = ref(false)
    const shiftsInLocalTz: Ref<Shift[]> = ref([])
    const totalPages = ref(props.totalPages)
    const providerFilter: Ref<Provider | null> = ref(null)

    watch(upcomingShifts, (val) => {
          shiftsInLocalTz.value = upcomingShifts.value.map(shift => {
            const startTime = DateTime.fromISO(shift.start)
            const endTime = DateTime.fromISO(shift.end)
            const date = DateTime.fromISO(shift.start)
            return {
              ...shift,
              startTime: startTime.toFormat('hh:mm a'),
              endTime: endTime.toFormat('hh:mm a'),
              date: date.toLocal().toFormat('yyyy-MM-dd'),
            }
          })
        },
        {immediate: true}
    )

    watch([page, providerFilter], async ([newPage, newProviderFilter], [oldPage, oldProviderFilter]) => {
      loading.value = true;
      const upcomingShiftData = await getUpcomingShifts(newPage, providerFilter.value);
      upcomingShifts.value = upcomingShiftData.shifts;
      totalPages.value = upcomingShiftData.totalPages;
      if (newProviderFilter?.id !== oldProviderFilter?.id) {
        page.value = 1;
      }
      loading.value = false;
    }, {immediate: true});

    const user: NstMemberUser | undefined = inject('user')
    return {
      shifts: shiftsInLocalTz,
      headers: [
        {title: 'Nurse', value: 'nurse_name', key: 'nurse_name', sortable: true},
        {title: 'Date', value: 'date', key: 'date'},
        {title: 'Start Time', value: 'startTime', key: 'startTime'},
        {title: 'End Time', value: 'endTime', key: 'endTime'},
        {title: 'Facility', value: 'provider.company', key: 'provider.company'},
        /**{title: 'Actions', value: 'actions', key: 'actions'}**/
      ],
      updatePagination: (pagination: any) => console.log(pagination),
      loading,
      page,
      totalPages,
      providerFilter,
      user
    }
  },
});
</script>

<template>
  <v-container>
    <v-card class="rounded-xl px-4 py-4 relative z-0" elevation="0">
      <div class="absolute top-1/2 left-1/2 z-10">
        <v-progress-circular
            v-if="loading"
            indeterminate
            size="64"
            color="red"/>
      </div>
      <v-card-title class="border-b border-gray-300">
        <div class=" flex flex-row justify-between items-center">
          <span class="text-xl font-bold">
            Upcoming Shifts
          </span>
          <div class="w-1/2 mt-2">
            <ProviderCombobox :user="user" v-model="providerFilter"/>
          </div>
        </div>
      </v-card-title>
      <!-- @vue-ignore -->
      <VDataTable
          @update:pagination="updatePagination"
          :headers="headers"
          hide-default-footer
          :items="shifts"
          :items-per-page="10"
      >
        <template v-slot:item.nurse_name="{ item }">
          <a :href="item.columns.nurse">
            {{ item.raw.nurse?.fullName ?? 'Unassigned' }}
          </a>
        </template>
        <!--template v-slot:item.actions="{ item }">
          <a :href="item.raw.shiftRoute">
            <v-icon :to="item['actions']" color="blue">las la-edit</v-icon>
          </a>
        </template-->
        <template v-slot:bottom>
          <div class="text-center pt-2">
            <v-pagination
                v-model="page"
                :length="totalPages"
            ></v-pagination>
          </div>
        </template>
      </VDataTable>
    </v-card>
  </v-container>
</template>


<style scoped>

</style>
