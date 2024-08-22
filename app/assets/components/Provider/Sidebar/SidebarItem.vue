<template>
  <v-list-item class="flex flex-row items-center m-0" style="margin: 0; padding:0" size="xl" :href="link" :active="isActive"
               active-class="text-black icon-active">
    <div class="flex flex-col justify-between">
      <div class="flex flex-row w-full justify-between p-5 px-2" :class="isActive || dropdownIsToggled ? 'bg-white': ''"
           @click="dropdownIsToggled = !dropdownIsToggled"
      >
        <v-list-item-title>
          <div v-if="rail">
            <v-icon slot="icon" class="ml-1" :color="isActive ? colors.nursestatRed : 'white'">{{
                icon
              }}
            </v-icon>
            <DropdownItems :items="dropdownItems" :isDropdownToggled="dropdownIsToggled" activator="parent"/>
          </div>
          <div v-else class="pl-5 pt-0">
            <span class="text-md font-light" :class="isActive || dropdownIsToggled ? 'text-black' : 'text-white'"
            >
              <v-icon slot="icon"
                      :color="isActive || dropdownIsToggled ? colors.nursestatRed : 'white'">{{
                  icon
                }}</v-icon>
              <span v-if="!rail" class="ml-1">
                {{ text }}
              </span>
            </span>
          </div>
        </v-list-item-title>

        <v-list-item-action v-if="isDropdown && !rail">
          <v-icon :color="isActive || dropdownIsToggled ? colors.nursestatRed : 'white'" :class="{'arrow-rotated': dropdownIsToggled}" class="arrow text-sm">las la-angle-right
          </v-icon>
        </v-list-item-action>
      </div>

      <transition name="dropdown" v-if="!rail && isDropdown">
        <div v-if="dropdownIsToggled" class="w-full flex flex-col items-start pl-16 pt-2 pb-4">
          <div>
            <div v-for="item in dropdownItems" :class="getLinkStyle(item.link)">
              <a :href="item.link" class="text-sm hover:text-red-500">{{ item.text }}</a>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </v-list-item>
</template>

<script lang="ts">
import colors from '../../../utils/colors';
import DropdownItems from "../../Common/DropdownItems.vue";
import {defineComponent, PropType, computed} from "vue";
import {DropdownItem} from '../../../types/types';
import {useAppState} from '../../../stores/appStateStore';

export default defineComponent({
  components: {DropdownItems},
  props: {
    rail: {
      type: Boolean,
      default: false,
    },
    icon: {
      type: String,
      required: true,
    },
    text: {
      type: String,
      required: true,
    },
    link: {
      type: String,
      required: true,
    },
    isActive: {
      type: Boolean,
      default: false,
    },
    isDropdown: {
      type: Boolean,
      default: false,
    },
    dropdownItems: {
      type: Array as PropType<DropdownItem[]>,
      default: () => [],
    },
  },
  data() {
    const appStateStore = useAppState();
    const isSidebarCollapsed = computed(() => appStateStore.isSidebarCollapsed);
    return {
      colors,
      isSidebarCollapsed,
      dropdownIsToggled: this.isActive,
      getLinkStyle: (link: string) => {
        return {
          'text-red': window.location.pathname === link,
          'text-white': window.location.pathname !== link,
        }
      }
    }
  },
})
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.4s ease;
  transform: translateX(0);
}

.dropdown-enter-from,
.dropdown-leave-to {
  transform: translateX(-100%);
  opacity: 0;
}

.arrow {
  transition: all 0.4s ease;
  transform: rotate(0deg);
  opacity: 1;
}

.arrow-rotated {
  transform: rotate(90deg);
  opacity: 1;
}

</style>
