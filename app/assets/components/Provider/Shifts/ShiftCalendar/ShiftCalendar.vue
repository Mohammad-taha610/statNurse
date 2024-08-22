<script lang="ts">
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin, {DateClickArg} from '@fullcalendar/interaction';
import {getColorForStatus} from '../../../../utils/colors';
import {CalendarEvent, DateShifts, Shift, ShiftCount, ShiftsAndCountAggregate} from '../../../../types/shifts/Shift';
import {CalendarOptions, DatesSetArg, EventHoveringArg} from '@fullcalendar/core';
import {bulkDeleteShifts, getShiftCategories, getShiftsInDateRange} from '../../../../services/ShiftService';
import {CalendarApi, EventClickArg, EventContentArg} from 'fullcalendar';
import {defineComponent, h, inject, onMounted, ref, Ref, watch} from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import EventTooltip from './ShiftPopup/ShiftPopup.vue';
import CreateShiftPopup from './CreateShiftPopup.vue';
import {Nurse} from '../../../../types/member/Nurse';
import FilterMenu from './FilterMenu.vue';
import {Filters} from '../../../../types/types';
import ShiftCalendarEvent from './ShiftCalendarEvent.vue';
import {DateTime} from 'luxon';
import ProviderCombobox from '../../../Common/ProviderCombobox.vue';
import NstMemberUser from '../../../../types/member/NstMemberUser';
import {Provider} from '../../../../types/member/Provider';
import ConfirmationDialog from './ShiftPopup/ConfirmationDialog.vue';
import {ShiftCategory} from '../../../../types/shifts/ShiftCategory';

