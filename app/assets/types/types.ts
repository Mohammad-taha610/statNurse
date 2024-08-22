import type {Component} from "vue";
import {Nurse} from './member/Nurse';
import {Provider} from './member/Provider';
import {ShiftCategory} from './shifts/ShiftCategory';

type DropdownItem = {
    text: string,
    link: string,
    icon: string,
}

type TSidebarItem = {
    icon: string,
    text: string,
    link?: string,
    isActive?: boolean,
    roles?: string[],
    isDropdown?: boolean,
    dropdownItems?: DropdownItem[],
}

type TableAction = {
    callback: Function,
    component: Component,
}


// filters for shift calendar
interface Filters {
    nurse?: Nurse
    provider?: Provider
    category?: ShiftCategory
    credential?: string
}

interface ShiftRecurrenceValue {
    selectedDays: string[],
    customDates: Date[],
    endDate: Date,
    selectedRecurrenceType: string
}

export {DropdownItem, TSidebarItem, TableAction, Filters, ShiftRecurrenceValue}
