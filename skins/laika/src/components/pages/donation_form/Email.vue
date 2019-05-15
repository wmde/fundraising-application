<template>
    <div>
        <h1 class="title is-size-1">{{ $t( 'donation_section_email_title' ) }}</h1>
        <div>
            <label for="email">{{ $t( 'email_label' ) }}</label>
            <input v-bind:class="[ 'input', 'is-large', 'has-border-rounded' ]"
                type="text"
                id="email"
                v-model="emailValue"
                :placeholder="$t('email_label')">
        </div>
        <div>
            <input type="checkbox"
                id="newsletter"
                name="newsletter"
                v-model="newsletterOptIn">
            <label for="newsletter">{{ $t( 'donation_sendinfo_label' ) }}</label>
        </div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'Email',
	data: function () {
		return {
			emailValue: '',
			newsletterOptIn: false,
		};
	},
} );
</script>
