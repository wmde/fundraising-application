import { Validity } from '@/view_models/Validity';

export interface MembershipFee {
	validity: {
		[key: string]: Validity
	},
	values: {
		[key: string]: string
	}
}

export interface TypeData {
	selectedType: string,
}

export interface IntervalData {
	selectedInterval: string,
	validateFeeUrl: string,
}

export interface SetFeePayload {
	feeValue: string,
	validateFeeUrl: string,
}
