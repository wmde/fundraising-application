var Redux = require( 'redux' ),
	reduxPromise = require( 'redux-promise' ),
	formPagination = require( './reducers/form_pagination' ),
	formContent = require( './reducers/form_content' ),
	validity = require( './reducers/validity' );

module.exports = Redux.createStore( Redux.combineReducers( {
	formPagination: formPagination,
	formContent: formContent,
	validity: validity
} ), undefined, Redux.applyMiddleware( reduxPromise ) );
