import { DataPersistenceRepository } from '@/view_models/DataPersistenceRepository';
import { SubtleDataEncryptor } from '@/store/SubtleDataEncryptor';
import { DataPersister } from '@/view_models/DataPersistence';
import { ActiveDataPersister } from '@/store/data_persistence/ActiveDataPersister';
import { InactiveDataPersister } from '@/store/data_persistence/InactiveDataPersister';

export const createDataPersister = ( repository: DataPersistenceRepository, keyNamespace: string, passphrase: string ): DataPersister => {
	if ( typeof window.crypto === 'undefined' || typeof TextEncoder === 'undefined' ) {
		return new InactiveDataPersister();
	}

	return new ActiveDataPersister(
		new SubtleDataEncryptor( passphrase ),
		repository,
		keyNamespace
	);
};

export const clearPersistentData = ( repository: DataPersistenceRepository, keyNamespaces: string[] ): void => {
	const items = repository.getItems();
	keyNamespaces.forEach( namespace => {
		const pattern = RegExp( `^${namespace}/` );
		const keys = Object.keys( items ).filter( key => pattern.test( key ) );
		keys.forEach( key => repository.removeItem( key ) );
	} );
};
