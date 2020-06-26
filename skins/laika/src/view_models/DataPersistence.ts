export enum DataPersistenceMutationType {
	VALUE,
	KEY_VALUE_PAIR,
}

export interface DataPersister {
	save( key: string, data: string ): void;
	load( key: string ): Promise<null | string>;
	getPlugin( items: DataPersistenceItem[] ): any;
	decryptInitialValues( items: DataPersistenceItem[] ): Promise<void>;
	getInitialValue( key: string ): string|null;
}

export interface DataPersistenceItem {
	key: string,
	mutationType: DataPersistenceMutationType,
	mutation: string,
	fields: string[]
}
