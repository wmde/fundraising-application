'use strict';

var Promise = require( 'promise' ),

	FormPage = {
		show: function () {
			this.sections.show();
		},

		hide: function () {
			this.sections.hide();
		},
		validate: function () {
			var self = this;
			return new Promise( function ( resolve, reject ) {
				if ( self.validation === null ) {
					resolve( null );
					return;
				}
				try {
					resolve( self.validation( self.sections ) );
				} catch ( e ) {
					reject( e );
				}
			} );
		},
		sections: null,
		validation: null
	},

	/**
	 * Create a form page from a jQuery object
	 *
	 * @param {jQuery} $sections jQuery object containing all elements of the form page
	 * @param {Function|null} validation Validation function (optional).
	 *        The function will get the section as parameter and is expected to return an object containing a
	 *        `status` key with 'OK' for a valid result.
	 * @return {FormPage}
	 */
	createFormPage = function ( $sections, validation ) {
		var p = Object.create( FormPage );
		p.sections = $sections;
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
