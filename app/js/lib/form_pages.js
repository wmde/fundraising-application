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
		pages: {},
		displayPage: function ( name ) {
			var pageName;
			for ( pageName in this.pages ) {
				if ( this.pages.hasOwnProperty( pageName ) ) {
					if ( pageName === name ) {
						this.pages[ pageName ].show();
					} else {
						this.pages[ pageName ].hide();
					}
				}
			}
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
		return f;
	};

module.exports = {
	createFormPage: createFormPage,
	createFormwithPages: createFormWithPages
};
