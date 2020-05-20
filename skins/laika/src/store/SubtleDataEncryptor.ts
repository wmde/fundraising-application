import { DataEncryptor } from '@/view_models/DataEncryptor';

const ENCODING_ALGORITHM = {
	name: 'AES-GCM',
	iv: new Uint8Array( 16 ),
};

export class SubtleDataEncryptor implements DataEncryptor {
	passphrase: string;
	cryptoKey: CryptoKey | null;

	constructor( passphrase: string ) {
		this.passphrase = passphrase;
		this.cryptoKey = null;
	}

	async decrypt( data: ArrayBuffer ): Promise<string> {
		const cryptoKey = await this.getCryptoKey();
		const decoded = await window.crypto.subtle.decrypt( ENCODING_ALGORITHM, cryptoKey, data );
		return this.arrayBufferToString( decoded );
	}

	async encrypt( data: string ): Promise<ArrayBuffer> {
		const cryptoKey = await this.getCryptoKey();
		return await window.crypto.subtle.encrypt(
			ENCODING_ALGORITHM,
			cryptoKey,
			this.stringToArrayBuffer( data )
		);
	}

	async getCryptoKey() {
		if ( this.cryptoKey === null ) {
			return await this.generateSecretKey( this.passphrase );
		}
		return this.cryptoKey;
	}

	async generateSecretKey( passphrase: string ) {
		this.cryptoKey = await window.crypto.subtle.importKey(
			'raw',
			this.base64StringToArrayBuffer( passphrase ),
			'AES-GCM',
			false,
			[ 'encrypt', 'decrypt' ]
		);
		return this.cryptoKey;
	}

	base64StringToArrayBuffer( data: string ): Uint8Array {
		const binaryString = window.atob( data );
		const length = binaryString.length;
		const bytes = new Uint8Array( length );
		for ( let i = 0; i < length; i++ ) {
			bytes[ i ] = binaryString.charCodeAt( i );
		}
		return bytes;
	}

	stringToArrayBuffer( data: string ): ArrayBuffer {
		return new TextEncoder().encode( data );
	}

	arrayBufferToString( buffer: ArrayBuffer ) {
		return new TextDecoder().decode( buffer );
	}
}
