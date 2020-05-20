import { mutation } from '@/store/util';
import { NS_ADDRESS, NS_PAYMENT } from '@/store/namespaces';
import { SET_AMOUNT, SET_INTERVAL, SET_TYPE } from '@/store/payment/mutationTypes';
import { SET_ADDRESS_TYPE } from '@/store/address/mutationTypes';
import { DataPersistenceMutationType } from '@/view_models/DataPersistenceItem';
import address from '@/store/data_persistence/address';

export default [
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'amount',
		mutation: mutation( NS_PAYMENT, SET_AMOUNT ),
		fields: [],
	},
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'interval',
		mutation: mutation( NS_PAYMENT, SET_INTERVAL ),
		fields: [],
	},
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'type',
		mutation: mutation( NS_PAYMENT, SET_TYPE ),
		fields: [],
	},
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'addressType',
		mutation: mutation( NS_ADDRESS, SET_ADDRESS_TYPE ),
		fields: [],
	},
	address( NS_ADDRESS ),
];
