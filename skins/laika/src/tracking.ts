// This file encapsulates the tracking interactions with Matomo

declare var _paq: MatomoLoggable; /* eslint-disable-line no-underscore-dangle */

interface MatomoLoggable {
	push( eventData: Array<any> ): void;
}

export function trackDynamicForm() {
	_paq.push( [ 'FormAnalytics::scanForForms' ] );
}

export function trackFormSubmission( formElement: HTMLFormElement ) {
	_paq.push( [ 'FormAnalytics::trackFormSubmit', formElement ] );
}

export function trackFormFieldRestored( field: string, value: string ) {
	_paq.push( [ 'trackEvent', 'Form Field Restored', field, value ] );
}

export function trackFormValidationErrors( formName: string, formFieldName: string ) {
	_paq.push( [ 'trackEvent', formName, formFieldName ] );
}
