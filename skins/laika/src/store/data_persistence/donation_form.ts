import { mutation } from '@/store/util';
import { NS_ADDRESS, NS_PAYMENT } from '@/store/namespaces';
import { SET_AMOUNT, SET_INTERVAL, SET_TYPE } from '@/store/payment/mutationTypes';
import { SET_ADDRESS_TYPE, SET_NEWSLETTER_OPTIN, SET_RECEIPT_OPTOUT } from '@/store/address/mutationTypes';
import { DataPersistenceMutationType } from '@/view_models/DataPersistence';
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
		key: 'receiptOptOut',
		mutation: mutation( NS_ADDRESS, SET_RECEIPT_OPTOUT ),
		fields: [],
	},
	{
		mutationType: DataPersistenceMutationType.VALUE,
		key: 'newsletter',
		mutation: mutation( NS_ADDRESS, SET_NEWSLETTER_OPTIN ),
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
