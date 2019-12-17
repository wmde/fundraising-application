<template>
	<div class="autofill-handler" v-on:animationstart="onAutofillAnimationStart"><slot/></div>
</template>

<script lang="ts">
import Vue from 'vue';

/**
	 * Number of milliseconds in which all autofill "events" are collected until they are emitted
	 * as event data. This value needs to strike a balance between the browser's autofill slowly filling
	 * out the fields and the period in which the error messages appear.
	 */
const AUTOFILL_DEBOUNCE_PERIOD = 50;

/**
	 * This class is a hack around the Safari/Edge bug where no events are triggered on autofill.
	 * We get around this by defining keyframe animations for the `:-webkit-autofill` pseudo selector and
	 * listening to the animationstart event for that specific keyframe.
	 *
	 * The autofilled input elements must have an id attributes to show up in the `autofill` event
	 * emitted by this component.
	 *
	 * Based on the comments at https://stackoverflow.com/q/11708092/130121
	 */
export default Vue.extend( {
	name: 'AutofillHandler',
	props: {
		inputSelector: {
			type: String,
			default: 'input[id]',
		},
	},
	data: function () {
		return {
			autofillQueue: new Set<HTMLInputElement>(),
			debounceTimeout: null,
		};
	},
	methods: {
		onAutofillAnimationStart( evt: AnimationEvent ) {
			if ( evt.animationName !== 'onAutoFillStart' ) {
				return;
			}
			this.$data.autofillQueue.add( evt.target );
			if ( this.$data.debounceTimeout !== null ) {
				clearTimeout( this.$data.debounceTimeout );
			}
			this.$data.debounceTimeout = setTimeout( this.emitAutofillValues.bind( this ), AUTOFILL_DEBOUNCE_PERIOD );
		},
		emitAutofillValues() {
			let eventData: { [key: string]: string; } = {};
			this.$data.autofillQueue.forEach( ( elem: HTMLInputElement ) => {
				if ( elem.id ) {
					eventData[ elem.id ] = elem.value;
				}
			} );
			this.$data.debounceTimeout = null;
			this.$emit( 'autofill', eventData );
		},
	},
} );
</script>
