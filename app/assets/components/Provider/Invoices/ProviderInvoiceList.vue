<script lang="ts">
import {defineComponent, inject, PropType, Ref, ref, watch} from 'vue';
import {Invoice} from '../../../types/payroll/invoice';
import {VDataTable} from 'vuetify/labs/VDataTable';
import {DateTime} from 'luxon';
import ProviderCombobox from '../../Common/ProviderCombobox.vue';
import NstMemberUser from '../../../types/member/NstMemberUser';
import {Provider} from '../../../types/member/Provider';
import {formatCurrency} from '../../../utils/currency';

export default defineComponent({
  methods: {formatCurrency},
  components: {
    ProviderCombobox,
    VDataTable
  },
  props: {
    invoices: {
      type: Array as PropType<Invoice[]>,
      required: true
    }
  },
  setup(props) {
    const headers = [
      {
        title: 'Invoice #',
        value: 'invoiceNumber',
        key: 'invoiceNumber'
      },
      {
        title: 'Pay Period',
        value: 'payPeriod',
        key: 'payPeriod',
      },
      {
        title: 'Amount',
        value: 'amount',
        key: 'amount'
      },
      {
        title: 'Status',
        value: 'status',
        key: 'status',
      },
      {
        title: 'Facility',
        value: 'provider',
        key: 'provider'
      },
      {
        title: 'Actions',
        value: 'actions',
        key: 'actions'
      }
    ]

    const filteredInvoices = ref(props.invoices);
    const providerFilter: Ref<Provider | null> = ref(null);

    watch(providerFilter, (newProvider) => {
      if (newProvider) {
        filteredInvoices.value = props.invoices.filter((invoice: Invoice) => {
          return invoice.provider.id === newProvider.id;
        });
      } else {
        filteredInvoices.value = props.invoices;
      }
    });
    const getPayPeriod = (dateStr: string) => {
      const [startDateStr, endDateStr] = dateStr.split('_');

      const startDate = DateTime.fromFormat(startDateStr, 'yyyyMMdd');
      const endDate = DateTime.fromFormat(endDateStr, 'yyyyMMdd');
      return `${startDate.toFormat('MM/dd/yyyy')} - ${endDate.toFormat('MM/dd/yyyy')}`;
    }

    const openFile = (url: string) => {
      window.open(url, '_blank');
    }

    return {
      headers,
      filteredInvoices,
      getPayPeriod,
      user: inject<NstMemberUser>('user'),
      providerFilter,
      openFile,
      customSort: function(items, index, isDesc) {
        if (index == 'payPeriod') {
          return items.sort((a, b) => {
            const [aStartDateString] = a.split(' - ');
            const [bStartDateString] = b.split(' - ');

            const aDate = new Date(aStartDateString);
            const bDate = new Date(bStartDateString);

            if (isDesc) {
              return aDate < bDate ? 1 : -1;
            } else {
              return aDate > bDate ? 1 : -1;
            }
          });
        } else if (index == 'status') {
          return items.sort((a, b) => {
            if (isDesc) {
              return a.status < b.status ? 1 : -1;
            } else {
              return a.status > b.status ? 1 : -1;
            }
          });
        } else if (index == 'provider') {
          return items.sort((a, b) => {
            if (isDesc) {
              return a.provider.company < b.provider.company ? 1 : -1;
            } else {
              return a.provider.company > b.provider.company ? 1 : -1;
            }
          });
        } else if (index == 'amount') {
          return items.sort((a, b) => {
            if (isDesc) {
              return a.amount < b.amount ? 1 : -1;
            } else {
              return a.amount > b.amount ? 1 : -1;
            }
          });
        } else {
          return items.sort((a, b) => {
            if (isDesc) {
              return a.invoiceNumber < b.invoiceNumber ? 1 : -1;
            } else {
              return a.invoiceNumber > b.invoiceNumber ? 1 : -1;
            }
          });
        }
      }
    }
  }
})
</script>

<template>
  <v-card class="rounded-xl px-4 py-4" elevation="0">
    <v-card-title class="border-b border-gray-300">
      <div class="flex flex-row items-center">
        <div>
          <span>
            Invoices
          </span>
        </div>
        <v-spacer/>
        <div class="mt-2 flex-1">
          <ProviderCombobox v-model="providerFilter" :user="user"/>
        </div>
      </div>
    </v-card-title>
    <VDataTable
        :custom-sort="customSort"
        :sort-by="[{key: 'status', order: 'desc'}, {key: 'payPeriod', order: 'desc'}]"
        :headers="headers" :items="filteredInvoices" class="text-xs">
      <template v-slot:item.payPeriod="{ item }">
        {{ getPayPeriod(item.raw.payPeriod) }}
      </template>

      <template v-slot:item.provider="{ item }">
        {{ item.raw.provider?.company }}
      </template>
      <template v-slot:item.amount="{ item }">
        {{ formatCurrency(item.raw.amount) }}
      </template>

      <template v-slot:item.actions="{ item }">
        <v-btn color="red" @click="openFile(item.raw.fileUrl)">View Invoice</v-btn>
      </template>
    </VDataTable>
  </v-card>
</template>

<style scoped>

</style>
