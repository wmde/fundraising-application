<template>
    <div class="form-input">
		<label for="email" class="subtitle">{{ $t( 'donation_form_email_label' ) }}</label>
		<b-field :type="{ 'is-danger': showError }">
			<b-input type="text"
				id="email"
				:placeholder="$t( 'form_for_example', { example: $t( 'donation_form_email_placeholder' ) } )"
				autocomplete="email"
				v-model="formData.email.value"
				@blur="$emit('field-changed', 'email')">
			</b-input>
		</b-field>
		<span v-if="showError" class="help is-danger">{{ $t( 'donation_form_email_error' ) }}</span>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'Email',
	props: {
		formData: Object as () => FormData,
		showError: Boolean,
	},
} );
</script>
