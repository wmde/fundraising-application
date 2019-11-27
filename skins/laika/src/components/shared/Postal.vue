<template>
<div>
	<div v-bind:class="['form-input', { 'is-invalid': showError.street, 'is-warning': showWarning }]">
		<label for="street" class="subtitle">{{ $t( 'donation_form_street_label' ) }}</label>
		<b-field :type="{ 'is-danger': showError.street, 'is-warning': showWarning && !showError.street }">
			<b-input type="text"
					id="street"
					:placeholder="$t( 'form_for_example', { example: $t( 'donation_form_street_placeholder' ) } )"
					v-model="formData.street.value"
					@blur="$emit('field-changed', 'street'); displayStreetWarning()">
			</b-input>
		</b-field>
		<span v-if="showError.street" class="help is-danger">{{ $t('donation_form_street_error') }}</span>
		<span v-if="showWarning" class="help">{{ $t('donation_form_street_number_warning') }}</span>
		<span v-if="streetValueEqualsPlaceholder" class="help">{{ $t('donation_form_street_placeholder_warning') }}</span>
	</div>
	<div v-bind:class="['form-input', { 'is-invalid': showError.postcode }]">
		<label for="post-code" class="subtitle">{{ $t( 'donation_form_zip_label' ) }}</label>
		<b-field :type="{ 'is-danger': showError.postcode }">
			<b-input type="text"
					id="post-code"
					v-model="formData.postcode.value"
					:placeholder="$t( 'form_for_example', { example: $t( 'donation_form_zip_placeholder' ) } )"
					@blur="$emit('field-changed', 'postcode')">
			</b-input>
		</b-field>
		<span v-if="showError.postcode" class="help is-danger">{{ $t('donation_form_zip_error') }}</span>
		<span v-if="zipValueEqualsPlaceholder" class="help">{{ $t('donation_form_zip_placeholder_warning') }}</span>
	</div>
	<div v-bind:class="['form-input', { 'is-invalid': showError.city }]">
		<label for="city" class="subtitle">{{ $t( 'donation_form_city_label' ) }}</label>
		<b-field :type="{ 'is-danger': showError.city }">
			<b-input type="text"
					id="city"
					:placeholder="$t( 'form_for_example', { example: $t( 'donation_form_city_placeholder' ) } )"
					v-model="formData.city.value"
					@blur="$emit('field-changed', 'city')">
			</b-input>
		</b-field>
		<span v-if="showError.city" class="help is-danger">{{ $t('donation_form_city_error') }}</span>
		<span v-if="cityValueEqualsPlaceholder" class="help">{{ $t('donation_form_city_placeholder_warning') }}</span>
	</div>
	<div class="form-input">
		<label for="country" class="subtitle">{{ $t( 'donation_form_country_label' ) }}</label>
		<b-field>
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
		</b-field>
	</div>
</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressValidity, AddressFormData } from '@/view_models/Address';

export default Vue.extend( {
	name: 'postal',
	props: {
		showError: Object as () => AddressValidity,
		formData: Object as () => AddressFormData,
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
	computed: {
		streetValueEqualsPlaceholder(): boolean {
			return this.$props.formData.street.value === this.$t( 'donation_form_street_placeholder' );
		},
		cityValueEqualsPlaceholder(): boolean {
			return this.$props.formData.city.value === this.$t( 'donation_form_city_placeholder' );
		},
		zipValueEqualsPlaceholder(): boolean {
			return this.$props.formData.postcode.value === this.$t( 'donation_form_zip_placeholder' );
		},
	},
} );
</script>
