import { h } from 'vue'
import type { IconSet, IconAliases, IconProps } from 'vuetify'

const aliases: IconAliases = {
    sortAsc: 'la-sort-up',
    sortDesc: 'la-sort-down',
    calendar: '...',
    collapse: '...',
    complete: '...',
    cancel: '...',
    close: '...',
    delete: '...',
    clear: '...',
    success: '...',
    info: '...',
    warning: '...',
    error: '...',
    prev: 'mdi mdi-chevron-left',
    next: 'mdi mdi-chevron-right',
    checkboxOn: 'mdi mdi-checkbox-marked',
    checkboxOff: 'mdi mdi-checkbox-blank-outline',
    checkboxIndeterminate: '...',
    delimiter: '...',
    sort: '...',
    expand: '...',
    menu: '...',
    subgroup: '...',
    dropdown: '...',
    radioOn: '...',
    radioOff: '...',
    edit: '...',
    ratingEmpty: '...',
    ratingFull: '...',
    ratingHalf: '...',
    loading: '...',
    first: 'la-angle-double-left',
    last: 'la-angle-double-right',
    unfold: '...',
    file: '...',
    plus: '...',
    minus: '...',
}

const custom: IconSet = {
    component: (props: IconProps) => h(props.tag, { class: ['las', props.icon] })
}

export { aliases, custom }
