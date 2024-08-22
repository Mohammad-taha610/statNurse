<template>
  <div class="flex flex-row justify-center items-center gap-2 cursor-pointer">
      <v-img :src="providerPlaceholder" :alt="user?.name" width="48" height="48" class="rounded-full" />
      <div class="flex flex-col  justify-center gap-0">
        <span class="text-md font-bold">{{user?.name}}</span>
        <span class="text-xs font-light">{{user?.type}}</span>
      </div>
      <SidebarItemDropdown activator="parent" :items="dropdownItems" :isDropdownToggled="dropdownIsToggled" />
  </div>
</template>

<script lang="ts">
import {defineComponent, inject, ref, Ref} from "vue";
import NstMemberUser from '../../../types/member/NstMemberUser';
import colors from '../../../utils/colors';
import {DropdownItem} from '../../../types/types';
import SidebarItemDropdown from '../../Common/DropdownItems.vue';
import providerPlaceholder from '../../../images/provider-placeholder-icon.png';

export default defineComponent({
  components: {SidebarItemDropdown},
  computed: {
    colors() {
      return colors
    }
  },
  setup() {
      const user = inject('user');
      const dropdownIsToggled = ref(false);


      const dropdownItems: DropdownItem[] = [
          /**
        {
          text: 'Manage Users',
          link: '/executive/manage-users',
          icon: 'las la-users'
        },**/
        {
          text: 'Logout',
          link: '/executive/logout',
          icon: 'las la-sign-out-alt'
        }
      ]

      return {
        user,
        dropdownIsToggled,
        dropdownItems,
        providerPlaceholder
      };
  }
})
</script>

<style scoped>

</style>
