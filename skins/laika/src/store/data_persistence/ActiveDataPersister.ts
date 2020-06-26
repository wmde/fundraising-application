import { DataEncryptor } from '@/view_models/DataEncryptor';
import { DataPersistenceRepository } from '@/view_models/DataPersistenceRepository';
import { DataPersistenceItem, DataPersistenceMutationType, DataPersister } from '@/view_models/DataPersistence';
import { MutationPayload, Store } from 'vuex';

export class ActiveDataPersister implements DataPersister {
	dataEncryptor: DataEncryptor;
	repository: DataPersistenceRepository;
	keyNamespace: string;
	initialValues: { key: string, value: any }[];

	constructor( dataEncryptor: DataEncryptor, repository: DataPersistenceRepository, keyNamespace: string ) {
		this.dataEncryptor = dataEncryptor;
		this.repository = repository;
		this.keyNamespace = keyNamespace;
		this.initialValues = [];
	}

	async save( key: string, data: string ) {
		const encrypted = await this.dataEncryptor.encrypt( data );
		if ( encrypted !== undefined ) {
			this.repository.setItem( `${this.keyNamespace}/${key}`, encrypted );
		}
	}

	async load( key: string ) {
		const data = this.repository.getItem( `${this.keyNamespace}/${key}` );

		if ( !data ) {
			return null;
		}

		try {
			return await this.dataEncryptor.decrypt( data );
		} catch ( e ) {
			this.repository.removeItem( `${this.keyNamespace}/${key}` );
			return null;
		}
	}

	getPlugin( items: DataPersistenceItem[] ) {
		return ( store: Store<any> ) => {
			store.subscribe( ( mutation: MutationPayload, state: any ) => {
				const persistenceItem = items.find( item => item.mutation === mutation.type );
				if ( !persistenceItem ) {
					return;
				}
				switch ( persistenceItem.mutationType ) {
					case DataPersistenceMutationType.VALUE:
						this.save( persistenceItem.key, JSON.stringify( mutation.payload ) );
						break;
					case DataPersistenceMutationType.KEY_VALUE_PAIR:
						if ( persistenceItem.fields.includes( mutation.payload.name ) ) {
							this.save( mutation.payload.name, JSON.stringify( mutation.payload.value ) );
						}
						break;
				}
			} );
		};
	}

	async decryptInitialValues( items: DataPersistenceItem[] ) {
		for ( const item of items ) {
			switch ( item.mutationType ) {
				case DataPersistenceMutationType.VALUE:
					await this.decryptInitialValue( item.key );
					break;
				case DataPersistenceMutationType.KEY_VALUE_PAIR:
					for ( let i = 0; i < item.fields.length; i++ ) {
						await this.decryptInitialValue( item.fields[ i ] );
					}
					break;
			}
		}
	}

	async decryptInitialValue( key: string ) {
		await this.load( key ).then( result => {
			if ( result ) {
				this.initialValues.push( { key: key, value: JSON.parse( result ) } );
			}
		} );
	}

	getInitialValue( key: string ) {
		const initialValue = this.initialValues.find( item => item.key === key );
		return initialValue ? initialValue.value : null;
	}
}
