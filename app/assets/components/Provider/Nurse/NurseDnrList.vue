<script lang="ts">
import {defineComponent, PropType} from 'vue';
import {Nurse} from '../../../types/member/Nurse';
import {ProviderNurseProp} from '../../../types/member/Provider';
import NurseList from './NurseList.vue';
import {TableAction} from '../../../types/types';
import {unblockNurse} from '../../../services/ProviderService';

export default defineComponent({
  components: {NurseList},
  props: {
    provider_nurse_list: Array as PropType<ProviderNurseProp[]>,
  },
  setup(props) {

  }
})

</script>

<template>
  <NurseList :provider_nurse_list="provider_nurse_list"/>
</template>

<style scoped>

</style>
