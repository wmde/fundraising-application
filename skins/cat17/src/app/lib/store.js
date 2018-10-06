var Redux = require( 'redux' ),
	reduxPromise = require( 'redux-promise' ),
	formPagination = require( './reducers/form_pagination' ),
	donationFormContent = require( './reducers/donation_form_content' ),
	donorUpdateFormContent = require( './reducers/donor_update_form_content' ),
	membershipFormContent = require( './reducers/membership_form_content' ),
	validity = require( './reducers/validity' ),
	donationInputValidation = require( './reducers/donation_input_validation' ),
	membershipInputValidation = require( './reducers/membership_input_validation' ),
	asynchronousRequests = require( './reducers/async_requests' ),
	middlewares = [ reduxPromise ];

/* eslint-disable no-console */ // Ignore console.log calls
/**
 * @param {Object} store
 * @see http://redux.js.org/docs/api/applyMiddleware.html
 * @return {Object}
 * */
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
/* eslint-enable no-console */
// Different stores for different pages, does not violate Redux pattern
module.exports = {
	createDonationStore: function ( initialState ) {
		return Redux.createStore( Redux.combineReducers( {
			formPagination: formPagination,
			donationFormContent: donationFormContent,
			validity: validity,
			donationInputValidation: donationInputValidation,
			asynchronousRequests: asynchronousRequests
		} ), initialState, Redux.applyMiddleware.apply( this, middlewares ) );
	},
	createDonorUpdateStore: function ( initialState ) {
		return Redux.createStore( Redux.combineReducers( {
			donorUpdateFormContent: donorUpdateFormContent,
			validity: validity,
			donationInputValidation: donationInputValidation,
			asynchronousRequests: asynchronousRequests
		} ), initialState, Redux.applyMiddleware.apply( this, middlewares ) );
	},
	createMembershipStore: function ( initialState ) {
		return Redux.createStore( Redux.combineReducers( {
			formPagination: formPagination,
			membershipFormContent: membershipFormContent,
			validity: validity,
			membershipInputValidation: membershipInputValidation,
			asynchronousRequests: asynchronousRequests
		} ), initialState, Redux.applyMiddleware.apply( this, middlewares ) );
	}
};
