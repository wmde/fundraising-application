// TODO Possibly throw this file away

import { PrivateDonorName, CompanyDonorName, DonorAddress, DonorEmail } from './AddressData';
import { IntervalData, TypeData, AmountData } from './Payment';

export interface DonationConfirmationModel {
	name: PrivateDonorName|CompanyDonorName|null,
	address: DonorAddress|null,
	email: DonorEmail|null
	paymentAmount: AmountData,
	paymentType: TypeData,
	paymentInterval: IntervalData
}