export default defineComponent({
  methods: {getColorForStatus},
  components: {ConfirmationDialog, ProviderCombobox, FilterMenu, CreateShiftPopup, EventTooltip, FullCalendar},
  setup(props) {
    const filters: Ref<Filters> = ref({});
    const tooltipRef: Ref<InstanceType<typeof EventTooltip> | null> = ref(null);
    const tooltipX = ref(0);
    const tooltipY = ref(0);
    const showingTooltip = ref(false);
    const currentShift: Ref<Shift | null> = ref(null);
    const shiftStatuses = ['Open', 'Pending', 'Assigned', 'Approved', 'Completed']
    const selectedShiftStatuses: Ref<string[]> = ref([]);
    const nurses: Ref<Nurse[]> = ref([]);
    const shifts = ref<DateShifts>();
    const events: Ref<CalendarEvent[]> = ref([]);
    const user: NstMemberUser | undefined = inject('user');
    const providerFilter: Ref<Provider | undefined> =
        ref(undefined);
    const isLoading = ref(false);
    const isBulkSelecting = ref(false);
    const shouldShowBulkSelect = ref(false);
    const selectedShifts: Ref<Set<number>> = ref(new Set<number>());
    const showDeleteConfirmation = ref(false);
    const categories: Ref<ShiftCategory[]> = ref([]);

    watch(providerFilter, (newVal) => {
      filters.value = {...filters.value, provider: newVal};
    }, {immediate: true});
    const filterByStatus = (unfilteredShifts: DateShifts, statuses: string[]) => {
      if (isMonthView()) {
        const filteredDateShifts: DateShifts = {};
        Object.keys(unfilteredShifts).forEach((date) => {
          const filteredShifts = unfilteredShifts[date];
          // remove the counts that don't match status
          const selectedStatuses = selectedShiftStatuses.value;
          let filteredShiftCount = {
            ...filteredShifts.count
          } as ShiftCount;
          if (selectedStatuses.includes('Open')) {
            // delete key
            delete filteredShiftCount.Open;
          }
          if (selectedStatuses.includes('Pending')) {
            delete filteredShiftCount.Pending;
          }

          if (selectedStatuses.includes('Assigned')) {
            delete filteredShiftCount.Assigned;
          }
          if (selectedStatuses.includes('Approved')) {
            delete filteredShiftCount.Approved;
          }
          if (selectedStatuses.includes('Completed')) {
            delete filteredShiftCount.Completed;
          }
          filteredDateShifts[date] = {
            ...filteredShifts,
            count: filteredShiftCount
          } as ShiftsAndCountAggregate;

        });
        return filteredDateShifts;
      } else {
        const filteredDateShifts: DateShifts = {};

        Object.keys(unfilteredShifts).forEach((date) => {
          const filteredShifts = unfilteredShifts[date].shifts.filter((shift) =>
              selectedShiftStatuses.value.length == 0 ||
              !selectedShiftStatuses.value.includes(shift.status));

          if (filteredShifts.length > 0) {
            filteredDateShifts[date] = {...unfilteredShifts[date], shifts: filteredShifts} as ShiftsAndCountAggregate;
          }
        });

        return filteredDateShifts;
      }
    }
    const toggleStatus = (status: string) => {
      if (selectedShiftStatuses.value.includes(status)) {
        selectedShiftStatuses.value = selectedShiftStatuses.value.filter((s) => s != status);
      } else {
        selectedShiftStatuses.value = [...selectedShiftStatuses.value, status];
      }

      if (shifts.value) {
        events.value =
            mapDateShiftsToCalendarEvents(
                filterByStatus(shifts.value, selectedShiftStatuses.value)
            );
      }
    }

    const mapDateShiftsToCalendarEvents = (dateShifts: DateShifts) => {
      if (!dateShifts) {
        return [];
      }

      return Object.keys(dateShifts).map((date) => {
        const day = dateShifts[date];
        return mapShiftAggregateToCalendarEvents(day, date);
      });
    }

    const mapShiftAggregateToCalendarEvents = (shiftsAgg: ShiftsAndCountAggregate, date: string) => {
      const title = Object.keys(shiftsAgg.count)
          .map((status) => {
            const statusKey = status as keyof ShiftCount;
            const count = shiftsAgg.count[statusKey] || 0;
            return `${status}: ${count}`;
          })
          .join(', ');

      return {
        title, // Aggregated title for all statuses in the day
        date: date, // Date of the event
        count: shiftsAgg.count, // Count of shifts by status
        shifts: shiftsAgg.shifts, // Additional data
      } as CalendarEvent;
    }

    watch(events, (newVal) => {
      fullCalendarRef.value.getApi().setOption('events', newVal);
    });

    const fullCalendarRef = ref();
    const isDayView = () => {
      return fullCalendarRef.value?.getApi().view.type == 'timeGridDay';
    }

    const isMonthView = () => {
      return fullCalendarRef.value?.getApi().view.type == 'dayGridMonth';
    }

    const fullCalendarApi: Ref<CalendarApi | null> = ref(null);
    const title = ref(fullCalendarApi.value?.view.title)

    onMounted(async () => {
      categories.value = await getShiftCategories();
      fullCalendarApi.value = fullCalendarRef.value.getApi();
      title.value = fullCalendarApi.value?.view.title;
    });

    const getNurseListFromShifts = (dateShifts: DateShifts) => {
      const uniqueNurseIds = new Set();
      return Object.keys(dateShifts).flatMap((date) => {
        const shifts = dateShifts[date].shifts;
        return shifts.reduce<Nurse[]>((uniqueNurses, shift) => {
          if (shift.nurse) {
            const nurseId = shift.nurse.id;
            if (!uniqueNurseIds.has(nurseId)) {
              uniqueNurseIds.add(nurseId);
              return [...uniqueNurses, shift.nurse]
            }
          }
          return uniqueNurses;
        }, []);
      });
    }

    const getCalendarModeFromViewType = (viewType: string) => {
      switch (viewType) {
        case 'dayGridMonth':
          return 'month';
        case 'dayGridWeek':
          return 'week';
        case 'dayGridDay':
          return 'day';
        default:
          return 'month';
      }
    }
    const fetchShifts = async (start: Date, end: Date) => {
      isLoading.value = true;
      const calendarMode = getCalendarModeFromViewType(fullCalendarApi.value?.view.type ?? 'dayGridMonth');
      const days = await getShiftsInDateRange(start.toISOString(), end.toISOString(), filters.value, calendarMode);
      // what mode is the calendar in?
      shifts.value = days;

      if (calendarMode == 'month') {
        events.value = mapDateShiftsToCalendarEvents(days);
        isLoading.value = false;
        return;
      }
      const filteredShifts = filterByStatus(days, selectedShiftStatuses.value);
      events.value = mapDateShiftsToCalendarEvents(filteredShifts);
      nurses.value = getNurseListFromShifts(days)
      isLoading.value = false;
    }

    const renderShiftCalendarEvent = (arg: EventContentArg) => {
      return h(ShiftCalendarEvent, {
        eventContent: arg,
        calendarApi: fullCalendarRef.value.getApi(),
        shifts: arg.event.extendedProps.shifts,
        count: arg.event.extendedProps.count,
        view: fullCalendarRef.value.getApi().view.type,
        isBulkSelecting: isBulkSelecting.value,
        categories,
        refreshCalendar: refreshCalendar,
        onSelectShift: (id: number) => {
          if (selectedShifts.value.has(id)) {
            selectedShifts.value.delete(id);
          } else {
            selectedShifts.value.add(id);
          }
        },
        onViewChange: () => {
          title.value = fullCalendarApi.value?.view.title;
          shouldShowBulkSelect.value = true
        }
      })
    }

    watch([isBulkSelecting, categories], () => {
      // re-render the calendar events with updates props
      calendarOptions.value.eventContent = (arg) => renderShiftCalendarEvent(arg);
    });

    const calendarOptions: Ref<CalendarOptions> = ref({
      firstDay: 1,
      plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
      initialView: 'dayGridMonth',
      contentHeight: '600px',
      expandRows: true,
      fixedWeekCount: false,
      events: events.value,
      eventContent: renderShiftCalendarEvent,
      headerToolbar: false,
      dateClick: (info: DateClickArg) => {
        shouldShowBulkSelect.value = true;
        if (isDayView()) {
          return;
        }
        fullCalendarRef.value.getApi().changeView('dayGridDay', info.dateStr);
      },
      eventDisplay: 'list-item',
      datesSet: (info: DatesSetArg) => {
        const {start, end} = info;

        const currentStart = fullCalendarApi.value?.view.currentStart;
        const currentEnd = fullCalendarApi.value?.view.currentEnd;
        const startUTC = DateTime.fromISO(start.toISOString()).toUTC().toJSDate()
        const endUTC = DateTime.fromISO(end.toISOString()).toUTC().toJSDate()

        fetchShifts(currentStart ?? startUTC, currentEnd ?? endUTC);
      },
      eventClick: () => {
        shouldShowBulkSelect.value = true;
      }
    });
    const onNextClicked = () => {
      fullCalendarApi.value?.next();
      title.value = fullCalendarApi.value?.view.title;
    }

    const onPrevClicked = () => {
      fullCalendarApi.value?.prev();
      title.value = fullCalendarApi.value?.view.title;
    }

    const goToToday = () => {
      shouldShowBulkSelect.value = true;
      fullCalendarApi.value?.today();
      title.value = fullCalendarApi.value?.view.title;
    }
    const monthViewClick = () => {
      shouldShowBulkSelect.value = false;
      fullCalendarApi.value?.changeView('dayGridMonth')
      title.value = fullCalendarApi.value?.view.title;
    }

    const weekViewClick = () => {
      shouldShowBulkSelect.value = true;
      fullCalendarApi.value?.changeView('dayGridWeek')
      title.value = fullCalendarApi.value?.view.title;
    }

    const dayViewClick = () => {
      shouldShowBulkSelect.value = true;
      fullCalendarApi.value?.changeView('dayGridDay')
      refreshCalendar()
    }

    const refreshCalendar = () => {
      const start = fullCalendarApi.value?.view.currentStart;
      const end = fullCalendarApi.value?.view.currentEnd;
      if (start && end) {
        fetchShifts(start, end);
      }
    }
    const refreshCalendarClick = () => {
      refreshCalendar();
    }

    watch(filters, (newVal) => {
      refreshCalendar()
    });

    const deleteSelectedShifts = async () => {
      isLoading.value = true;
      await bulkDeleteShifts(
          Array.from(selectedShifts.value)
      )
      isLoading.value = false;
      showDeleteConfirmation.value = false;
      selectedShifts.value = new Set<number>();
      refreshCalendar()
    }

    return {
      calendarOptions,
      fullCalendarRef,
      fullCalendarApi,
      tooltipX,
      tooltipY,
      showingTooltip,
      tooltipRef,
      currentShift,
      title,
      nurses,
      isBulkSelecting,
      filters,
      selectedShiftStatuses,
      shiftStatuses,
      user,
      providerFilter,
      shouldShowBulkSelect,
      showDeleteConfirmation,
      selectedShifts,
      deleteSelectedShifts,
      onNextClicked,
      onPrevClicked,
      goToToday,
      monthViewClick,
      weekViewClick,
      dayViewClick,
      refreshCalendarClick,
      toggleStatus,
      refreshCalendar,
      isLoading
    };
  },
})
</script>

