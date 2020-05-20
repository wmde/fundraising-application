import { DataPersistenceRepository } from '@/view_models/DataPersistenceRepository';

export default class FakeDataPersistenceRepository implements DataPersistenceRepository {
	public items: {[key: string]: any};

	constructor() {
		this.items = {};
	}

	getItems(): {[key: string]: number} {
		return this.items;
	}

	getItem( key: string ): ArrayBuffer | null {
		const item = this.items[ key ];
		return item !== undefined ? item : null;
	}

	removeItem( key: string ): void {
		delete this.items[ key ];
	}

	setItem( key: string, data: ArrayBuffer ): void {
		this.items[ key ] = data;
	}
}
