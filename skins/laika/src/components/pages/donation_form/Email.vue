<template>
    <div>
        <h1 class="title is-size-1">{{ $t( 'donation_section_email_title' ) }}</h1>
        <div>
            <label for="email">{{ $t( 'email_label' ) }}</label>
            <input v-bind:class="[ 'input', 'is-large', 'has-border-rounded' ]"
                type="text"
                id="email"
                v-model="emailValue"
                @blur="setEmail"
                :placeholder="$t( 'email_label' )">
        </div>
        <div>
            <input type="checkbox"
                id="newsletter"
                name="newsletter"
                v-model="newsletterOptIn"
                @change="setNewsletterOptIn">
            <label for="newsletter">{{ $t( 'donation_sendinfo_label' ) }}</label>
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
			newsletterOptIn: false,
		};
	},
	methods: {
		setEmail: function () {
			this.$store.dispatch( action( NS_ADDRESS, setEmail ), this.$data.emailValue );
		},
		setNewsletterOptIn: function () {
			this.$store.dispatch( action( NS_ADDRESS, setNewsletterOptIn ), this.$data.setNewsletterOptIn );
		},
	},
} );
</script>
