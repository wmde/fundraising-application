<template>
    <fieldset class="has-margin-top-18">
		<label for="email" class="subtitle">{{ $t( 'donation_form_email_label' ) }}</label>
		<b-input type="text"
			id="email"
			:placeholder="$t( 'donation_form_email_placeholder' )"
			v-model="emailValue"
			@blur="validateEmail">
		</b-input>
		<span v-if="showError.email || emailHasError" class="help is-danger">{{ $t( 'donation_form_email_error' ) }}</span>
    </fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressValidity } from '@/view_models/Address';

export default Vue.extend( {
	name: 'Email',
	data: function () {
		return {
			emailValue: this.$props.initialValue,
			emailPattern: /^[^@]+@.+$/,
			emailHasError: false,
		};
	},
	props: {
		initialValue: String,
		showError: Object as () => AddressValidity,
	},
	methods: {
		setEmail: function () {
			this.$emit( 'email', this.$data.emailValue );
		},
		validateEmail: function () {
			this.emailHasError = !this.emailIsValid();
			if ( !this.emailHasError ) {
				this.setEmail();
			}
		},
		emailIsValid: function () {
			return this.emailPattern.test( this.emailValue );
		},
	},
} );
</script>
