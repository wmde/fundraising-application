<template>
    <b-autocomplete
            class="is-form-input"
            field="cityName"
            :placeholder="$t( 'form_for_example', { example: $t( placeholder ) } )"
            v-model="city.value"
            name="city"
            id="city"
            :keep-first="true"
            :open-on-focus="true"
            :data="postalLocalities"
			@focus="onFocus"
			@input="onInput"
			@blur="onBlur">
        <template slot-scope="props">
            <strong>{{ postcode }}</strong> {{ props.option }}
        </template>
    </b-autocomplete>
</template>

<script lang="ts">
import Vue from 'vue';
import { PostalLocalityResource } from '@/PostalLocalityResource';
import { InputField } from '@/view_models/Address';

const postcodePattern = /^[0-9]{5}$/;

export default Vue.extend( {
	name: 'AutocompleteCity',
	props: {
		placeholder: String,
		city: Object as () => InputField,
		showError: Boolean,
		postcode: String,
		postalLocalityResource: Object as () => PostalLocalityResource,
	},
	data() {
		return {
			postalLocalities: [],
			focused: false,
			currentPostcode: '',
		};
	},
	mounted() {
		this.getPostalLocalities( this.$props.postcode );
	},
	watch: {
		'postcode': function ( value ) {
			if ( !postcodePattern.test( value ) ) {
				return;
			}
			if ( value === this.$data.currentPostcode ) {
				return;
			}
			this.$data.currentPostcode = value;
			this.getPostalLocalities( value );
		},
	},
	methods: {
		getPostalLocalities( postcode: string ) {
			this.$props.postalLocalityResource.getPostalLocalities( postcode ).then(
				( localities: Array<string> ) => {
					this.$data.postalLocalities = localities;
				}
			);
		},
		onFocus() {
			this.$data.focused = true;
		},
		onBlur() {
			this.$data.focused = false;
			this.$emit( 'field-changed' );
		},
		onInput() {
			// Blur sometimes runs before input/select events but then has no value
			// so we need to re-validate if input happened while not focused
			if ( !this.$data.focused ) {
				this.$emit( 'field-changed' );
			}
		},
	},
	computed: {
	},
} );
</script>
