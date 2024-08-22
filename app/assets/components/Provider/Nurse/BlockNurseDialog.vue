<script lang="ts">
import {defineComponent, inject, PropType, Ref, ref} from 'vue';
import {Nurse} from '../../../types/member/Nurse';
import ProviderCombobox from '../../Common/ProviderCombobox.vue';
import {Provider} from '../../../types/member/Provider';
import NstMemberUser from '../../../types/member/NstMemberUser';
import {blockNurse} from '../../../services/ProviderService';

export default defineComponent({
  components: {ProviderCombobox},
  props: {
    nurse: Object as PropType<Nurse>,
  },
  setup(props) {
    const user: NstMemberUser | undefined = inject('user');
    const dialog = ref(false);
    const providers: Ref<Provider[] | null> = ref([]);
    const blockAll = ref(false);
    const onBlockNurseClick = async () => {
      if (blockAll.value === true) {
        await blockNurse(props.nurse?.id || -1, user?.providers ?? []);
      } else {
        await blockNurse(props.nurse?.id || -1, providers.value ?? []);
      }
      window.location.replace('/executive/providers/nurse_list')
    }

    return {
      dialog,
      providers,
      user,
      blockAll,
      onBlockNurseClick
    }
  }
})

</script>

<template>
  <v-dialog
      v-model="dialog"
      width="600px"
  >
    <template v-slot:activator="{ props }">
      <v-btn
          color="red"
          v-bind="props"
      >
        Block Nurse
      </v-btn>
    </template>

    <v-card>
      <v-card-text>
        <div class="flex flex-col">

        <span class="text-2xl font-bold">
          Block {{ nurse?.fullName }}
        </span>
          <div class="p-3 flex flex-row items-center gap-2">
            <div class="w-2/3">
              <h2>Choose Provider</h2>
              <ProviderCombobox :multiple="true" v-model="providers" :user="user"/>
            </div>
            <span>OR</span>
            <v-checkbox
                hide-details
                v-model="blockAll"
                class="text-red-500 font-bold"
                label="Block ALL"></v-checkbox>
          </div>

        </div>
      </v-card-text>
      <v-card-actions>
        <v-btn color="info" @click="dialog = false">Cancel</v-btn>
        <v-btn color="red" @click="onBlockNurseClick()">BLOCK NURSE</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style scoped>

</style>
