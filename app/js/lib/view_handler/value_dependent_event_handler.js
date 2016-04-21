'use strict';

var objectAssign = require( 'object-assign' ),
	_ = require( 'lodash' ),
	ValueDependentEventHandler = {
		eventHandlerConfig: [],
		handlers: [],
		update: function ( value ) {
			var i;
			this.handlers = [];
			for ( i = 0; i < this.eventHandlerConfig.length; i++ ) {
				if ( this.eventHandlerConfig[ i ][ 0 ].test( value ) ) {
					this.handlers.push( this.eventHandlerConfig[ i ][ 1 ] );
				}
			}
		},
		handleEvents: function ( evt ) {
			var i;
			for ( i = 0; i < this.handlers.length; i++ ) {
				this.handlers[ i ]( evt );
			}
		}
	};

module.exports = {
	createHandler: function ( element, eventNames, eventHandlerConfig ) {
		var handler = objectAssign( Object.create( ValueDependentEventHandler ), {
			eventHandlerConfig: eventHandlerConfig
		} );
		// need to use bind to set the correct value for `this` in handleEvents
		element.on( eventNames, _.bind( handler.handleEvents, handler ) );
		return handler;
	}
};
