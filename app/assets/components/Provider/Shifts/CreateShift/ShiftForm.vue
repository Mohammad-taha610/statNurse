<script lang="ts">
import {defineComponent, PropType, ref, Ref, watch} from 'vue';
import {ShiftCategory} from '../../../../types/shifts/ShiftCategory';
import {loadNursesForProvider} from '../../../../services/ProviderService';
import {DateTime} from 'luxon';
import NstMemberUser from '../../../../types/member/NstMemberUser';
import {Provider} from '../../../../types/member/Provider';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import {VDatePicker} from 'vuetify/labs/VDatePicker';
import {Nurse} from '../../../../types/member/Nurse';
import {Shift} from '../../../../types/shifts/Shift';
import ShiftRecurrenceDropdown from './ShiftRecurrenceDropdown.vue';
import {ShiftRecurrenceValue} from '../../../../types/types';
import {createShift} from '../../../../services/ShiftService';

export default defineComponent({
  components: {
    ShiftRecurrenceDropdown,
    VueDatePicker,
    VDatePicker
  },
  props: {
    shift: {
      type: Object as PropType<Shift | undefined>,
      required: false
    },
    categories: {
      type: Array as PropType<ShiftCategory[]>,
      required: true
    },
    user: {
      type: Object as PropType<NstMemberUser>,
      required: true
    },
    isEdit: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const menu = ref(false);
    const date: Ref<Date | undefined> = ref();
    const selectedCategory: Ref<ShiftCategory | null> = ref(null);
    const selectedNurseType = ref('');
    const onlyOneProvider = props.user.providers.length === 1;
    const selectedProvider: Ref<Provider | null> = ref(onlyOneProvider ? props.user.providers[0] : null);
    const nurseList: Ref<Nurse[]> = ref([]);
    const bonusAmount = ref(0);
    const bonusDescription = ref('');
    const description = ref('');
    const selectedNurse: Ref<Nurse | null> = ref(null);
    const numberOfCopies = ref(1);
    const selectedIncentive = ref('None');
    const shiftRecurrence: Ref<ShiftRecurrenceValue> = ref({
      selectedDays: [],
      customDates: [],
      endDate: '',
      selectedRecurrenceType: 'None'
    });

    const startTime = ref({
      hours: 0,
      minutes: 0
    });
    const endTime = ref({
      hours: 0,
      minutes: 0
    });

    if (props.isEdit && props.shift) {
      const initialStartTime = DateTime.fromISO(props.shift.start, {zone: 'UTC'}).toLocal();
      const initialEndTime = DateTime.fromISO(props.shift.end, {zone: 'UTC'}).toLocal();
      date.value = DateTime.fromISO(props.shift.date).toLocal().toJSDate();
      startTime.value = {
        hours: initialStartTime.hour,
        minutes: initialStartTime.minute
      };
      endTime.value = {
        hours: initialEndTime.hour,
        minutes: initialEndTime.minute
      };
      selectedCategory.value = props.shift.category;
      selectedNurseType.value = props.shift.nurseType;
      selectedProvider.value = props.shift.provider;
      bonusAmount.value = props.shift.bonus;
      bonusDescription.value = props.shift.bonusDescription;
      description.value = props.shift.description;
      selectedNurse.value = props.shift.nurse;
      (async() => {
        nurseList.value = await loadNursesForProvider(
            selectedNurseType.value,
            initialStartTime.toISO() || '',
            initialEndTime.toISO() || '',
            props.shift?.provider?.id || 0
        );
        selectedNurse.value = props.shift?.nurse || null;
      })()

    }

    const getRecurrenceData = () => {
      let recurrenceData: any = {};
      recurrenceData.recurrence_interval = 1
      recurrenceData.recurrence_type = shiftRecurrence.value.selectedRecurrenceType;
      if (recurrenceData.recurrence_type == 'None') {
        return recurrenceData;
      }
      else if (recurrenceData.recurrence_type == 'Weekly' || recurrenceData.recurrence_type == 'Daily') {
        recurrenceData.recurrence_options = shiftRecurrence.value.selectedDays.map(day => day.toUpperCase().substring(0, 2));
      }
      else if (recurrenceData.recurrence_type == 'Custom') {
        recurrenceData.recurrence_custom_dates = shiftRecurrence.value.customDates.map(date => DateTime.fromISO(date.toISOString()).toUTC().toFormat('yyyy-MM-dd'));
      }
      recurrenceData.recurrence_end_date = DateTime.fromISO(shiftRecurrence.value.endDate.toISOString()).toUTC().toFormat('yyyy-MM-dd');
      return recurrenceData;
    }

    const onSaveShift = async () => {
      const parsedDateTime = DateTime.fromISO(date.value?.toISOString() ?? '');
      const startDateTime = parsedDateTime.set(
          {
            hour: parseInt(startTime.value.hours.toString()),
            minute: parseInt(startTime.value.minutes.toString())
          }
      );
      const endDateTime = parsedDateTime.set(
          {
            hour: parseInt(endTime.value.hours.toString()),
            minute: parseInt(endTime.value.minutes.toString())
          }
      );
      const recurrenceData = getRecurrenceData();

      const data = {
        id: props.shift?.id,
        //name: null,
        start_time: startDateTime.toFormat('HH:mm'),
        end_time: endDateTime.toFormat('HH:mm'),
        start_date: parsedDateTime.toFormat('yyyy-MM-dd'),
        // TODO end_date,
        // TODO end_date_enabled,
        end_date_enabled: false,
        nurse_type: selectedNurseType.value,
        bonus_amount: bonusAmount.value,
        bonus_description: bonusDescription.value,
        ...recurrenceData,
        // TODO recurrence custom dates
        description: description.value,
        category_id: selectedCategory.value?.id || 0,
        nurse_id: selectedNurse.value?.id || undefined,
        // TODO approve nurse
        approve_nurse: false,
        deny_nurse: false,
        nurse_changed: props.isEdit && selectedNurse.value?.id != props.shift?.nurse?.id,
        number_of_copies: numberOfCopies.value,
        incentive: selectedIncentive.value == 'None' ? 1 : selectedIncentive.value == '1.5x' ? 1.5 : 2,
        is_covid: 'Yes',
        is_copy: 'No',
        action_type: props.isEdit ? '' : 'create',
        provider_id: selectedProvider.value?.id || 0,
      }
      await createShift(data)
      // reload this page
      window.location.reload();
    }


    const nurseTypes = ['CNA', 'CMT', 'CMT/LPN/RN', 'LPN/RN'];
    const recurrenceTypes = ['None', 'Daily', 'Weekly', 'Custom'];

    watch([selectedProvider, date, startTime, endTime, selectedNurseType],
        async () => {
          if (!selectedNurseType.value || !date.value || !startTime.value || !endTime.value || !selectedProvider.value) {
            return;
          }
          const parsedDateTime = new DateTime(date.value);
          const startDateTime = parsedDateTime.set(
              {
                hour: parseInt(startTime.value.hours.toString()),
                minute: parseInt(startTime.value.minutes.toString())
              }
          );
          const endDateTime = parsedDateTime.set(
              {
                hour: parseInt(endTime.value.hours.toString()),
                minute: parseInt(endTime.value.minutes.toString())
              }
          );
          nurseList.value = await loadNursesForProvider(
              selectedNurseType.value,
              startDateTime.toISO() || '',
              endDateTime.toISO() || '',
              selectedProvider.value?.id || 0
          );
        });

    return {
      newDate: new Date().toISOString().substr(0, 10),
      menu,
      date,
      selectedCategory,
      selectedNurseType,
      selectedProvider,
      nurseTypes,
      recurrenceTypes,
      startTime,
      endTime,
      onlyOneProvider,
      nurseList,
      onSaveShift,
      bonusAmount,
      bonusDescription,
      description,
      selectedNurse,
      numberOfCopies,
      selectedIncentive,
      shiftRecurrence
    }
  }
});

