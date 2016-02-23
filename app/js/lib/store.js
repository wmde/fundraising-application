var Redux = require( 'redux' ),
	formPagination = require( './reducers/form_pagination' ),
	validity = require( './reducers/validity' );

module.exports = Redux.createStore( Redux.combineReducers( {
	formPagination: formPagination,
	validity: validity
} ) );
