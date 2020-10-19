export default class PageDataInitializer<T> {
	environment: string;
	applicationVars: T;
	messages: { [key: string]: string };
	assetsPath: string;
	selectedBuckets: string[];

	constructor( dataElementSelector: string = '#appdata' ) {
		const dataElement: HTMLElement | null = document.querySelector( dataElementSelector );
		if ( !dataElement ) {
			throw new Error( 'No element found with selector ' + dataElementSelector );
		}
		const applicationVars = JSON.parse( dataElement.dataset.applicationVars || '{}' );
		this.selectedBuckets = [];
		if ( applicationVars.selectedBuckets ) {
			this.selectedBuckets = applicationVars.selectedBuckets;
			delete applicationVars.selectedBuckets;
		}
		this.environment = dataElement.dataset.environment || '';
		this.applicationVars = applicationVars;
		this.messages = JSON.parse( dataElement.dataset.applicationMessages || '{}' );
		this.assetsPath = dataElement.dataset.assetsPath || '{}';
	}
}
