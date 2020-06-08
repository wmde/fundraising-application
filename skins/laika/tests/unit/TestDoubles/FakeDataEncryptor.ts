import { DataEncryptor } from '@/view_models/DataEncryptor';
const { TextEncoder, TextDecoder } = require( 'util' );

export class FakeDataEncryptor implements DataEncryptor {
	decrypt( data: ArrayBuffer ): Promise<string> {
		return Promise.resolve( new TextDecoder().decode( data ) );
	}

	encrypt( data: string ): Promise<ArrayBuffer> {
		return Promise.resolve( new TextEncoder().encode( data ) );
	}
}

export class FakeFailingDataEncryptor implements DataEncryptor {

	decrypt( data: ArrayBuffer ): Promise<string> {
		throw new Error( 'I never return anything' );
	}

	encrypt( data: string ): Promise<ArrayBuffer> {
		return Promise.resolve( new TextEncoder().encode( data ) );
	}
}
