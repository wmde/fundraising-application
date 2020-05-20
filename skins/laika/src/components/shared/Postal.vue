<template>
<div>
	<div v-bind:class="['form-input', { 'is-invalid': showError.street, 'is-warning': showWarning }]">
		<label for="street" class="subtitle">{{ $t( 'donation_form_street_label' ) }}</label>
		<b-field :type="{ 'is-danger': showError.street, 'is-warning': showWarning && !showError.street }">
			<b-input type="text"
					id="street"
					:placeholder="$t( 'form_for_example', { example: $t( 'donation_form_street_placeholder' ) } )"
					autocomplete="street-address"
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
					autocomplete="postal-code"
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

	<div v-bind:class="['form-input', { 'is-invalid': showError.country }]">
		<label for="country" class="subtitle">{{ $t( 'donation_form_country_label' ) }}</label>
		<b-field :type="{ 'is-danger': showError.country }">
			<b-autocomplete
					class="is-form-input"
					field="countryFullName"
					:placeholder="$t( 'form_for_example', { example: countries[0].countryFullName } )"
					v-model="countryInput"
					name="country"
					id="country"
					:keep-first="keepFirst"
					:open-on-focus="openOnFocus"
					:data="filteredCountries"
					@focus="() => focusCountryField()"
					@input="value => changeCountry( value )">
			</b-autocomplete>
		</b-field>
		<span v-if="showError.country" class="help is-danger">{{ $t('donation_form_country_error') }}</span>
	</div>
</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressValidity, AddressFormData } from '@/view_models/Address';
import { Country } from '@/view_models/Country';

const DEFAULT_POSTAL_REGEX = '^.+$';

export default Vue.extend( {
	name: 'postal',
	props: {
		showError: Object as () => AddressValidity,
		formData: Object as () => AddressFormData,
		countries: Array as () => Array<Country>,
	},
	data() {
		return {
			showWarning: false,
			keepFirst: true,
			openOnFocus: true,
			countryInput: 'Deutschland',
			countryClicked: false,
			countryFocused: false,
			countryInitialised: false,
		};
	},
	watch: {
		'formData.country.value': function ( value ) {
			if ( this.countryInitialised ) {
				return;
			}

			const country = this.countries.find( ( c: Country ) => c.countryCode === value );
			if ( country !== undefined && this.countryInput !== country.countryFullName ) {
				this.countryInitialised = true;
				this.countryInput = country.countryFullName;
				this.formData.postcode.pattern = country.postCodeValidation;
				this.$emit( 'field-changed', 'postcode' );
			}
		},
	},
	methods: {
		displayStreetWarning() {
			this.showWarning = /^\D+$/.test( this.formData.street.value );
		},
		focusCountryField() {
			if ( !this.$data.countryClicked ) {
				this.formData.country.value = '';
				this.$data.countryInput = '';
			}
		},
		changeCountry( option: string ) {
			let country = this.$props.countries.find( ( c: Country ) => c.countryFullName === option );
			if ( country ) {
				this.formData.postcode.pattern = country.postCodeValidation;
				this.formData.country.value = country.countryCode;
			} else {
				this.formData.postcode.pattern = DEFAULT_POSTAL_REGEX;
				this.formData.country.value = '';
			}
			if ( this.$data.countryClicked ) {
				this.$emit( 'field-changed', 'country' );
				this.$emit( 'field-changed', 'postcode' );
			}
			this.$data.countryClicked = true;
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

		filteredCountries(): Array<Country> {
			return this.countries.filter( ( countryOption: Country ) => {
				return countryOption.countryFullName
					.toString()
					.toLowerCase()
					.indexOf( this.$data.countryInput.toLowerCase() ) >= 0;
			} );
		},
	},
} );
</script>
