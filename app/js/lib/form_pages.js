var Promise = require( 'promise' ),

	FormPage = {
		show: function () {
			jQuery( this.sections ).show();
		},

		hide: function () {
			jQuery( this.sections ).hide();
		},
		validate: function () {
			'use strict';
			var self = this;
			return new Promise( function ( resolve, reject ) {
				if ( self.validation === null ) {
					resolve( null );
					return;
				}
				try {
					resolve( self.validation() );
				} catch ( e ) {
					reject( e );
				}
			} );
		},
		sections: '',
		validation: null
	},

	/**
	 * Create a form page encompassing different sections
	 *
	 * @param {string} sections A CSS selector
	 * @param {Function|null} validation Validation function (optional)
	 * @return {FormPage}
	 */
	createFormPage = function ( sections, validation ) {
		var p = Object.create( FormPage );
		p.sections = sections;
		p.validation = validation || null;
		return p;
	},

	PageCollection = {
		pages: [],
		currentPage: 0,
		displayPage: function ( pageIndex ) {
			this.pages.forEach( function ( page, index )  {
				if ( index === pageIndex ) {
					this.currentPage = index;
					page.show();
				} else {
					page.hide();
				}
			}, this );
		},
		nextPage: function () {
			var self = this;
			return Promise.resolve( this.pages[ this.currentPage ].validate() ).then( function ( validationResult ) {
				'use strict';
				if ( validationResult && validationResult.status === 'OK' ) {
					if ( self.currentPage + 1 === self.pages.length ) {
						throw new Error( 'You are on the last page' );
					}
					self.displayPage( self.currentPage + 1 );
				}
			} );

		}
	},

	/**
	 * Create a multi page form
	 *
	 * @param {FormPage[]} pages
	 * @return {PageCollection}
	 */
	createFormWithPages = function ( pages ) {
		var f = Object.create( PageCollection );
		f.pages = pages;
		f.displayPage( 0 );
		return f;
	};

module.exports = {
	createFormPage: createFormPage,
	createFormwithPages: createFormWithPages
};
