<template>
    <div>
        <h1 class="title is-size-1">{{ $t( 'donation_section_email_title' ) }}</h1>
        <div>
            <label for="email">{{ $t( 'email_label' ) }}</label>
            <input v-bind:class="[ 'input', 'is-large', 'has-border-rounded' ]"
                type="text"
                id="email"
                v-model="emailValue"
                @blur="validateEmail"
                :placeholder="$t( 'email_label' )">
            <span v-if="emailHasError" class="help is-danger">{{ $t('form_email_error') }}</span>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { NS_ADDRESS } from '@/store/namespaces';
import { action } from '@/store/util';
import { setEmail, setNewsletterOptIn } from '@/store/address/actionTypes';

export default Vue.extend( {
	name: 'Email',
	data: function () {
		return {
			emailValue: '',
			emailPattern: /^[^@]+@.+$/,
			emailHasError: false,
		};
	},
	methods: {
		setEmail: function () {
			this.$store.dispatch( action( NS_ADDRESS, setEmail ), this.$data.emailValue );
		},
		validateEmail: function () {
			if ( this.emailIsValid() ) {
				this.emailHasError = false;
				this.setEmail();
			} else { this.emailHasError = true; }
		},
		emailIsValid: function () {
			return this.emailPattern.test( this.emailValue );
		},
	},
} );
</script>
