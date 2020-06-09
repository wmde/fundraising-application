<template>
	<fieldset class="form-input form-input__vertical-option-list">
		<legend class="subtitle">{{ $t( 'donation_form_section_address_header_type' ) }}</legend>
		<div class="radio-container">
			<b-radio id="personal"
					name="addressTypeInternal"
					v-model="type"
					:native-value="AddressTypeModel.PERSON"
					@change.native="setAddressType()">{{ $t( 'donation_form_addresstype_option_private' ) }}
			</b-radio>
			<b-radio id="company"
					name="addressTypeInternal"
					v-model="type"
					:native-value="AddressTypeModel.COMPANY"
					@change.native="setAddressType()">
				{{ $t( 'donation_form_addresstype_option_company' ) }}
			</b-radio>
			<b-radio v-if="!disabledAnonymousType" id="anonymous"
					name="addressTypeInternal"
					v-model="type"
					:disabled="this.disabledAddressTypes.includes( AddressTypeModel.ANON )"
					:native-value="AddressTypeModel.ANON"
					@change.native="setAddressType()">
				{{ $t( 'donation_form_addresstype_option_anonymous' ) }}
				<div v-show="isDirectDebit" class="info-message has-margin-top-18">{{ $t( 'donation_form_address_choice_direct_debit_disclaimer' ) }}</div>
			</b-radio>
		</div>
    </fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressTypeModel, AddressTypes } from '@/view_models/AddressTypeModel';

export default Vue.extend( {
	name: 'AddressType',
	data: function () {
		return {
			type: this.$props.initialAddressType ? AddressTypes.get( this.$props.initialAddressType ) : null,
		};
	},
	props: {
		disabledAddressTypes: Array,
		disabledAnonymousType: Boolean,
		initialAddressType: String,
		isDirectDebit: Boolean,
	},
	computed: {
		AddressTypeModel: {
			get: function () {
				return AddressTypeModel;
			},
		},
	},
	watch: {
		disabledAddressTypes:
		{
			handler: function ( disabledAddressTypes ) {
				const $this = ( this as any );
				if ( disabledAddressTypes.includes( $this.$data.type ) ) {
					$this.$data.type = AddressTypeModel.PERSON;
					$this.setAddressType();
				}
			},
			deep: true,
		},
	},
	methods: {
		setAddressType: function () {
			this.$emit( 'address-type', this.$data.type );
		},
	},
} );
</script>
