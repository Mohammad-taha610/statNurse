import {ShiftCategory} from './ShiftCategory';

export default interface PresetShiftTime {
    id: number;
    shiftCategory: ShiftCategory;
    displayTime: string;
    isCustom?: boolean;
}
