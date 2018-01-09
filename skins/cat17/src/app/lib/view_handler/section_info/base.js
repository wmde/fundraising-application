'use strict';

var _ = require( 'underscore' ),
	SECTION_STATUS = require( './section_status' ),
	DOM_SELECTORS = require( './dom_selectors' )
;

/**
 * Base class updating mark-up of widgets, repeatedly present in the page, indicating form progress
 *
 * Example:
 * .amount <- set class according to SECTION_STATUS (calculated from validity)
 * |- i <- add class from valueIconMap (or error icon depending on validity)
 * |- span.text <- set text from valueTextMap (with tick for fallback text from data attribute in case of unset value)
 * |- div.info-text-bottom <- set text from valueLongTextMap
 *
 * Not all widgets have to have all features (icon, text, longText), so checks are in place to make widgets flexible
 *
 * In the default set-up it knows how to map a value passed to update to the range of possible values and present it.
 * It does not, by default, set section status as this needs some form of validity.
 */
module.exports = {
	SECTION_STATUS: SECTION_STATUS,
	DOM_SELECTORS: DOM_SELECTORS,

	container: null,
	icon: null,
	text: null,
	longText: null,

	// mappings between possible form values and content to use
	valueIconMap: {},
	valueTextMap: {},
	valueLongTextMap: {},

	/**
	 * @param {*} value
	 * @param {validation_result} validity
	 */
	update: function ( value, validity ) {
		this.setSectionStatusFromValidity( validity );

		this.setIcon( this.getValueIcon( value ) );
		this.setText( this.getValueText( value ) );
		this.setLongText( this.getValueLongText( value ) );
	},
	getValueIcon: function ( value ) {
		return this.valueIconMap[ value ];
	},
	getFallbackIcon: function () {
		if ( !this.icon ) {
			return null;
		}

		// only configured icon are supposed to communicate validation problems
		// @todo Consider always applying the class and decide not to have UI effects in CSS
		if ( this.icon.data( DOM_SELECTORS.data.displayError ) !== true ) {
			return null;
		}

		return DOM_SELECTORS.classes.errorIcon;
	},
	getValueText: function ( value ) {
		return this.valueTextMap[ value ];
	},
	getFallbackText: function () {
		if ( !this.text ) {
			return '';
		}

		return this.text.data( DOM_SELECTORS.data.emptyText );
	},
	getValueLongText: function ( value ) {
		return this.valueLongTextMap[ value ];
	},
	setText: function ( text ) {
		if ( !this.text ) {
			return;
		}

		if ( text === undefined ) {
			text = this.getFallbackText();
		}

		this.text.text( text );
	},
	setLongText: function ( longText ) {
		if ( !this.longText ) {
			return;
		}

		this.longText.text( longText );

		this.setLongTextIndication( longText !== '' );
	},
	setLongTextHtml: function ( longText ) {
		if ( !this.longText ) {
			return;
		}

		this.longText.html( longText );

		this.setLongTextIndication( longText !== '' );
	},
	/**
	 * Indicate that a relevant text is part of this widget (i.e. it's not empty)
	 * @param {boolean} hasLongText
	 */
	setLongTextIndication: function ( hasLongText ) {
		this.container.toggleClass( DOM_SELECTORS.classes.hasLongtext, hasLongText );
		// collapse possibly opened longtext (see formInfosManager)
		this.container.removeClass( DOM_SELECTORS.classes.opened );
		this.container.find( '.' + DOM_SELECTORS.classes.opened ).removeClass( DOM_SELECTORS.classes.opened );
	},
	setIcon: function ( icon ) {
		if ( !this.icon ) {
			return;
		}

		this.icon.removeClass( DOM_SELECTORS.classes.errorIcon );
		this.icon.removeClass( _.values( this.valueIconMap ).join( ' ' ) );

		if ( icon === undefined ) {
			icon = this.getFallbackIcon();
		}

		if ( typeof icon === 'string' ) {
			this.icon.addClass( icon );
		}
	},
	setSectionStatus: function ( status ) {
		this.container.removeClass( [ DOM_SELECTORS.classes.sectionComplete, DOM_SELECTORS.classes.sectionDisabled, DOM_SELECTORS.classes.sectionInvalid ].join( ' ' ) );
		if ( status === 'invalid' ) {
			this.container.addClass( DOM_SELECTORS.classes.sectionInvalid );
		} else if ( status === 'complete' ) {
			this.container.addClass( DOM_SELECTORS.classes.sectionComplete );
		} else {
			this.container.addClass( DOM_SELECTORS.classes.sectionDisabled );
		}
	},
	setSectionStatusFromValidity: function ( validity ) {
		if ( !validity ) {
			return;
		}

		if ( validity.isValid === true ) {
			this.setSectionStatus( SECTION_STATUS.complete );
		} else if ( validity.dataEntered === true && validity.isValid === false ) {
			this.setSectionStatus( SECTION_STATUS.invalid );
		} else {
			this.setSectionStatus( SECTION_STATUS.disabled );
		}
	}
};

