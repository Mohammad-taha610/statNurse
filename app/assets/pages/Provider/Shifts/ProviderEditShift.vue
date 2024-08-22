<script lang="ts">
import ProviderLayout from '../../../components/Provider/ProviderLayout.vue';
import {defineComponent, PropType, provide} from 'vue';
import {ShiftCategory} from '../../../types/shifts/ShiftCategory';
import ShiftForm from '../../../components/Provider/Shifts/CreateShift/ShiftForm.vue';
import {Shift} from '../../../types/shifts/Shift';
import {UserProp} from '../../../utils/props';

export default defineComponent({
  components: {
    ShiftForm,
    ProviderLayout
  },
  props: {
    user: UserProp,
    categories: Array as PropType<ShiftCategory[]>,
    shift: Object as PropType<Shift>
  },
  setup(props) {
    provide('user', props.user)
  }
})
</script>

<template>
  <ProviderLayout>
    <ShiftForm :is-edit="true" :shift="shift" :categories="categories ?? []" :user="user" />
  </ProviderLayout>
</template>

<style scoped>

</style>
