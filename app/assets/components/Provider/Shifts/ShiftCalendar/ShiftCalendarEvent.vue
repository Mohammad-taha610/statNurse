<script lang="ts">
import {computed, defineComponent, PropType, Ref, ref, watch} from 'vue';
import {Calendar, EventContentArg} from 'fullcalendar';
import {Shift, ShiftCount} from '../../../../types/shifts/Shift';
import {getColorForStatus} from '../../../../utils/colors';
import EventTooltip from './ShiftPopup/ShiftPopup.vue';
import {ShiftCategory} from '../../../../types/shifts/ShiftCategory';

export default defineComponent({
  components: {EventTooltip},
  methods: {getColorForStatus},
  props: {
    shifts: {
      type: Array as PropType<Shift[]>,
      required: true,
    },
    count: {
      type: Object as PropType<ShiftCount>,
      required: true,
    },
    view: String,
    calendarApi: Calendar,
    eventContent: Object as PropType<EventContentArg>,
    isBulkSelecting: Boolean,
    onSelectShift: {
      type: Function as PropType<(shiftId: number) => void>,
      required: true,
    },
    onViewChange: {
      type: Function as PropType<() => void>,
      required: true
    },
    categories: Object as PropType<ShiftCategory[]>,
    refreshCalendar: Function as PropType<() => void>,
  },
  setup(props) {
    const openCount = props.count.Open || 0;
    const pendingCount = props.count.Pending || 0;
    const approvedCount = props.count.Approved || 0;
    const assignedCount = props.count.Assigned || 0;
    const completedCount = props.count.Completed || 0;
    const shiftCount = openCount + pendingCount + approvedCount + assignedCount + completedCount;
    const showingTooltip = ref(false);
    const currentShift = ref<Shift | undefined>();
    const selectedShifts: Ref<number[]> = ref([])
    const categories = ref(props.categories)

    const sortShiftsByCategoryAndStatus = (shifts: Shift[]) => {
      const statusOrder = ['Open', 'Pending', 'Assigned', 'Approved', 'Completed'];
      const grouped = shifts.reduce<Record<string, Shift[]>>((acc, shift) => {
        const categoryId = shift.category?.id ?? -1;
        if (!acc[categoryId]) {
          acc[categoryId] = [];
        }
        acc[categoryId].push(shift);
        return acc;
      }, {});

      Object.keys(grouped).forEach((categoryId) => {
        grouped[categoryId].sort((a, b) => {
          const orderA = statusOrder.indexOf(a.status);
          const orderB = statusOrder.indexOf(b.status);
          return orderA - orderB;
        });
      });

      const flattened: Shift[] = [];
      Object.keys(grouped).forEach((categoryId) => {
        flattened.push(...grouped[categoryId]);
      });

      return flattened;
    }

    const groupedByCategory = computed(() => {
      if (!props.shifts) {
        return {};
      }
      const byCategory: { [key: number]: Shift[] } = props.shifts.reduce((acc: { [key: number]: Shift[] }, shift) => {
        const categoryId = shift.category?.id ?? -1;

        if (!acc[categoryId]) {
          acc[categoryId] = [];
        }

        acc[categoryId].push(shift);
        return acc;
      }, {});

      // Sort by status within each category
      const orderStatus = ['Open', 'Pending', 'Assigned', 'Approved', 'Complete'];

      Object.keys(byCategory).forEach((categoryIdStr: string) => {
        const categoryId = parseInt(categoryIdStr);
        byCategory[categoryId].sort((a, b) => {
          return orderStatus.indexOf(a.status) - orderStatus.indexOf(b.status);
        });
      });

      return byCategory;
    });
    const sortedShifts = computed(() => {
      if (props.view == 'dayGridMonth') {
        return props.shifts
      } else {
        if (props.shifts) {
          return sortShiftsByCategoryAndStatus(props.shifts)
        }
      }
    });

    const counts = [
      {
        status: 'Approved',
        count: approvedCount,
      },
      {
        status: 'Assigned',
        count: assignedCount,
      },
      {
        status: 'Completed',
        count: completedCount,
      },
      {
        status: 'Open',
        count: openCount,
      },
      {
        status: 'Pending',
        count: pendingCount,
      }
    ]

    const onShiftClick = (shift: Shift) => {
      showingTooltip.value = true;
      currentShift.value = shift;
    }

    const toDayView = () => {
      props.calendarApi?.changeView('dayGridDay');
      if (props.eventContent?.event?.start) {
        props.calendarApi?.gotoDate(props.eventContent.event.start);
      }
      props.onViewChange();
    }


    return {
      selectedShifts,
      shiftCount,
      counts,
      showingTooltip,
      currentShift,
      sortedShifts,
      groupedByCategory,
      getCategoryName: (categoryId: number) => {
        if (categoryId == -1) {
          return 'Uncategorized';
        }
        return categories?.value?.find((category) => category.id == categoryId)?.name ?? ''
      },
      onShiftClick,
      toDayView,
      shiftCanBeDeleted(shift: Shift) {
        // make sure shift is > 2 hours away
        const now = new Date();
        const shiftStart = new Date(shift.start);
        const diff = shiftStart.getTime() - now.getTime();
        const hours = Math.floor(diff / 1000 / 60 / 60);
        return hours > 2;
      }
    }
  }
})

