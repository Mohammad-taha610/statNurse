<template>
  <v-app :style="{'background': colors.mainBg}">
      <ProviderSidebar :rail="appStateStore.isSidebarCollapsed" />
      <ProviderAppBar :collapseSidebar="() => appStateStore.toggleIsSidebarCollapsed()" />
      <v-main>
        <div class="lg:p-3">
          <slot></slot>
        </div>
      </v-main>
  </v-app>
</template>


<script lang="ts">

import colors from '../../utils/colors';
import {useAppState} from "../../stores/appStateStore";
import ProviderSidebar from "./Sidebar/ProviderSidebar.vue";
import ProviderAppBar from "./AppBar/ProviderAppBar.vue";
import {useUserStore} from "../../stores/userStore";
import {defineComponent, inject, PropType, provide} from "vue";
import NstMemberUser from "../../types/member/NstMemberUser";
export default defineComponent({
  name: "ProviderLayout",
  components: {ProviderAppBar, ProviderSidebar},
  props: {
    user: Object as PropType<NstMemberUser>,
  },
  data() {
    return {
      rail: true,
    }
  },
  setup(props) {
    const appStateStore = useAppState();
    const user = inject('user')
    return {
      appStateStore,
      colors,
      user
    }
  },
  mounted() {
    const userStore = useUserStore();
    userStore.setUser(this.user);
  }
});
</script>

<style scoped>

</style>
