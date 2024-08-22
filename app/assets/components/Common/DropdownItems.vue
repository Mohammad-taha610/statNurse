<script lang="ts">
import {defineComponent, ref, PropType} from 'vue';
import {DropdownItem} from '../../types/types';
import colors from '../../utils/colors';

export default defineComponent({
  computed: {
    colors() {
      return colors
    }
  },
  props: {
    items: {
      type: Array as PropType<DropdownItem[]>,
      required: true,
    },
    activator: {
      type: String,
      required: true,
    },
  },
  setup() {
    const isDropdownToggled = ref(false);
    return {
      isDropdownToggled
    }
  }
})

</script>

<template>
  <v-menu v-model="isDropdownToggled" :activator="activator" offset="5,5">
  <v-list>
    <v-list-item v-for="(item, index) in items" :key="index">
      <a :href="item.link" class="flex flex-row items-center gap-1 w-full justify-start hover:text-red-500">
        <v-icon class="mr-3" :color="colors.nursestatRed" size="24px">{{item.icon}}</v-icon>
        <v-list-item-title>{{ item.text }}</v-list-item-title>
      </a>
    </v-list-item>
  </v-list>
  </v-menu>
</template>

<style scoped>

</style>
