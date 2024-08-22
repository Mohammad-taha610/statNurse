<script setup lang="ts">
import ProviderLayout from '../../../components/Provider/ProviderLayout.vue';
import {UserProp} from '../../../utils/props';
import {PropType, provide} from 'vue';
import {ProviderLocation} from '../../../types/member/ProviderLocation';
import LocationCurrentStats from './LocationCurrentStats.vue';
import NurseList from '../../../components/Provider/Nurse/NurseList.vue';
import {ProviderNurseProp} from '../../../types/member/Provider';

const {user, location} = defineProps({
  user: UserProp,
  location: Object as PropType<ProviderLocation>
})

const providerNurse: ProviderNurseProp = {
  provider_id: location.provider.id,
  nurses: location?.previousNurses
}

provide('user', user)
</script>

<template>
  <ProviderLayout>
    <div class="flex flex-col gap-5">
      <h1 class="text-3xl font-bold">{{ location.provider.company }}</h1>
      <LocationCurrentStats :location="location"/>
      <NurseList :show_provider_filter="false" :provider_nurse_list="[providerNurse]"/>
    </div>
  </ProviderLayout>
</template>

<style scoped>

</style>
