'use strict';

/**
 * Connect store updates to validators, components and view handlers
 *
 * @module store_update_handling
 * @requires redux_validation
 */

var reduxValidation = require( './redux_validation' ),
	_ = require( 'lodash' );

module.exports = {
	/**
	 *
	 * validatorFactoryFunction must return an array of validation dispatchers
	 *
	 * @param {Function} validatorFactoryFunction
	 * @param {store.Store} store
	 * @param {Object} initialValues - initial values for the validation dispatchers so they don't fire on initialization
	 */
	connectValidatorsToStore: function ( validatorFactoryFunction, store, initialValues ) {
		var completeInitialValues = _.extend( {}, store.getState().formContent, initialValues ),
			validators = validatorFactoryFunction( completeInitialValues );
		reduxValidation.createValidationDispatcherCollection( store, validators );
	},
	connectComponentsToStore: function ( components, store ) {
		store.subscribe( function () {
			var state = store.getState(),
				formContent = state.formContent;

			// TODO check if formContent has changed before executing update actions
			components.forEach( function ( component ) {
				component.render( formContent );
			} );
		} );
	},
	connectViewHandlersToStore: function ( viewHandlers, store ) {
		store.subscribe( function () {
			var state = store.getState();
			// TODO check if state has changed before executing update actions

			viewHandlers.forEach( function ( viewHandlerConfig ) {
				viewHandlerConfig.viewHandler.update.call(
					viewHandlerConfig.viewHandler,
					_.get( state, viewHandlerConfig.stateKey )
				);
			} );
		} );
	}
};
