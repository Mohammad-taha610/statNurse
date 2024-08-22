<template>
  <v-navigation-drawer
      class="nav"
      v-model="drawer"
      theme="dark"
      width="300"
      rail-width="55"
      :rail="rail"
      :permanent="true"
  >
    <div class="px-2 py-4 mb-4">
      <img v-if="!rail" :src="whiteLogo" alt="Logo" class="w-full object-cover"/>
      <img v-if="rail" :src="smallLogo" alt="Logo" class="w-full object-cover"/>
    </div>
    <v-list color="transparent">
      <SidebarItems :rail="rail" :items="items"/>
    </v-list>
    <template v-slot:append>
    </template>
  </v-navigation-drawer>
</template>
<script lang="ts">
import SidebarItem from "./SidebarItem.vue";
import SidebarItems from "./SidebarItems.vue";
import {defineComponent, inject} from 'vue';
import {TSidebarItem} from '../../../types/types';
import smallLogo from '../../../images/small-logo.png'
import whiteLogo from '../../../images/white-logo.png'
import NstMemberUser from '../../../types/member/NstMemberUser';

const sidebarItems: TSidebarItem[] = [
  {
    icon: 'las la-server',
    text: 'Dashboard',
    isActive: window.location.pathname === '/executive/dashboard',
    link: '/executive/dashboard'
  },
  {
    icon: 'las la-calendar',
    text: 'Shift Calendar',
    isActive: ['/executive/shifts', '/executive/shifts/create', '/executive/shifts/review', '/executive/shifts/requests'].includes(window.location.pathname),
    isDropdown: true,
    dropdownItems: [
      {
        text: 'Manage Shift Calendar',
        icon: 'las la-calendar',
        link: '/executive/shifts',
      },
      {
        text: 'Shift Requests',
        icon: 'las la-exclamation-circle',
        link: '/executive/shifts/requests',
      },
      {
        text: 'Review Shifts',
        icon: 'las la-check-circle',
        link: '/executive/shifts/review',
      }
    ]
  },
  {
    icon: 'las la-user-nurse',
    text: 'Nurses',
    isDropdown: true,
    isActive: ['/executive/providers/nurse_list', '/executive/providers/dnr_list'].includes(window.location.pathname),
    dropdownItems: [
      {
        text: 'Nurses List',
        icon: 'las la-list',
        link: '/executive/providers/nurse_list',
      },
      {
        text: 'Do Not Return List',
        icon: 'las la-ban',
        link: '/executive/providers/dnr_list',
      }
    ]
  },
  {icon: 'las la-map-marked', text: 'Locations', link: '/executive/provider/locations', roles: ['ROLE_EXECUTIVE'], isActive: window.location.pathname === '/executive/provider/locations'},
  {icon: 'las la-file-invoice-dollar', text: 'Review Invoices', link: '/executive/providers/invoices', isActive: window.location.pathname === '/executive/providers/invoices'},
  {
    icon: 'las la-dollar-sign',
    text: 'Current Pay Period',
    link: '/executive/payroll/current_pay_period',
    roles: ['ROLE_PROVIDER_ADMIN'],
    isActive: window.location.pathname === '/executive/payroll/current_pay_period'
  },
  /*{
    icon: 'las la-file',
    text: 'PBJ Report',
    link: '/executive/payroll/pbj_report',
    roles: ['ROLE_PROVIDER_ADMIN'],
    isActive: window.location.pathname === '/executive/payroll/pbj_report'
  },*/
]
export default defineComponent({
  name: "ProviderSidebar",
  components: {SidebarItems, SidebarItem},
  props: {
    rail: {
      type: Boolean,
      default: true,
    },
  },
  setup() {
    const user: NstMemberUser | undefined = inject('user')
    const items = sidebarItems.filter(item => {
      if (item.roles) {
        const userRoles = user?.roles
        const requiredRoles = item.roles
        if (userRoles && requiredRoles) {
          return userRoles.some(role => requiredRoles.includes(role))
        }
        return false;
      }
      return true
    });
    return {
      collapse: false,
      items: items,
      drawer: true,
      smallLogo,
      whiteLogo
    }
  }
})
</script>

<style scoped>
.nav {
  background-color: rgb(34, 34, 34);
}
</style>
