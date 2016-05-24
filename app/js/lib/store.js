var Redux = require( 'redux' ),
	reduxPromise = require( 'redux-promise' ),
	formPagination = require( './reducers/form_pagination' ),
	donationFormContent = require( './reducers/donation_form_content' ),
	membershipFormContent = require( './reducers/membership_form_content' ),
	validity = require( './reducers/validity' ),
	validationMessages = require( './reducers/validation_messages' ),
	membershipInputValidation = require( './reducers/membership_input_validation' ),
	middlewares = [ reduxPromise ];

/* jshint ignore:start */ // Ignore console.log calls
/** @see http://redux.js.org/docs/api/applyMiddleware.html */
function logger( store ) {
	var getState = store.getState;

	return function ( next ) {
		return function ( action ) {
			var returnValue;

			console.log( 'will dispatch', action );

			// Call the next dispatch method in the middleware chain.
			returnValue = next( action );

			console.log( 'state after dispatch', getState() );

			// This will likely be the action itself, unless
			// a middleware further in chain changed it.
			return returnValue;
		};
	};
}

if ( process.env.REDUX_LOG === 'on' ) {
	middlewares.push( logger );
}
/* jshint ignore:end */

// Different stores for different pages, does not violate Reflux pattern
module.exports = {
	createDonationStore: function ( initialState ) {
		return Redux.createStore( Redux.combineReducers( {
			formPagination: formPagination,
			donationFormContent: donationFormContent,
			validity: validity,
			validationMessages: validationMessages
		} ), initialState, Redux.applyMiddleware.apply( this, middlewares ) );
	},
	createMembershipStore: function ( initialState ) {
		return Redux.createStore( Redux.combineReducers( {
			formPagination: formPagination,
			membershipFormContent: membershipFormContent,
			validity: validity,
			validationMessages: validationMessages,
			membershipInputValidation: membershipInputValidation
		} ), initialState, Redux.applyMiddleware.apply( this, middlewares ) );
	}
};
