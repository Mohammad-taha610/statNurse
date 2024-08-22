<script lang="ts">
import {loadNursesForProvider} from '../../../../services/ProviderService';
import {defineComponent, PropType, Ref, ref, toRefs, watch} from 'vue';
import {Nurse} from '../../../../types/member/Nurse';
import {DateTime} from 'luxon';
import {Shift} from '../../../../types/shifts/Shift';
import {Provider} from '../../../../types/member/Provider';

export default defineComponent({
  props: {
    selectedNurseType: String,
    provider: Object as PropType<Provider>,
    modelValue: Object as PropType<Nurse>
  },
  setup(props, {emit}) {
    const nurseList: Ref<Nurse[]> = ref([]);
    const selectedNurse: Ref<Nurse | null> = ref(null);
    const {selectedNurseType, provider} = toRefs(props);
    watch([selectedNurseType, provider], async () => {
      if (!provider.value) return;
      nurseList.value = await loadNursesForProvider(
          selectedNurseType.value,
          null,
          null,
          provider.value.id
      );
    }, {immediate: true});

    watch(selectedNurse, (newVal) => {
      emit('update:modelValue', newVal);
    });
    return {
      nurseList,
      selectedNurse
    }
  }
})
</script>

<template>
  <v-select
      label="Nurse"
      :clearable="true"
      clear-icon="las la-times"
      persistent-clear
      prepend-icon="las la-user-nurse"
      v-model="selectedNurse"
      :items="nurseList"
      item-title="fullName"
      item-value="id"
      variant="underlined"
      return-object
  />
</template>

<style scoped>

</style>