</script>

<template>
  <v-sheet rounded="5px" class="p-5 m-5 rounded-lg" elevation="3">
    <h3 class="text-h3 mb-5">Create Shift</h3>
    <v-form class="flex flex-column gap-2">
      <div class="flex flex-row gap-2">
        <div class="w-1/2">
          <VueDatePicker
              time-picker minutes-increment="15"
              :start-time="{hours: 0, minutes: 0}"
              minutes-grid-increment="15"
              :is24="false"
              :disabled="isEdit"
              v-model="startTime"/>
        </div>
        <div class="w-1/2">
          <VueDatePicker
              time-picker minutes-increment="15"
              :start-time="{hours: 0, minutes: 0}"
              :disabled="isEdit"
              minutes-grid-increment="15"
              :is24="false"
              v-model="endTime"/>
        </div>
      </div>
      <v-menu
          v-model="menu"
          :close-on-content-click="false"
          :return-value.sync="date"
          activator="#menu-activator"
      >
        <template v-slot:activator="{ on, attrs }">
          <v-text-field
              id="menu-activator"
              :disabled="isEdit"
              v-model="date"
              label="Date"
              variant="underlined"
              prepend-icon="las la-calendar"
              readonly
              v-bind="attrs"
              @click="menu = true"
              v-on="on"
          ></v-text-field>
        </template>
        <v-date-picker
            v-if="menu"
            v-model="date"
            :disabled="isEdit"
            @click:save="menu = false"
            @click:cancel="menu=false"
            @input="menu = false"
            landscape="true"
            input-mode="keyboard"
            variant="flat"
            title="Date"
            expand-icon="las la-calendar"
            elevation="1"
            keyboard-icon="las la-keyboard"
            calendar-icon="las la-calendar"
            locale="en"
            class=""/>
      </v-menu>
      <div class="flex flex-row w-full gap-2">
        <v-combobox
            label="Category"
            :items="categories"
            :disabled="isEdit"
            v-model="selectedCategory"
            item-title="name"
            item-value="id"
            variant="underlined"
            prepend-icon="las la-user-clock"
            class="w-1/2"
        ></v-combobox>
        <v-combobox
            label="Nurse Type"
            :disabled="isEdit"
            v-model="selectedNurseType"
            prepend-icon="las la-user-tag"
            :items="nurseTypes"
            variant="underlined"
            class="w-1/2"
        ></v-combobox>
      </div>
      <div class="flex flex-row w-full gap-2">
        <v-text-field :disabled="isEdit" type="number" label="Bonus" v-model="bonusAmount" variant="underlined" prepend-icon="las la-dollar-sign"/>
        <div class="w-3/4">
          <v-text-field :disabled="isEdit" type="text" label="Bonus Description" v-model="bonusDescription" variant="underlined"/>
        </div>
      </div>
      <div class="flex flex-row w-full gap-2">
        <!-- TODO load categories and nurse types -->
        <v-combobox
            label="Covid Unit"
            :disabled="isEdit"
            :items="['No', 'Yes']"
            variant="underlined"
            class="w-1/2"
            prepend-icon="las la-exclamation-triangle"
        ></v-combobox>
        <v-combobox
            label="Incentive"
            :disabled="isEdit"
            v-model="selectedIncentive"
            prepend-icon="las la-money-bill"
            :items="['None', '1.5x', '2x']"
            class="w-1/2"
            variant="underlined"
        ></v-combobox>
      </div>
      <ShiftRecurrenceDropdown v-model="shiftRecurrence" />
      <div>
        <v-text-field :disabled="isEdit" type="text" label="Description" v-model="description" variant="underlined" prepend-icon="las la-info"/>
      </div>
      <div>
        <v-combobox
            :disabled="onlyOneProvider || isEdit"
            label="Provider"
            prepend-icon="las la-user-nurse"
            v-model="selectedProvider"
            item-title="company"
            item-value="id"
            :items="user.providers"
            class="w-1/2"
            variant="underlined"
        />
      </div>
      <div>
        <v-combobox
            label="Nurse"
            :clearable="true"
            clear-icon="las la-times"
            persistent-clear
            prepend-icon="las la-user-nurse"
            v-model="selectedNurse"
            :items="nurseList"
            item-title="fullName"
            item-value="id"
            class="w-1/2"
            variant="underlined"
        />
      </div>
      <div>
        <v-text-field :disabled="isEdit" type="number" label="Number of Copies" v-model="numberOfCopies" variant="underlined" prepend-icon="las la-copy"/>
      </div>
      <div class="flex flex-row justify-center gap-3">
        <v-btn @click="onSaveShift" color="success" class="w-1/6" large>Save Shift</v-btn>
        <v-btn color="red" class="w-1/6" large>Reset</v-btn>
      </div>
    </v-form>
  </v-sheet>
</template>

<style scoped>
</style>
