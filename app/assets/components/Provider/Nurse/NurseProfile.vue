<script lang="ts">
import {defineComponent, inject, PropType, provide, Ref, ref} from 'vue';
import {Nurse} from '../../../types/member/Nurse';
import BlockNurseDialog from './BlockNurseDialog.vue';
import NstMemberUser from '../../../types/member/NstMemberUser';
import {blockNurse} from '../../../services/ProviderService';
import {Provider} from '../../../types/member/Provider';
import {NstFile} from '../../../types/files/NstFile';
import profilePlaceholder from '../../../images/profile_placeholder.png';

export default defineComponent({
  components: {BlockNurseDialog},
  props: {
    nurse: Object as PropType<Nurse>,
    files: Array as PropType<NstFile[]>
  },
  setup(props) {
    const user: NstMemberUser | undefined = inject('user');
    const onlyOneProvider = user?.providers.length === 1;
    const selectedProvider: Ref<Provider | undefined> = ref(user?.providers[0]);
    const blockNurseClick = async () => {
      await blockNurse(props.nurse?.id || -1, selectedProvider.value?.id || -1);
      window.location.replace('/executive/providers/nurse_list')
    }
    return {
      onlyOneProvider,
      blockNurseClick,
      profilePlaceholder
    }
  }
})

</script>

<template>
  <div class="flex flex-col xl:flex-row w-full">
    <v-sheet elevation="1" rounded="70px" class="p-5 rounded-xl w-full xl:w-2/3">
      <div class="flex flex-row items-center">
        <span class="text-2xl font-bold mr-2">General</span>
        <v-spacer/>
        <v-btn v-if="onlyOneProvider" color="red" @click="blockNurseClick">Block Nurse</v-btn>
        <BlockNurseDialog v-else :nurse="nurse"/>
      </div>
      <v-divider class="border-black my-6"/>
      <div class="flex flex-col">
        <span class="font-thin text-gray-500">First Name</span>
        <span>{{ nurse?.firstName }}</span>
      </div>

      <div class="flex flex-col mt-4">
        <span class="font-thin text-gray-500">Middle Name</span>
        <span>{{ nurse?.middleName }}</span>
      </div>
      <div class="flex flex-col mt-4">
        <span class="font-thin text-gray-500">Last Name</span>
        <span>{{ nurse?.lastName }}</span>
      </div>

      <div class="flex flex-col mt-4">
        <span class="font-thin text-gray-500">Birthday</span>
        <span>{{ nurse?.birthDate }}</span>
      </div>
      <div class="mt-8">
        <span class="text-2xl font-bold mr-2 mt-6">Files</span>
        <v-divider class="border-black my-6"/>
        <div class="flex flex-row flex-wrap gap-4">
          <a :href="file.route" target="_blank" v-for="file in files" class="bg-white shadow-md w-36 h-36 p-5 rounded-md flex flex-col items-center">
            <v-icon class="text-red text-4xl" size="50px">las la-file</v-icon>
            <span class="text-sm text-center">{{file.tag.name}}</span>
          </a>
        </div>
      </div>
    </v-sheet>
    <v-sheet elevation="1" class="rounded-xl w-full md:w-2/3 xl:w-1/3 h-fit pb-3 my-5 xl:my-0 xl:mx-5">
      <div
          class="flex flex-column items-center px-0 xl:px-5 py-8 "
      >
        <img :src="profilePlaceholder" alt="avatar placeholder"
             class="w-36 h-36 rounded-full"/>
        <div class="mt-5">
          <span class="text-xl font-bold">
            {{ nurse?.fullName }}
          </span>
        </div>
        <span class="mt-3 font-light text-gray-500">{{ nurse?.credentials }}</span>
      </div>
      <div class="border-[0.2px] border-gray-200 w-full"/>
      <div class="flex flex-column items-start px-5 py-8">
        <span><v-icon class="text-red mr-2">las la-phone</v-icon>{{ nurse?.phoneNumber }}</span>
        <span class="mt-3"><v-icon class="text-red mr-2">las la-envelope</v-icon>{{ nurse?.email }}</span>
      </div>
    </v-sheet>
  </div>
</template>

<style scoped>

</style>
