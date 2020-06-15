import { mutation } from '@/store/util';
import { NS_MEMBERSHIP_ADDRESS, NS_MEMBERSHIP_FEE } from '@/store/namespaces';
import {
	SET_ADDRESS_TYPE,
	SET_DATE,
	SET_MEMBERSHIP_TYPE,
	SET_RECEIPT_OPTOUT,
} from '@/store/membership_address/mutationTypes';
import { SET_FEE, SET_INTERVAL } from '@/store/membership_fee/mutationTypes';
import { DataPersistenceMutationType } from '@/view_models/DataPersistenceItem';
import address from '@/store/data_persistence/address';

export default [
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'membershipType',
		mutation: mutation( NS_MEMBERSHIP_ADDRESS, SET_MEMBERSHIP_TYPE ),
		fields: [],
	},
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'addressType',
		mutation: mutation( NS_MEMBERSHIP_ADDRESS, SET_ADDRESS_TYPE ),
		fields: [],
	},
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'date',
		mutation: mutation( NS_MEMBERSHIP_ADDRESS, SET_DATE ),
		fields: [],
	},
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'fee',
		mutation: mutation( NS_MEMBERSHIP_FEE, SET_FEE ),
		fields: [],
	},
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'receiptOptOut',
		mutation: mutation( NS_MEMBERSHIP_ADDRESS, SET_RECEIPT_OPTOUT ),
		fields: [],
	},
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'interval',
		mutation: mutation( NS_MEMBERSHIP_FEE, SET_INTERVAL ),
		fields: [],
	},
	address( NS_MEMBERSHIP_ADDRESS ),
];
