<template>
	<div :style="{ height: height + 'px' }"></div>
</template>

<script lang="ts">
import Vue from 'vue';
import { onUnmounted, ref, watch } from '@vue/composition-api';

export default Vue.extend( {
	name: 'CookieNotice',
	props: {
		element: HTMLElement,
		elementVisibility: Boolean,
	},
	setup( props: any ) {
		const height = ref( 0 );

		const onElementResize = () => {
			height.value = props.element.offsetHeight;
		};

		const removeEventListeners = () => {
			props.element.removeEventListener( 'click', onElementResize );
			window.removeEventListener( 'resize', onElementResize );
		};

		watch( () => props.element, ( element, previousElement ) => {
			if ( previousElement === null && element !== null ) {
				onElementResize();
				props.element.addEventListener( 'click', onElementResize );
				window.addEventListener( 'resize', onElementResize );
			}
		} );

		watch( () => props.elementVisibility, ( isVisible, wasVisible ) => {
			if ( !isVisible && wasVisible ) {
				removeEventListeners();
			}
		} );

		onUnmounted( removeEventListeners );

		return { height };
	},
} );
</script>