</script>

<template>

  <div class="p-1 w-full flex flex-row justify-center items-center cursor-pointer"
       style="height: 75px; max-height: 75px;"
       v-if="view == 'dayGridMonth'"
       @click="toDayView">
    <div class="w-full px-1 rounded-lg gray-bg h-full flex flex-row justify-between items-center">
      <div class="flex flex-col xl:flex-row justify-between items-center gap-2 w-full">
        <div class="rounded-full w-7 h-7 border-2 border-gray-400  flex justify-center items-center bg-white">
          <span class="text-xs font-bold">{{ shiftCount }}</span>
        </div>
        <div>
          <div v-for="count in counts">
            <div class="flex flex-row gap-1 items-center" v-if="count.count > 0">
              <div class="h-2 w-2 rounded-full" :style="'background-color: ' + getColorForStatus(count.status)"/>
              <span class="text-xs font-bold">{{ count.count }} <span
                  class="text-[10px] text-gray-500">({{ count.status }})</span></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div v-else class="flex flex-col gap-2 w-full">
    <div v-for="categoryId in Object.keys(groupedByCategory).map((id) => parseInt(id))"
         class="flex flex-col gap-2 w-full"
         :key="shifts[0].category.id">
      <span
          class="text-xs text-center rounded-md text-black underline w-full py-2">{{
          getCategoryName(categoryId)
        }}</span>
      <div v-for="shift in groupedByCategory[categoryId]"
           class="relative"
           :style="'background-color: ' + getColorForStatus(shift.status)"
           :key="shift.id"
      >
        <div
            v-if="isBulkSelecting && shiftCanBeDeleted(shift)"
            class="absolute top-0 right-0  h-full flex flex-col justify-center"
            :class="view == 'dayGridWeek' ? 'top-[-20px]' : 'top-[-15px]'"
        >
          <v-checkbox
              @click="onSelectShift(shift.id)"
              :value="shift.id"
              v-model="selectedShifts"
              hide-details
              color="white"/>
        </div>
        <button @click="onShiftClick(shift)"
                class="flex flex-col items-start gap-1 justify-between rounded-sm p-1 w-full text-ellipsis max-w-100 overflow-hidden text-wrap text-white"
        >
        <span class="text-white uppercase text-ellipsis"
              style="font-size: 10px">
            <strong class="text-ellipsis">{{ shift.startTime }}</strong>
          </span>
          <span class=" text-white uppercase text-[10px]">
            [{{ shift.nurseType }}]
        </span>
          <span class=" text-white uppercase text-[10px]">
            {{ shift.nurse?.fullName }}
        </span>
          <span class="justify-self-start text-left">
              {{ shift.provider.company }}
        </span>
        </button>
      </div>
    </div>
  </div>

  <EventTooltip :key="currentShift?.id"
                v-if="currentShift"
                @click.stop ref="tooltipRef" v-model="showingTooltip" class="tooltip"
                :shift="currentShift"
                :refresh-calendar="refreshCalendar"
  />
</template>

<style scoped>

.gray-bg {
  background-color: #EEEEEE;
}
</style>
