import axios, { AxiosResponse } from 'axios';

export class PostalLocalityResource {
	private endpoint: string;

	constructor( endpoint: string ) {
		this.endpoint = endpoint;
	}

	async getPostalLocalities( postcode: string ): Promise<Array<string>> {

		return axios.get( this.endpoint + '?postcode=' + postcode )
			.then( ( postalLocalities: AxiosResponse<any> ) => {
				return postalLocalities.data;
			} );
	}

}
