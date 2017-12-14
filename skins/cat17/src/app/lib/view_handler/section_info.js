'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'underscore' ),
	SECTION_STATUS = {
		invalid: 'invalid',
		complete: 'complete',
		disabled: 'disabled'
	},
	DOM_SELECTORS = {
		data: {
			emptyText: 'empty-text',
			displayError: 'display-error'
		},
		classes: {
			errorIcon: 'icon-error',
			summaryBankInfo: 'bank-info',
			sectionInvalid: 'invalid',
			sectionComplete: 'completed',
			sectionDisabled: 'disabled',
			hasLongtext: 'has-longtext',
			opened: 'opened'
		}
	},

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
	SectionInfo = {
		container: null,
		icon: null,
		text: null,
		longText: null,

		// mappings between possible form values and content to use
		valueIconMap: {},
		valueTextMap: {},
		valueLongTextMap: {},

		update: function ( value, validity ) {
			this.defaultBehavior( value, validity );
		},
		/**
		 *
		 * @param {*} value
		 * @param {validation_result} validity
		 */
		defaultBehavior: function ( value, validity ) {
			this.setIcon( this.getValueIcon( value ) );
			this.setText( this.getValueText( value ) );
			this.setLongText( this.getValueLongText( value ) );

			if ( validity ) {
				if ( validity.dataEntered === false ) {
					this.setSectionStatus( SECTION_STATUS.disabled );
				} else {
					if ( validity.isValid === true ) {
						this.setSectionStatus( SECTION_STATUS.complete );
					} else {
						this.setSectionStatus( SECTION_STATUS.invalid );
					}
				}
			}
		},
		getValueIcon: function ( value ) {
			return this.valueIconMap[ value ];
		},
		getValueText: function ( value ) {
			return this.valueTextMap[ value ];
		},
		getValueLongText: function ( value ) {
			return this.valueLongTextMap[ value ];
		},
		setText: function ( text ) {
			if ( !this.text ) {
				return;
			}

			if ( text === undefined ) {
				text = this.text.data( DOM_SELECTORS.data.emptyText );
			}

			this.text.text( text );
		},
		/**
		 * Fill the longText element with text. Make sure to call setLongTextIndication if you run your own implementation.
		 * @param {string} longText
		 */
		setLongText: function ( longText ) {
			if ( !this.longText ) {
				return;
			}

			this.longText.text( longText );

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
				// only configured icon are supposed to communicate validation problems
				// @todo Consider always applying the class and decide not to have UI effects in CSS
				if ( this.icon.data( DOM_SELECTORS.data.displayError ) === true ) {
					this.icon.addClass( DOM_SELECTORS.classes.errorIcon );
				}
			}
			else {
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
		}
	},

	AmountFrequencySectionInfo = objectAssign( Object.create( SectionInfo ), {
		// todo Inject actual currency formatter (that knows how to format it depending on locale and incl currency symbol)
		currencyFormatter: null,
		update: function ( amount, paymentInterval, aggregateValidity ) {
			if ( aggregateValidity.isValid === true ) {
				this.setSectionStatus( SECTION_STATUS.complete );
			} else if ( aggregateValidity.isValid === false ) {
				this.setSectionStatus( SECTION_STATUS.invalid );
			} else {
				this.setSectionStatus( SECTION_STATUS.disabled );
			}

			this.setIcon( this.getValueIcon( paymentInterval ) );

			if ( this.text ) {
				this.setText(
					amount === 0 ?
						this.text.data( DOM_SELECTORS.data.emptyText ) :
						this.currencyFormatter.format( amount ) + ' €'
				);
			}

			this.setLongText( this.getValueLongText( paymentInterval ) );
		}
	} ),

	PaymentTypeSectionInfo = objectAssign( Object.create( SectionInfo ), {
		update: function( paymentType, iban, bic, aggregateValidity ) {
			if ( aggregateValidity.isValid === true ) {
				this.setSectionStatus( SECTION_STATUS.complete );
			} else if ( aggregateValidity.isValid === false ) {
				this.setSectionStatus( SECTION_STATUS.invalid );
			} else {
				this.setSectionStatus( SECTION_STATUS.disabled );
			}

			this.setIcon( this.getValueIcon( paymentType ) );

			if ( this.text ) {
				this.setText(
					!aggregateValidity.dataEntered ?
						this.text.data( DOM_SELECTORS.data.emptyText ) :
						this.getValueText( paymentType )
				);
			}

			if ( paymentType !== 'BEZ' ) {
				this.setLongText( '' );
				return;
			}

			this.setLongText( this.getValueLongText( paymentType ) );

			if ( this.longText && iban && bic ) {
				this.longText.prepend( // intentionally html. Escaping performed through .text() calls on user-input vars
					$( '<dl>' ).addClass( DOM_SELECTORS.classes.summaryBankInfo ).append(
						$('<dt>').text( 'IBAN' ),
						$('<dd>').text( iban ),
						$('<dt>').text( 'BIC' ),
						$('<dd>').text( bic )
					)
				);
			}
		}
	} ),

	DonorTypeSectionInfo = objectAssign( Object.create( SectionInfo ), {
		countryNames: null,
		update: function( addressType, salutation, title, firstName, lastName, companyName, street, postcode, city, country, email, aggregateValidity ) {
			if ( aggregateValidity.isValid === true ) {
				this.setSectionStatus( SECTION_STATUS.complete );
			} else if ( aggregateValidity.isValid === false ) {
				this.setSectionStatus( SECTION_STATUS.invalid );
			} else {
				this.setSectionStatus( SECTION_STATUS.disabled );
			}

			this.setIcon( this.getValueIcon( addressType ) );

			if ( this.text ) {
				this.setText(
					!aggregateValidity.dataEntered ?
						this.text.data( DOM_SELECTORS.data.emptyText ) :
						this.getValueText( addressType )
				);
			}

			if ( !this.longText ) {
				return;
			}

			var wrapperTag = '<span>';
			var longtext = $( wrapperTag );
			// TODO Reuse AddressDisplayHandler maybe?
			if ( addressType === 'person' && firstName !== '' && lastName !== '' ) {
				longtext.append( $( wrapperTag ).text( salutation + ' ' + title + ' ' + firstName + ' ' + lastName ), '<br>' );
			}
			else if ( addressType === 'firma' && companyName !== '' ) {
				longtext.append( $( wrapperTag ).text( companyName ), '<br>' );
			}
			if ( street !== '' ) {
				longtext.append( $( wrapperTag ).text( street ), '<br>' );
			}
			if ( postcode !== '' && city !== '' ) {
				longtext.append( $( wrapperTag ).text( postcode + ' ' + city ), '<br>' );
			}
			if ( country !== '' ) {
				longtext.append( $( wrapperTag ).text( this.countryNames[ country ] ), '<br>' );
			}
			if ( email !== '' ) {
				longtext.append( $( wrapperTag ).text( email ), '<br>' );
			}

			this.longText.html( longtext );
			// we worked around setLongText so have to clean up manually
			this.setLongTextIndication( true );
		}
	} ),

	/**
	 * Create a widget instance with all properties set-up
	 *
	 * @param {string} type
	 * @param {jQuery} widgetNode A HTML node representing a widget
	 * @param {object} valueIconMap Mapping of value to icon
	 * @param {object} valueTextMap Mapping of value to text
	 * @param {object} valueLongTextMap Mapping of value to longText
	 * @param {object} additionalDependencies Additional properties that will be merged into the instance of type
	 * @return {SectionInfo} or a child
	 */
	createInstance = function ( type, widgetNode, valueIconMap, valueTextMap, valueLongTextMap, additionalDependencies ) {
		return objectAssign(
			Object.create( type ),
			{
				container: widgetNode,

				// calculate and cache elements
				icon: widgetNode.find( 'i:not(".link")' ),
				text: widgetNode.find( '.text' ),
				longText: widgetNode.find( '.info-detail' ),

				valueIconMap: valueIconMap,
				valueTextMap: valueTextMap,
				valueLongTextMap: valueLongTextMap
			},
			additionalDependencies
		);
	},

	/**
	 * Proxy that can take DOM `containers` describing widgets, maps them to one widget instance each, forward calls to them
	 *
	 * We still use jQuery as the selector engine for sub-elements. Possible todo
	 *
	 * @param type
	 * @param {jQuery} containers A list of HTML node representing a widget (matched by the same selector)
	 * @param {object} valueIconMap Mapping of value to icon
	 * @param {object} valueTextMap Mapping of value to text
	 * @param {object} valueLongTextMap Mapping of value to longText
	 * @param {object} additionalDependencies Additional properties that will be merged into the instance of type
	 * @return {SectionInfo} or a child
	 */
	createProxy = function ( type, containers, valueIconMap, valueTextMap, valueLongTextMap, additionalDependencies ) {
 		var widgets = [];
		_.each( containers.get(), function( container ) {
			widgets.push( createInstance( type, $( container ), valueIconMap, valueTextMap, valueLongTextMap, additionalDependencies ) );
		} );

		return objectAssign( {
			widgets: widgets,
			update: function () {
				var originalArgs = arguments;
				// There is no _.apply unfortunately and _.invoke can't pass `arguments`
				_.each( this.widgets, function ( widget ) {
					widget.update.apply( widget, originalArgs );
				} );
			}
		} );
	}
