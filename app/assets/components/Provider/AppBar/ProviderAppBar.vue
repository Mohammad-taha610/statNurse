<template>
  <v-app-bar :elevation="0" :color="colors.mainBg">
    <v-app-bar-nav-icon @click.stop="collapseSidebar" :color="colors.nursestatRed" icon="las laserver">
      <v-icon>las la-bars</v-icon>
    </v-app-bar-nav-icon>
    <v-app-bar-title>
      <span class="font-bold text-2xl">
        {{appStateStore.currentPageName}}
      </span>
    </v-app-bar-title>
    <v-spacer />
    <NurseSearchBar />
    <v-spacer />
    <div class="mr-12">
      <UserMenu />
    </div>
  </v-app-bar>
</template>


<script setup>
import {useAppState} from "../../../stores/appStateStore";
import NurseSearchBar from "./NurseSearchBar.vue";
import colors from '../../../utils/colors';
import {inject} from 'vue';
import UserMenu from "./UserMenu.vue";

const appStateStore = useAppState();

const user = inject('user');
</script>
<script>
import colors from '../../../utils/colors';
export default {
  name: "ProviderAppBar",
  props: {
    collapseSidebar: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      colors,
    }
  },
}
</script>

<style scoped>

</style>
