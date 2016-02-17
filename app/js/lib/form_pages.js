var FormPage = {
		show: function () {
			jQuery( this.sections ).show();
		},

		hide: function () {
			jQuery( this.sections ).hide();
		},
		sections: ''
	},

	/**
	 * Create a form page encompassing different sections
	 *
	 * @param {string} sections A CSS selector
	 * @return {FormPage}
	 */
	createFormPage = function ( sections ) {
		var p = Object.create( FormPage );
		p.sections = sections;
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
