'use strict';

var Factory = require( './factory' ),
	Base = require( './base' ),
	AmountFrequency = require( './types/amount_frequency' ),
	DonorType = require( './types/donor_type' ),
	PaymentType = require( './types/payment_type' )
;

module.exports = {
	createFrequencySectionInfo: function ( containers, valueIconMap, valueTextMap, valueLongTextMap ) {
		return Factory.createProxy( Base, containers, valueIconMap, valueTextMap, valueLongTextMap );
	},
	createAmountFrequencySectionInfo: function ( containers, valueIconMap, valueTextMap, valueLongTextMap, currencyFormatter ) {
		return Factory.createProxy( AmountFrequency, containers, valueIconMap, valueTextMap, valueLongTextMap, {
			currencyFormatter: currencyFormatter
		} );
	},
	createPaymentTypeSectionInfo: function ( containers, valueIconMap, valueTextMap, valueLongTextMap ) {
		return Factory.createProxy( PaymentType, containers, valueIconMap, valueTextMap, valueLongTextMap );
	},
	createDonorTypeSectionInfo: function ( containers, valueIconMap, valueTextMap, countryNames ) {
		return Factory.createProxy( DonorType, containers, valueIconMap, valueTextMap, {}, {
			countryNames: countryNames
		} );
	},
	createMembershipTypeSectionInfo: function ( containers, valueIconMap, valueTextMap, valueLongTextMap ) {
		return Factory.createProxy( Base, containers, valueIconMap, valueTextMap, valueLongTextMap );
	}
};
