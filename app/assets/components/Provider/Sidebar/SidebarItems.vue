<template>
      <SidebarItem
          v-for="(item, _) in items"
          :link="item.link ?? ''"
          :text="item.text"
          :icon="item.icon"
          :is-active="item.isActive"
          :is-dropdown="item.isDropdown"
          :dropdown-items="item.dropdownItems"
          :rail="rail"
          class="cursor-pointer text-white"
      />
</template>

<script lang="ts">
import SidebarItem from "./SidebarItem.vue";
import {defineComponent, PropType} from 'vue';
import {TSidebarItem} from '../../../types/types';

export default defineComponent({
  name: "SidebarItems",
  props: {
    items: {
      type: Array as PropType<TSidebarItem[]>,
      default: () => [],
    },
    rail: {
      type: Boolean,
      default: false,
    }
  },
  components: {SidebarItem},
})
</script>

<style scoped>

</style>