;

module.exports = {
	createInstance: createInstance,
	createProxy: createProxy,
	SectionInfo: SectionInfo,
	AmountFrequencySectionInfo: AmountFrequencySectionInfo,
	PaymentTypeSectionInfo: PaymentTypeSectionInfo,
	DonorTypeSectionInfo: DonorTypeSectionInfo,
	createFrequencySectionInfo: function ( containers, valueIconMap, valueTextMap, valueLongTextMap ) {
		return createProxy( SectionInfo, containers, valueIconMap, valueTextMap, valueLongTextMap );
	},
	createAmountFrequencySectionInfo: function ( containers, valueIconMap, valueTextMap, valueLongTextMap, currencyFormatter ) {
		return createProxy( AmountFrequencySectionInfo, containers, valueIconMap, valueTextMap, valueLongTextMap, {
			currencyFormatter: currencyFormatter
		} );
	},
	createPaymentTypeSectionInfo: function ( containers, valueIconMap, valueTextMap, valueLongTextMap ) {
		return createProxy( PaymentTypeSectionInfo, containers, valueIconMap, valueTextMap, valueLongTextMap );
	},
	createDonorTypeSectionInfo: function ( containers, valueIconMap, valueTextMap, countryNames ) {
		return createProxy( DonorTypeSectionInfo, containers, valueIconMap, valueTextMap, {}, {
			countryNames: countryNames
		} );
	},
	createMembershipTypeSectionInfo: function ( containers, valueIconMap, valueTextMap, valueLongTextMap ) {
		return createProxy( SectionInfo, containers, valueIconMap, valueTextMap, valueLongTextMap );
	}
};
