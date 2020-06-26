import { mutation } from '@/store/util';
import { NS_ADDRESS, NS_PAYMENT } from '@/store/namespaces';
import { SET_AMOUNT, SET_INTERVAL, SET_TYPE } from '@/store/payment/mutationTypes';
import { SET_ADDRESS_TYPE, SET_NEWSLETTER_OPTIN, SET_RECEIPT_OPTOUT } from '@/store/address/mutationTypes';
import { DataPersistenceMutationType } from '@/view_models/DataPersistence';
import address from '@/store/data_persistence/address';

export default [
	{
		storageKey: 'amount',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_PAYMENT, SET_AMOUNT ),
		fields: [],
	},
	{
		storageKey: 'interval',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_PAYMENT, SET_INTERVAL ),
		fields: [],
	},
	{
		storageKey: 'type',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_PAYMENT, SET_TYPE ),
		fields: [],
	},
	{
		storageKey: 'receiptOptOut',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_ADDRESS, SET_RECEIPT_OPTOUT ),
		fields: [],
	},
	{
		storageKey: 'newsletter',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_ADDRESS, SET_NEWSLETTER_OPTIN ),
		fields: [],
	},
	{
		storageKey: 'addressType',
		mutationType: DataPersistenceMutationType.VALUE,
		mutationKey: mutation( NS_ADDRESS, SET_ADDRESS_TYPE ),
		fields: [],
	},
	address( NS_ADDRESS ),
];
