<script lang="ts">
import {computed, defineComponent, inject, onMounted, PropType, ref, Ref, toRefs, watch} from 'vue';
import {Nurse} from '../../../../types/member/Nurse';
import {Filters} from '../../../../types/types';
import {Provider} from '../../../../types/member/Provider';
import NstMemberUser from '../../../../types/member/NstMemberUser';
import {ShiftCategory} from '../../../../types/shifts/ShiftCategory';
import {getShiftCategories} from '../../../../services/ShiftService';

export default defineComponent({
  props: {
    nurses: Array<Nurse>,
    modelValue: {
      type: Object as PropType<Filters>,
      required: true
    }
  },
  setup(props, {emit}) {
    const selectedNurse: Ref<Nurse | undefined> = ref();
    const filters: Ref<Filters> = ref(props.modelValue)
    const selectedNurseType: Ref<string | undefined> = ref();
    const nurseTypes = ['CNA', 'CMT', 'LPN/RN', 'CMT/LPN/RN'];
    const categories: Ref<ShiftCategory[]> = ref([]);
    const selectedCategory: Ref<ShiftCategory | undefined> = ref();

    onMounted(async () => {
      categories.value = await getShiftCategories()
    })

    watch(() => props.modelValue, (newFilters) => {
      filters.value = newFilters
    })

    watch([selectedNurse, selectedNurseType, selectedCategory],
        ([newNurse, newNurseType, newSelectedCategory]) => {
          const newFilters = {
            provider: filters.value.provider,
            nurse: newNurse,
            credential: newNurseType,
            category: newSelectedCategory
          }
          emit('update:modelValue', newFilters)
        });

    return {
      selectedNurse,
      selectedNurseType,
      selectedCategory,
      nurseTypes,
      categories,
    }
  }
})

</script>

<template>
  <v-menu offset-y width="300" :close-on-content-click="false">
    <template v-slot:activator="{props}">
      <v-btn variant="outlined" append-icon="mdi mdi-chevron-down" v-bind="props">
        FILTERS
      </v-btn>
    </template>
    <v-list>
      <v-list-item>
        <v-combobox
            v-model="selectedNurse"
            label="Nurse"
            :items="nurses"
            item-title="fullName"
            clear-icon="mdi mdi-close"
            :clearable="true"
            item-value="id"
            variant="underlined"
            placeholder="Nurse"
            small-chips
            solo
            dense
            outlined
            hide-selected
            return-object
        />
      </v-list-item>
      <v-list-item>
        <v-combobox
            v-model="selectedNurseType"
            label="Nurse type"
            :items="nurseTypes"
            clear-icon="mdi mdi-close"
            :clearable="true"
            variant="underlined"
            placeholder="Nurse type"
            small-chips
            solo
            dense
            outlined
            hide-selected
            return-object
        />
      </v-list-item>
      <v-list-item>
        <v-combobox
            v-model="selectedCategory"
            label="Category"
            :items="categories"
            item-title="name"
            clear-icon="mdi mdi-close"
            :clearable="true"
            item-value="id"
            variant="underlined"
            placeholder="Category"
            small-chips
            solo
            dense
            outlined
            hide-selected
            return-object
        />
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<style scoped>

</style>
