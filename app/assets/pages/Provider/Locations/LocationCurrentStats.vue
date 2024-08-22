<template>
  <div class="flex flex-row gap-4 w-full rounded-xl">
    <div
        v-for="card in cards"
        :key="card.title"
    >
      <div class="flex flex-row items-center p-2 rounded-xl">
        <div class="rounded-full p-5" :class="card.class">
          <v-icon :color="card.color">las {{ card.icon }}</v-icon>
        </div>
        <div>
          <v-card-title>
            <span class="text-gray-500 text-sm">
              {{ card.title }}
            </span>
          </v-card-title>
          <v-card-text>
            <span class="text-xl font-bold">
              {{ card.text }}
            </span>
          </v-card-text>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { PropType } from 'vue';
import { ProviderLocation } from '../../../types/member/ProviderLocation';

const props = defineProps({
  location: {
    type: Object as PropType<ProviderLocation>,
    required: true,
  },
});

const cards = [
  {
    icon: 'la-exclamation-triangle',
    class: 'bg-red-300/70',
    color: 'red',
    title: 'Unclaimed Shifts',
    text: props.location.unclaimedShiftsCount,
  },
  {
    icon: 'la-bell',
    color: 'warning',
    class: 'bg-orange-100/80',
    title: 'Shift requests',
    text: props.location.shiftRequestCount,
  },
  {
    icon: 'la-list-alt',
    color: 'red',
    class: 'bg-red-300/70',
    title: 'Unresolved Payments',
    text: props.location.unresolvedPaymentCount,
  },
];
</script>

<style scoped>

</style>
