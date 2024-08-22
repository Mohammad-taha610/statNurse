<script lang="ts">
import {defineComponent, PropType, provide} from 'vue';
import {Nurse} from '../../../types/member/Nurse';
import ProviderLayout from '../../../components/Provider/ProviderLayout.vue';
import NurseDnrList from '../../../components/Provider/Nurse/NurseDnrList.vue';
import {ProviderNurseProp} from '../../../types/member/Provider';
import NstMemberUser from '../../../types/member/NstMemberUser';

export default defineComponent({
  components: {NurseDnrList, ProviderLayout},
  props: {
    user: Object as PropType<NstMemberUser>,
    provider_nurse_list: Array as PropType<ProviderNurseProp[]>,
  },
  setup(props) {
    provide('user', props.user)
    return {}
  }
})

</script>

<template>
  <ProviderLayout>
    <NurseDnrList :provider_nurse_list="provider_nurse_list"/>
  </ProviderLayout>
</template>

<style scoped>

</style>
