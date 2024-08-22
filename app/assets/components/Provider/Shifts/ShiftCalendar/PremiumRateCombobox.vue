<script lang="ts">
import {computed, defineComponent, PropType, toRefs, watch} from 'vue';

export default defineComponent({
  props: {
    modelValue: Object as PropType<{text: string, value: number}>
  },
  setup(props, {emit}) {
    const rates = [
      {
        text: 'None',
        value: 1
      },
      {
        text: '20%',
        value: 1.20
      },
      {
        text: '25%',
        value: 1.25
      },
      {
        text: '30%',
        value: 1.30
      },
      {
        text: '50%',
        value: 1.50
      },
      {
        text: '75%',
        value: 1.75
      }
    ];

    const { modelValue } = toRefs(props);

    const rules = [
      (v: any) => !!v || 'Item is required',
    ]

    const internalSelectedRate = computed({
      get: () => modelValue.value,
      set: (rate) => {
        emit('update:modelValue', rate)
      }
    });

    return {
      rates,
      internalSelectedRate,
      rules
    }
  }
})
</script>

<template>
  <v-select
      class="w-full"
      label="Premium rate"
      prepend-icon="las la-dollar-sign"
      :clearable="true"
      clear-icon="las la-times"
      persistent-clear
      v-model="internalSelectedRate"
      item-title="text"
      item-value="value"
      :rules="rules"
      :items="rates ?? []"
      variant="underlined"
      return-object
  />
</template>

<style scoped>

</style>
