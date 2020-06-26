import { DataPersistenceItem, DataPersister } from '@/view_models/DataPersistence';

export class InactiveDataPersister implements DataPersister {
	decryptInitialValues( items: DataPersistenceItem[] ): Promise<void> {
		return Promise.resolve( undefined );
	}

	getInitialValue( key: string ): string | null {
		return null;
	}

	getPlugin( items: DataPersistenceItem[] ): any {
		return () => {};
	}

	load( key: string ): Promise<string | null> {
		return Promise.resolve( null );
	}

	save( key: string, data: string ): void {
	}
}
