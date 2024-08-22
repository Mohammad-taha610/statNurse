<script lang="ts">
import {defineComponent, PropType, provide} from 'vue';
import NstMemberUser from '../../../types/member/NstMemberUser';
import {Nurse} from '../../../types/member/Nurse';
import ProviderLayout from '../../../components/Provider/ProviderLayout.vue';
import NurseProfile from '../../../components/Provider/Nurse/NurseProfile.vue';
import {NstFile} from '../../../types/files/NstFile';

export default defineComponent({
  components: {NurseProfile, ProviderLayout},
  props: {
    user: Object as PropType<NstMemberUser>,
    nurse: Object as PropType<Nurse>,
    files: Array as PropType<NstFile[]>
  },
  setup(props) {
    provide('user', props.user)
    return {
    }
  }
})

</script>

<template>
  <ProviderLayout>
    <NurseProfile :nurse="nurse" :files="files" />
  </ProviderLayout>
</template>

<style scoped>

</style>
