<template>
  <div>
    <ProviderLayout>
      <v-container class="">
        <ShiftCalendar :user="user"/>
      </v-container>
    </ProviderLayout>
  </div>
</template>

<script lang="ts">


import NstMemberUser from '../../../types/member/NstMemberUser';
import {defineComponent, PropType, provide} from 'vue';
import ShiftCalendar from '../../../components/Provider/Shifts/ShiftCalendar/ShiftCalendar.vue';
import ProviderLayout from '../../../components/Provider/ProviderLayout.vue';
import {UserProp} from '../../../utils/props';

export default defineComponent({
  components: {ShiftCalendar, ProviderLayout},
  props: {
    user: UserProp
  },
  setup(props) {
    provide('user', props.user)
  }
})
</script>
