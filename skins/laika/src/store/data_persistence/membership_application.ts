import { mutation } from '@/store/util';
import { NS_MEMBERSHIP_ADDRESS, NS_MEMBERSHIP_FEE } from '@/store/namespaces';
import {
	SET_ADDRESS_TYPE,
	SET_DATE,
	SET_MEMBERSHIP_TYPE,
	SET_RECEIPT_OPTOUT,
} from '@/store/membership_address/mutationTypes';
import { SET_FEE, SET_INTERVAL } from '@/store/membership_fee/mutationTypes';
import { DataPersistenceMutationType } from '@/view_models/DataPersistence';
import address from '@/store/data_persistence/address';

export default [
	{
		storageKey: 'membershipType',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_MEMBERSHIP_ADDRESS, SET_MEMBERSHIP_TYPE ),
		fields: [],
	},
	{
		storageKey: 'addressType',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_MEMBERSHIP_ADDRESS, SET_ADDRESS_TYPE ),
		fields: [],
	},
	{
		storageKey: 'date',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_MEMBERSHIP_ADDRESS, SET_DATE ),
		fields: [],
	},
	{
		storageKey: 'fee',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_MEMBERSHIP_FEE, SET_FEE ),
		fields: [],
	},
	{
		storageKey: 'receiptOptOut',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_MEMBERSHIP_ADDRESS, SET_RECEIPT_OPTOUT ),
		fields: [],
	},
	{
		storageKey: 'interval',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_MEMBERSHIP_FEE, SET_INTERVAL ),
		fields: [],
	},
	address( NS_MEMBERSHIP_ADDRESS ),
];
