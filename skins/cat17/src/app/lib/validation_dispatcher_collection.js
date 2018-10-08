'use strict';

/**
 *
 * @module redux_validation
 */

var objectAssign = require( 'object-assign' ),

	ValidationDispatcherCollection = {
		dispatchers: [],
		store: null,
		formContentName: '',
		onUpdate: function () {
			var formContent = this.store.getState()[ this.formContentName ],
				i;
			for ( i = 0; i < this.dispatchers.length; i++ ) {
				this.dispatchers[ i ].dispatchIfChanged( formContent, this.store );
			}
		}
	},

	/**
	 * @constructor
	 * @param {Object} store Redux store
	 * @param {ValidationDispatcher[]} dispatchers
	 * @param {string} formContentName Field name for the store to access form contents, e.g. 'donationFormContent' or 'membershipFormContent'
	 * @return {ValidationDispatcherCollection}
	 */
	createValidationDispatcherCollection = function ( store, dispatchers, formContentName ) {
		var collection = objectAssign( Object.create( ValidationDispatcherCollection ), {
			store: store,
			dispatchers: dispatchers,
			formContentName: formContentName
		} );
		store.subscribe( collection.onUpdate.bind( collection ) );
		return collection;
	};

module.exports = {
	createValidationDispatcherCollection: createValidationDispatcherCollection
};
