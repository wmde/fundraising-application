<template>
    <b-autocomplete
            class="is-form-input"
            field="cityName"
            :placeholder="$t( 'form_for_example', { example: $t( placeholder ) } )"
            v-model="value"
            name="city"
            id="city"
            :keep-first="true"
            :open-on-focus="true"
            :data="postalLocalities">
    </b-autocomplete>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressValidity } from '@/view_models/Address';

export default Vue.extend( {
	name: 'AutocompleteCity',
	data() {
		return {
			postalLocalities: [],
			value: '',
		};
	},
	mounted() {
		this.filterPostalLocalities();
	},
	watch: {
		'postcode': function ( value ) {
			if ( value === '10829' ) {
				this.$data.postalLocalities = [ 'Berlin1', 'Berlin2' ];
				return;
			}
			this.filterPostalLocalities();
		},
	},
	methods: {
		filterPostalLocalities() {
			this.$data.postalLocalities = [ 'city1', 'city2', 'city3' ];
		},
	},
	props: {
		placeholder: String,
		showError: Object as () => AddressValidity,
		postcode: String,
	},
	computed: {
	},
} );
</script>
