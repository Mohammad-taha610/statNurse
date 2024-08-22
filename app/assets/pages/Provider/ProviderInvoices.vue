<script lang="ts">
import {defineComponent, PropType, provide} from 'vue';
import ProviderLayout from '../../components/Provider/ProviderLayout.vue';
import ProviderInvoiceList from '../../components/Provider/Invoices/ProviderInvoiceList.vue';
import {Invoice} from '../../types/payroll/invoice';
import NstMemberUser from '../../types/member/NstMemberUser';
import {UserProp} from '../../utils/props';

export default defineComponent({
  components: {ProviderInvoiceList, ProviderLayout},
  props: {
    user: UserProp,
    data: {
      type: Array as PropType<Invoice[]>,
      required: true
    }
  },
  setup(props) {
    provide('user', props.user)
  }
})
</script>

<template>
  <ProviderLayout>
    <ProviderInvoiceList :invoices="data" />
  </ProviderLayout>
</template>

<style scoped>

</style>
