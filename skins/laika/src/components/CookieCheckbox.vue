<template>
	<div class="cookie-checkbox" :class="{ open: open }">
		<b-checkbox
			type="checkbox"
			class="is-inline-checkbox cookie-checkbox-checkbox"
			v-model="isChecked"
			:disabled="disabled"
			@change.native="$emit('toggle')"
		>
			<span class="cookie-checkbox-heading">{{ heading }}</span>
			<span class="cookie-checkbox-content">
				{{ content }}
			</span>
		</b-checkbox>
		<a href="" v-on:click="toggleOpen" class="cookie-notice-more">{{ $t( 'cookie_option_more' ) }}</a>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { ref } from '@vue/composition-api';

export default Vue.extend( {
	name: 'CookieCheckbox',
	props: {
		heading: String,
		content: String,
		name: String,
		checked: Boolean,
		disabled: Boolean,
	},
	setup( props: any ) {
		const open = ref( false );
		const isChecked = ref( props.checked );
		const toggleOpen = ( e: Event ) => {
			e.preventDefault();
			open.value = !open.value;
		};

		return { open, isChecked, toggleOpen };
	},
} );
</script>
