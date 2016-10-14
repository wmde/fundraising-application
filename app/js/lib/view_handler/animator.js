'use strict';

var objectAssign = require( 'object-assign' ),

	ElementAnimator = {
		el: null,
		showElement: function () {
			throw new Error( 'This is an abstract class!' );
		},
		hideElement: function () {
			throw new Error( 'This is an abstract class!' );
		}
	},

	SlidingElementAnimator = objectAssign( Object.create( ElementAnimator ), {
		// internal fields
		slideSpeed: 600,

		showElement: function () {
			this.el
				.slideDown( this.slideSpeed )
				.animate(
					{ opacity: 1 },
					{ queue: false, duration: this.slideSpeed }
				);
		},
		hideElement: function () {
			this.el
				.slideUp( this.slideSpeed )
				.animate(
					{ opacity: 0 },
					{ queue: false, duration: this.slideSpeed }
				);
		}
	} ),

	SimpleElementAnimator = objectAssign( Object.create( ElementAnimator ), {
		showElement: function () {
			this.el.show();
		},
		hideElement: function () {
			this.el.hide();
		}
	} );

module.exports = {
	createSimpleElementAnimator: function ( element ) {
		return objectAssign(
			Object.create( SimpleElementAnimator ),
			{ el: element }
		);
	},

	createSlidingElementAnimator: function ( element ) {
		return objectAssign(
			Object.create( SlidingElementAnimator ),
			{ el: element }
		);
	},

	ElementAnimator: ElementAnimator
};
