<script lang="ts">
import ProviderLayout from '../../../components/Provider/ProviderLayout.vue';
import {defineComponent, PropType, provide} from 'vue';
import NstMemberUser from '../../../types/member/NstMemberUser';
import {ShiftCategory} from '../../../types/shifts/ShiftCategory';
import ShiftForm from '../../../components/Provider/Shifts/CreateShift/ShiftForm.vue';

export default defineComponent({
  components: {
    ShiftForm,
    ProviderLayout
  },
  props: {
    user: Object as PropType<NstMemberUser>,
    categories: Array as PropType<ShiftCategory[]>
  },
  setup(props) {
    provide('user', props.user)
  }
})
</script>

<template>
  <ProviderLayout>
    <ShiftForm :categories="categories" :user="user" />
  </ProviderLayout>
</template>

<style scoped>

</style>
