<script lang="ts">
import {computed, defineComponent, PropType, Ref, ref} from 'vue';
import NstMemberUser from '../../types/member/NstMemberUser';
import {Provider} from '../../types/member/Provider';
import {watch, toRefs} from 'vue';

export default defineComponent({
  props: {
    user: Object as PropType<NstMemberUser>,
    modelValue: Object as PropType<Provider | null | Provider[]>,
    multiple: {
      type: Boolean,
      default: false,
    },
  },
  setup(props, {emit}) {
    const { modelValue, user } = toRefs(props);

    const internalSelectedProvider = computed({
      get: () => {
        if (props.multiple && Array.isArray(modelValue.value)) {
          return modelValue.value;
        }
         return modelValue.value?.company
      },
      set: val => {
        emit('update:modelValue', val)
      }
    });

    const sortProviders = (providers: Provider[]) => {
      return providers.sort((a: Provider, b: Provider) => {
        return a.company.localeCompare(b.company);
      });
    }
    const sortedProviders = ref(sortProviders(user?.value?.providers));

    watch(user.value?.providers, (newProviders) => {
      if (newProviders?.length > 0) {
        sortedProviders.value = sortProviders(newProviders)
      }
    }, {immediate: true});

    const search = ref('');

    const onSearchUpdate = (newSearch: string | undefined) => {
      console.log('serach updating')
      search.value = newSearch ?? '';
    }
    const providerList = computed(() => {
      if (!search.value) {
        return []
      }
      if (search.value.length === 0) {
        return []
      }
      else {
        return sortedProviders.value.filter((provider: Provider) => {
          return provider.company.toLowerCase().indexOf(search.value.toLowerCase()) > -1;
        })
      }
    });

    const customProviderFilter = (company: string, queryText: string, itemText: string) => {
      if (queryText.length === 0) {
        return false
      }
      return company.toLowerCase().indexOf(queryText.toLowerCase()) > -1;
    }

    watch(search, (newSearch: string | undefined) => {
      console.log(newSearch?.value, 'search udpated')
    })

    return {
      onlyOneProvider: computed(() => props.user?.providers.length === 1),
      internalSelectedProvider,
      sortedProviders,
      search,
      customProviderFilter,
      onSearchUpdate
    }
  },
})
</script>

<template>
  <v-autocomplete
      v-model="internalSelectedProvider"
      class="w-full"
      :custom-filter="customProviderFilter"
      :disabled="onlyOneProvider"
      :multiple="multiple"
      persistent-hint
      hint="Search for a facility"
      label="Facility"
      prepend-icon="las la-user-nurse"
      :clearable="true"
      clear-icon="las la-times"
      persistent-clear
      item-title="company"
      item-value="id"
      :items="sortedProviders ?? []"
      variant="underlined"
      return-object
  />
</template>

<style scoped>

</style>
