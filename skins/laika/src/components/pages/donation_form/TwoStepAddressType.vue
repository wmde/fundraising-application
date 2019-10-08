<template>
	<div>
		<fieldset>
			<legend class="subtitle">{{ $t( 'donation_form_section_address_or_anon' ) }}</legend>
			<div class="radio-container">
				<b-radio id="withAddress"
						name="addressChoice"
						v-model="isAnon"
						native-value="no"
						@change.native="setAddressChoice()">{{ $t( 'donation_form_address_choice_yes' ) }}
				</b-radio>
				<b-radio id="anonymous"
						name="addressChoice"
						v-model="isAnon"
						native-value="yes"
						:disabled="this.disabledAddressTypes.includes( AddressTypeModel.ANON )"
						@change.native="setAddressChoice()">
					{{ $t( 'donation_form_address_choice_no' ) }}
				</b-radio>
			</div>
		</fieldset>
		<div v-show="isAnon === 'yes'" class="has-margin-top-18">
			{{ $t( 'donation_addresstype_option_anonymous_disclaimer' ) }}
		</div>
		<div
				v-show="isAnon === 'no' && disabledAddressTypes.includes( AddressTypeModel.ANON )"
				class="has-margin-top-18">
			{{ $t( 'donation_form_address_choice_direct_debit_disclaimer' ) }}
		</div>

		<fieldset v-show="isAnon === 'no'" class="has-margin-top-36">
			<legend class="subtitle">{{ $t( 'donation_form_section_address_header_type' ) }}</legend>
			<div  class="radio-container">
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
			</div>
		</fieldset>
		<h2 v-show="isAnon === 'no'" class="title is-size-5 has-margin-top-36 has-negative-margin-bottom-18">{{ $t( 'donation_form_section_address_header' ) }}</h2>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export default Vue.extend( {
	name: 'TwoStepAddressType',
	data: function () {
		return {
			isAnon: 'no',
			type: AddressTypeModel.PERSON,
		};
	},
	props: {
		disabledAddressTypes: Array,
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
		setAddressChoice: function () {
			if ( this.$data.isAnon === 'yes' ) {
				this.$emit( 'address-type', AddressTypeModel.ANON );
				return;
			}
			this.$emit( 'address-type', this.$data.type );
		},
	},
} );
</script>
