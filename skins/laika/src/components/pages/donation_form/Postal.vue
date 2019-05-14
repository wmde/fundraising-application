<template>
<div>
	<div v-bind:class="[{ invalid: showError.street }]">
		<label for="street">{{ $t('street_label') }}</label>
		<input type="text"
				id="street"
				name="street"
				:placeholder="$t('street_label')"
				v-model="formData.street.value"
				@blur="validateInput(formData, 'street'); displayStreetWarning()">
		<span v-if="showError.street" class="error-text">{{ $t('form_street_error') }}</span>
		<span v-if="showWarning" class="warning-text">{{ $t('form_street_number_warning') }}</span>
	</div>
	<div v-bind:class="[{ invalid: showError.postcode }]">
		<label for="post-code">{{ $t('zip_label') }}</label>
		<input type="text"
				id="post-code"
				name="postcode"
				v-model="formData.postcode.value"
				:placeholder="$t('zip_label')"
				@blur="validateInput(formData, 'postcode')">
		<span v-if="showError.postcode" class="error-text">{{ $t('form_zip_error') }}</span>
	</div>
	<div v-bind:class="[{ invalid: showError.city }]">
		<label for="city">{{ $t('city_label') }}</label>
		<input type="text"
				id="city"
				name="city"
				:placeholder="$t('city_label')"
				v-model="formData.city.value"
				@blur="validateInput(formData, 'city')">
		<span v-if="showError.city" class="error-text">{{ $t('form_city_error') }}</span>
	</div>
	<div>
		<label for="country">{{ $t('country_label') }}</label>
		<select id="country"
				name="country"
				class="country-select"
				v-model="formData.country.value"
				@blur="validateInput(formData, 'country')">
			<option v-for="(countryCode, index) in countries"
					:value="countryCode"
					:key="index">{{ $t('country_option_' + countryCode ) }}
			</option>
		</select>
	</div>
</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { AddressValidity, FormData } from '@/view_models/Address';

export default Vue.extend( {
	name: 'postal',
	props: {
		showError: Object as () => AddressValidity,
		formData:  Object as () => FormData,
		validateInput: Function,
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
