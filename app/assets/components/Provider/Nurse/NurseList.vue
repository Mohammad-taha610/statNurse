<script lang="ts">
import ProviderCombobox from '../../Common/ProviderCombobox.vue';
import {defineComponent, inject, PropType, provide, ref, Ref, watch} from 'vue';
import ProviderLayout from '../ProviderLayout.vue';
import {VDataTable} from 'vuetify/labs/VDataTable';
import NstMemberUser from '../../../types/member/NstMemberUser';
import {Provider, ProviderNurseProp} from '../../../types/member/Provider';
import {TableAction} from '../../../types/types';
import BlockNurseDialog from './BlockNurseDialog.vue';
import {Nurse} from '../../../types/member/Nurse';
import {unblockNurse} from '../../../services/ProviderService';

export default defineComponent({
  components: {BlockNurseDialog, ProviderCombobox, ProviderLayout, VDataTable},
  props: {
    provider_nurse_list: {
      default: [],
      type: Array<ProviderNurseProp[]>
    },
    actions: {
      type: Array<TableAction>,
      default: []
    },
    show_provider_filter: {
      type: Boolean,
      default: true
    },
    showBlockNurse: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const user: NstMemberUser | undefined = inject('user');
    const providers = user?.providers || [];
    const providerFilter: Ref<Provider | null> = ref(null);

    const headers = [
      {
        title: 'First Name',
        value: 'firstName',
        key: 'firstName'
      },
      {
        title: 'Last Name',
        value: 'lastName',
        key: 'lastName'
      },
      {
        title: 'Credentials',
        value: 'credentials',
        key: 'credentials'
      },
      {
        title: 'Facility',
        value: 'provider',
        key: 'provider',
      },
      {
        title: 'Actions',
        value: 'actions',
        key: 'actions'
      }
    ]

    const nurses = props.provider_nurse_list?.flatMap((providerNurse: ProviderNurseProp) => {
      return providerNurse.nurses.map((nurse: Nurse) => {
        return {
          ...nurse,
          provider: providers?.find((provider: Provider) => {
            return provider.id === providerNurse.provider_id;
          })
        } as Nurse
      })
    });

    const filteredNurses = ref(nurses);

    watch(providerFilter, () => {
      if (providerFilter.value === null) {
        filteredNurses.value = nurses;
      } else {
        const providerNurse = props.provider_nurse_list?.find((providerNurse: ProviderNurseProp) => {
          return providerNurse.provider_id === providerFilter.value?.id;
        });

        filteredNurses.value = providerNurse?.nurses?.flatMap((nurse: Nurse) => {
          return {
            ...nurse,
            provider: providers?.find((provider: Provider) => {
              return provider.id === providerNurse?.provider_id;
            })
          } as Nurse
        });
      }
    });

    const unblockNurseClick = async (nurse: Nurse) => {
      await unblockNurse(nurse.id, nurse.provider?.id ?? 0)
      window.location.reload()
    }

    return {
      headers,
      nurses,
      providers,
      providerFilter,
      filteredNurses,
      unblockNurseClick,
      user,
      actions: props.actions
    }
  },
})
</script>

<template>
  <v-card class="rounded-xl px-4 py-4" elevation="0">
    <v-card-title class="border-b border-gray-300">
      Nurse List
    </v-card-title>
    <div class="w-1/2 py-5" v-if="show_provider_filter">
      <!-- TODO add a link on first/last name to nurse page -->
      <ProviderCombobox :user="user" v-model="providerFilter"/>
    </div>
    <VDataTable :headers="headers" :items="filteredNurses">
      <template v-slot:item.firstName="{ item }">
        <a :href="item.raw.nurseRoute" class="text-blue-500">
          {{ item.raw.firstName }}
        </a>
      </template>
      <template v-slot:item.lastName="{ item }">
        <a :href="item.raw.nurseRoute" class="text-blue-500">
          {{ item.raw.lastName }}
        </a>
      </template>
      <template v-slot:item.provider="{ item }">
        {{ item.selectable.provider?.company }}
      </template>
      <template v-slot:item.actions="{ item }">
        <div class="flex flex-row gap-1">
          <BlockNurseDialog v-if="showBlockNurse" :nurse="item.selectable"/>
          <v-btn @click="unblockNurseClick(item.raw)" color="red" v-else>Unblock</v-btn>
        </div>
      </template>
    </VDataTable>
  </v-card>
</template>

<style scoped>

</style>
