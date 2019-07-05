<template>
<div>
	<div v-bind:class="[{ 'is-invalid': showError.street }]">
		<label for="street" class="subtitle has-margin-top-36">{{ $t( 'donation_form_street_label' ) }}</label>
		<b-input type="text"
				id="street"
				:placeholder="$t('donation_form_street_placeholder')"
				v-model="formData.street.value"
				@blur="$emit('field-changed', 'street'); displayStreetWarning()">
		</b-input>
		<span v-if="showError.street" class="help is-danger">{{ $t('donation_form_street_error') }}</span>
		<span v-if="showWarning" class="help is-warning">{{ $t('donation_form_street_number_warning') }}</span>
	</div>
	<div v-bind:class="[{ 'is-invalid': showError.postcode }]">
		<label for="post-code" class="subtitle has-margin-top-36">{{ $t( 'donation_form_zip_label' ) }}</label>
		<b-input type="text"
				id="post-code"
				v-model="formData.postcode.value"
				:placeholder="$t('donation_form_zip_placeholder')"
				@blur="$emit('field-changed', 'postcode')">
		</b-input>
		<span v-if="showError.postcode" class="help is-danger">{{ $t('donation_form_zip_error') }}</span>
	</div>
	<div v-bind:class="[{ 'is-invalid': showError.city }]">
		<label for="city" class="subtitle has-margin-top-36">{{ $t( 'donation_form_city_label' ) }}</label>
		<b-input type="text"
				id="city"
				:placeholder="$t('donation_form_city_placeholder')"
				v-model="formData.city.value"
				@blur="$emit('field-changed', 'city')">
		</b-input>
		<span v-if="showError.city" class="help is-danger">{{ $t('donation_form_city_error') }}</span>
	</div>
	<div>
		<label for="country" class="subtitle has-margin-top-36">{{ $t( 'donation_form_country_label' ) }}</label>
		<b-select
				class="is-form-input"
				v-model="formData.country.value"
				id="country"
				name="country"
				@blur="$emit('field-changed', 'country')">
			<option v-for="(countryCode, index) in countries"
					:value="countryCode"
					:key="index">{{ $t('donation_form_country_option_' + countryCode ) }}
			</option>
		</b-select>
	</div>
</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressValidity, FormData } from '@/view_models/Address';

export default Vue.extend( {
	name: 'postal',
	props: {
		showError: Object as () => AddressValidity,
		formData: Object as () => FormData,
		countries: Array as () => Array<String>,
	},
	data() {
		return {
			showWarning: false,
		};
	},
	methods: {
		displayStreetWarning() {
			this.showWarning = /^\D+$/.test( this.formData.street.value );
		},

	},
} );
</script>
