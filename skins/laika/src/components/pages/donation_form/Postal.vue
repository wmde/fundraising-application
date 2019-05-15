<template>
<div>
	<div v-bind:class="[{ 'is-invalid': showError.street }]">
		<label for="street" class="subtitle has-margin-top-36">{{ $t( 'street_label' ) }}</label>
		<b-input type="text"
				id="street"
				name="street"
				:placeholder="$t('street_label')"
				v-model="formData.street.value"
				@blur="validateInput(formData, 'street'); displayStreetWarning()">
		</b-input>
		<span v-if="showError.street" class="help is-danger">{{ $t('form_street_error') }}</span>
		<span v-if="showWarning" class="help is-warning">{{ $t('form_street_number_warning') }}</span>
	</div>
	<div v-bind:class="[{ 'is-invalid': showError.postcode }]">
		<label for="post-code" class="subtitle has-margin-top-36">{{ $t( 'zip_label' ) }}</label>
		<b-input type="text"
				id="post-code"
				name="postcode"
				v-model="formData.postcode.value"
				:placeholder="$t('zip_label')"
				@blur="validateInput(formData, 'postcode')">
		</b-input>
		<span v-if="showError.postcode" class="help is-danger">{{ $t('form_zip_error') }}</span>
	</div>
	<div v-bind:class="[{ 'is-invalid': showError.city }]">
		<label for="city" class="subtitle has-margin-top-36">{{ $t( 'city_label' ) }}</label>
		<b-input type="text"
				id="city"
				name="city"
				:placeholder="$t('city_label')"
				v-model="formData.city.value"
				@blur="validateInput(formData, 'city')">
		</b-input>
		<span v-if="showError.city" class="help is-danger">{{ $t('form_city_error') }}</span>
	</div>
	<div>
		<label for="country" class="subtitle has-margin-top-36">{{ $t( 'country_label' ) }}</label>
		<b-select
				class="is-form-input"
				v-model="formData.country.value"
				id="country"
				name="country"
				@blur="validateInput(formData, 'title')">
			<option v-for="(countryCode, index) in countries"
					:value="countryCode"
					:key="index">{{ $t('country_option_' + countryCode ) }}
			</option>
		</b-select>
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
		formData: Object as () => FormData,
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
