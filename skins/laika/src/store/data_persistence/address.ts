import { DataPersistenceMutationType } from '@/view_models/DataPersistenceItem';
import { mutation } from '@/store/util';
import { SET_ADDRESS_FIELD } from '@/store/address/mutationTypes';

export default ( namespace: string ) => {
	return {
		mutationType: DataPersistenceMutationType.KEY_VALUE_PAIR,
		key: 'address',
		mutation: mutation( namespace, SET_ADDRESS_FIELD ),
		fields: [
			'salutation',
			'title',
			'firstName',
			'lastName',
			'street',
			'postcode',
			'city',
			'country',
			'email',
			'companyName',
		],
	};
};
