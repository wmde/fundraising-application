import { Validity } from '@/view_models/Validity';

export interface FieldInitialization {
    name: string,
    value: any,
    validity: Validity,
}
