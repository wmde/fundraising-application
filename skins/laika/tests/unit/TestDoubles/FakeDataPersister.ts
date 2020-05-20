import { Store } from 'vuex';
import { DataEncryptor } from '@/view_models/DataEncryptor';
import { DataPersister } from '@/store/data_persistence/DataPersister';
import { DataPersistenceRepository } from '@/view_models/DataPersistenceRepository';
import { DataPersistenceItem } from '@/view_models/DataPersistenceItem';
import { FakeDataEncryptor } from './FakeDataEncryptor';
import FakeDataPersistenceRepository from './FakeDataPersistenceRepository';

export default class FakeDataPersister implements DataPersister {
	dataEncryptor: DataEncryptor;
	initialValues: { key: string; value: any }[];
	keyNamespace: string;
	repository: DataPersistenceRepository;

	constructor( initialValues: { key: string; value: any }[] ) {
		this.initialValues = initialValues;

		this.dataEncryptor = new FakeDataEncryptor();
		this.keyNamespace = 'not a real namespace';
		this.repository = new FakeDataPersistenceRepository();
	}

	async decryptInitialValue( key: string ): Promise<void> {
		return Promise.resolve( undefined );
	}

	async decryptInitialValues( items: DataPersistenceItem[] ): Promise<void> {
		return Promise.resolve( undefined );
	}

	getInitialValue( key: string ): any {
		const item = this.initialValues.find( item => item.key === key );
		return item ? item.value : null;
	}

	getPlugin( items: DataPersistenceItem[] ): ( store: Store<any> ) => void {
		return () => {};
	}

	async load( key: string ): Promise<null | string> {
		return Promise.resolve( null );
	}

	async save( key: string, data: string ): Promise<void> {
		return Promise.resolve( undefined );
	}
}
