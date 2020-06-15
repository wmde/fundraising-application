<template>
	<div class="opt-out-box">
		<b-checkbox type="checkbox" class="is-inline-checkbox"
			id="newsletter"
			name="newsletter"
			v-model="newsletterOptIn"
			@change.native="setNewsletterOptIn()">
			{{ $t( 'donation_form_newsletter_label' ) }}
		</b-checkbox>
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
	mounted() {
		this.$data.newsletterOptIn = this.$store.state.address.newsletterOptIn;
	},
	methods: {
		setNewsletterOptIn: function () {
			this.$store.dispatch( action( NS_ADDRESS, setNewsletterOptIn ), this.$data.newsletterOptIn );
		},
	},
} );
</script>
