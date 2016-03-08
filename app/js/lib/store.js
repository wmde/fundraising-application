var Redux = require( 'redux' ),
	reduxPromise = require( 'redux-promise' ),
	formPagination = require( './reducers/form_pagination' ),
	formContent = require( './reducers/form_content' ),
	validity = require( './reducers/validity' ),
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

module.exports = Redux.createStore( Redux.combineReducers( {
	formPagination: formPagination,
	formContent: formContent,
	validity: validity
} ), undefined, Redux.applyMiddleware.apply( this, middlewares ) );

