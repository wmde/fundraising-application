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
import { PostalLocalityFilter } from '@/PostalLocalityFilter';
import { InputField } from '@/view_models/Address';

export default Vue.extend( {
	name: 'AutocompleteCity',
	props: {
		placeholder: String,
		city: Object as () => InputField,
		showError: Boolean,
		postcode: String,
		postalLocalityFilter: Object as () => PostalLocalityFilter,
	},
	data() {
		return {
			postalLocalities: [],
			focused: false,
		};
	},
	mounted() {
		this.filterPostalLocalities( this.$props.postcode );
	},
	watch: {
		'postcode': function ( value ) {
			this.filterPostalLocalities( value );
		},
	},
	methods: {
		filterPostalLocalities( postcode: string ) {
			this.$data.postalLocalities = this.$props.postalLocalityFilter.getPostalLocalities( postcode );
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
