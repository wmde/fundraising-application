<template>
	<span>
		<span ref="textToWatch">
			<slot/>
		</span>
		<a href="" v-on:click="toggleText" class="cookie-notice-more" v-if="showMoreLink">
			{{ $t( 'cookie_option_more' ) }}
		</a>
	</span>
</template>

<script lang="ts">
import Vue from 'vue';
import { onMounted, onUnmounted, ref } from '@vue/composition-api';

export default Vue.extend( {
	name: 'TextVisibilityToggle',
	props: {
		heightToShow: Number,
	},
	setup( props: any, { emit } ) {
		const showMoreLink = ref( false );
		const textToWatch = ref<any>( null );

		const toggleText = ( e: Event ) => {
			e.preventDefault();
			emit( 'toggle-text' );
		};

		const onElementResize = () => {
			showMoreLink.value = textToWatch.value.offsetHeight > props.heightToShow;
		};

		onMounted( () => {
			onElementResize();
			window.addEventListener( 'resize', onElementResize );
		} );

		onUnmounted( () => {
			window.removeEventListener( 'resize', onElementResize );
		} );

		return {
			showMoreLink,
			textToWatch,
			toggleText,
		};
	},
} );
</script>
