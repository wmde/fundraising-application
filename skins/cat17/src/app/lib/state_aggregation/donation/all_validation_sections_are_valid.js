'use strict';

module.exports = function ( state ) {
	return ( state.validity.paymentData === true && state.validity.address === true && state.validity.bankData !== false );
};
