export default class PageDataInitializer<T> {
	applicationVars: T;
	messages: { [key: string]: string };
	assetsPath: string;

	constructor( dataElementSelector: string = '#app' ) {
		const dataElement: HTMLElement | null = document.querySelector( dataElementSelector );
		if ( !dataElement ) {
			throw new Error( 'No element found with selector ' + dataElementSelector );
		}
		this.applicationVars = JSON.parse( dataElement.dataset.applicationVars || '{}' );
		this.messages = JSON.parse( dataElement.dataset.applicationMessages || '{}' );
		this.assetsPath = dataElement.dataset.assetsPath || '{}';
	}
}
