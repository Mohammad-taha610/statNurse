<template>
  <v-dialog v-model="internalShowDialog" :width="width">
    <template v-slot:activator="{ props, on }">
      <slot name="activator" v-bind="props" v-on="on" />
    </template>

    <v-card>
      <div :style="'background-color: ' + headerColor" class="text-white">
        <v-card-text>
          <slot name="title" />
        </v-card-text>
      </div>

      <div class="py-3 px-2">
        <slot name="content" />
      </div>
      <v-card-actions>
        <v-spacer />
        <slot name="actions"/>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script lang="ts">
import {ref, defineComponent, watch, computed} from 'vue';

export default defineComponent({
  props: {
    width: {
      type: [String, Number],
      default: 300
    },
    headerColor: {
      type: String,
      default: 'red'
    },
    isActive: {
      type: Boolean,
      default: false
    }
  },
  setup(props, { emit }) {
    const internalShowDialog = computed({
      get: () => props.isActive,
      set: (value) => emit('update:isActive', value)
    })

    return {
      internalShowDialog,
    };
  }
});
</script>
