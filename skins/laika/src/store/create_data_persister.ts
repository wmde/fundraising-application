import { DataPersistenceRepository } from '@/view_models/DataPersistenceRepository';
import { SubtleDataEncryptor } from '@/store/SubtleDataEncryptor';
import { DataPersister } from '@/store/data_persistence/DataPersister';

export const createDataPersister = ( repository: DataPersistenceRepository, keyNamespace: string, passphrase: string ): DataPersister => {
	return new DataPersister(
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
