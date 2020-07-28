export interface PostalLocalityFilter {
	getPostalLocalities( postcode: String ): Array<String>
}

export class CSVPostalLocalityFilter implements PostalLocalityFilter {

	private csvData: Array<object>;

	constructor( csvData: Array<object> ) {
		this.csvData = csvData;
	}

	getPostalLocalities( postcode: String ): Array<String> {
		throw new Error( 'not implemented' );
	}

}
