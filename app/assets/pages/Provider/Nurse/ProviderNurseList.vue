<template>
  <ProviderLayout>
    <NurseList :show-block-nurse="true" :provider_nurse_list="provider_nurse_list"/>
  </ProviderLayout>
</template>
<script lang="ts">


import NstMemberUser from '../../../types/member/NstMemberUser';
import {defineComponent, PropType, provide, Ref, ref, watch} from 'vue';
import ProviderLayout from '../../../components/Provider/ProviderLayout.vue';
import { ProviderNurseProp} from '../../../types/member/Provider';
import NurseList from '../../../components/Provider/Nurse/NurseList.vue';

export default defineComponent({
  components: {NurseList, ProviderLayout},
  props: {
    user: Object as PropType<NstMemberUser>,
    provider_nurse_list: {
      default: [],
      type: Object as PropType<ProviderNurseProp[]>
    }
  },
  setup(props) {
    provide('user', props.user)
  }
})
</script>
