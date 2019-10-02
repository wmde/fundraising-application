import _Vue from 'vue';

export function FeatureTogglePlugin( Vue: typeof _Vue, options?: FeatureTogglePluginOptions ): void {
	Vue.component( 'feature-toggle', {
		functional: true,
		render( h, ctx ) {
			if ( !options || !options.activeFeatures ) {
				return h();
			}
			const slots = ctx.slots();
			const visibleSlotNames = Object.keys( slots ).filter( slotName => options.activeFeatures.indexOf( slotName ) > -1 );
			if ( visibleSlotNames.length > 0 ) {
				return visibleSlotNames.map( slotName => slots[ slotName ] );
			}
			return h();
		},
	} );
}

export interface FeatureTogglePluginOptions {
	activeFeatures: string,
}
