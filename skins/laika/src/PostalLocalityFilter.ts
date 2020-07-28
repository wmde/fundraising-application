export interface PostalLocalityFilter {
	getPostalLocalities( postcode: string ): Array<string>
}

export interface PostalLocality {
	locality: string,
	postcode: string,
}

export class CSVPostalLocalityFilter implements PostalLocalityFilter {

	private csvData: Array<PostalLocality>;
	private postcodePattern: RegExp = /^[0-9]{5}$/;

	constructor( csvData: Array<PostalLocality> ) {
		this.csvData = csvData;
	}

	getPostalLocalities( postcode: string ): Array<string> {
		if ( !this.isValidPostcode( postcode ) ) {
			return [];
		}
		const arrayOfLocalityStrings = this.csvData.filter( entry => entry.postcode === postcode )
			.map( entry => entry.locality )
			.sort();
		return [ ... new Set( arrayOfLocalityStrings ) ];
	}

	private isValidPostcode( postcode: string ): boolean {
		if ( postcode === '' ) {
			return false;
		}
		if ( !this.postcodePattern.test( postcode ) ) {
			return false;
		}
		return true;
	}

}