<template>
  <v-sheet
      class="p-12 rounded-xl relative z-0">
    <div class="absolute z-10" style="left: 50%; top: 50%" v-if="isLoading">
      <v-progress-circular
          indeterminate
          color="red"
      ></v-progress-circular>
    </div>

    <div class="flex flex-col mb-5 items-start gap-3 md:flex-wrap">
      <div class="flex flex-row items-center justify-between w-full">
        <div class="flex flex-row gap-3 ml-2">
          <span class="text-xl font-bold">{{ title }}</span>
          <button class="text-md" @click="onPrevClicked">
            <v-icon>mdi mdi-chevron-left</v-icon>
          </button>
          <button class="text-md" @click="onNextClicked">
            <v-icon>mdi mdi-chevron-right</v-icon>
          </button>
        </div>
        <div class="flex w-1/2" v-if="user != undefined">
          <ProviderCombobox :user="user" v-model="providerFilter"/>
        </div>
      </div>
      <div class="flex flex-row justify-end items-center w-full gap-1">
        <v-btn variant="outlined" class="text-sm" @click="goToToday">TODAY</v-btn>
        <CreateShiftPopup :refresh-calendar="refreshCalendar"/>
        <v-btn variant="outlined" @click="refreshCalendarClick">
          <v-icon color="black">las la-sync-alt</v-icon>
        </v-btn>
        <FilterMenu v-model="filters" :nurses="nurses"/>
        <v-menu offset-y>
          <template v-slot:activator="{props}">
            <v-btn variant="outlined" append-icon="mdi mdi-chevron-down" v-bind="props">
              {{
                fullCalendarApi?.view.type == 'dayGridMonth' ? 'Month' : fullCalendarApi?.view.type == 'dayGridWeek' ? 'Week' : 'Day'
              }}
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="monthViewClick">
              <v-list-item-title>Month</v-list-item-title>
            </v-list-item>
            <v-list-item @click="weekViewClick">
              <v-list-item-title>Week</v-list-item-title>
            </v-list-item>
            <v-list-item @click="dayViewClick">
              <v-list-item-title>Day</v-list-item-title>
            </v-list-item>
          </v-list>
        </v-menu>
        <v-btn v-if="shouldShowBulkSelect" variant="outlined" :color="isBulkSelecting ? 'red' : 'black'"
               @click="isBulkSelecting = !isBulkSelecting">Bulk Select
        </v-btn>
        <ConfirmationDialog :isActive.sync="showDeleteConfirmation" width="300" headerColor="red">
          <template #activator="{ props, on }">
            <v-btn v-if="isBulkSelecting && fullCalendarApi?.view.type != 'dayGridMonth' && selectedShifts.size > 0 "
                   v-bind="props" v-on="on" @click="showDeleteConfirmation = true" variant="outlined"
                   class="text-red">
              <span class="font-semibold">Delete {{selectedShifts.size}} shift{{selectedShifts.size > 1 ? 's' : ''}}</span>
            </v-btn>
          </template>

          <template #title>
            <span class="text-2xl">Are you sure?</span>
          </template>

          <template #content>
            <span>Do you wish to <span class="text-red font-bold">DELETE</span> the selected shifts? This action cannot be undone.</span>
          </template>

          <template #actions>
            <div class="flex flex-row gap-2 justify-end">
              <v-btn variant="flat" @click="showDeleteConfirmation = false" class="">
                <span class="font-semibold">Cancel</span>
              </v-btn>
              <v-btn variant="flat" @click="deleteSelectedShifts" class="text-red">
                <span class="font-semibold">Yes, delete</span>
              </v-btn>
            </div>
          </template>
        </ConfirmationDialog>
      </div>
    </div>
    <FullCalendar
        ref="fullCalendarRef"
        :options="calendarOptions"
    />

    <div class="mt-5 flex flex-row justify-center gap-1 md:gap-12">
      <button v-for="status in shiftStatuses" @click="toggleStatus(status)">
        <span class="uppercase text-sm font-semibold tracking-wider"
              :style="'color: ' + (selectedShiftStatuses.includes(status) ? 'gray;' : getColorForStatus(status))">
          {{ status }}
        </span>
      </button>
    </div>
  </v-sheet>
</template>

<style scoped>

</style>
