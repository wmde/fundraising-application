<template>
	<div>
		<b-checkbox type="checkbox" class="subtitle has-margin-top-18"
			id="newsletter"
			name="newsletter"
			v-model="newsletterOptIn"
			@change.native="setNewsletterOptIn()">
		</b-checkbox>
		<label for="newsletter">{{ $t( 'donation_sendinfo_label' ) }}</label>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { NS_ADDRESS } from '@/store/namespaces';
import { action } from '@/store/util';
import { setNewsletterOptIn } from '@/store/address/actionTypes';

export default Vue.extend( {
	name: 'NewsletterOptIn',
	data: function () {
		return {
			newsletterOptIn: false,
		};
	},
	methods: {
		setNewsletterOptIn: function () {
			this.$store.dispatch( action( NS_ADDRESS, setNewsletterOptIn ), this.$data.newsletterOptIn );
		},
	},
} );
</script>
