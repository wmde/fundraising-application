<template>
		<fieldset class="form-input form-input__vertical-option-list">

				<legend class="subtitle">{{ $t( 'donation_form_provisional_address_choice_title' ) }}</legend>
				<div class="radio-container">
						<b-radio
										v-model="addressType"
										native-value="full"
										name="addressTypeProvisional"
										>
								{{ $t( 'donation_form_provisional_address_choice_fulladdress' ) }}
								{{ $t( 'donation_form_provisional_address_choice_fulladdress_notice' ) }}
						</b-radio>
						<b-radio
										name="addressTypeProvisional"
										v-model="addressType"
										native-value="email"
										:disabled="this.disabledAddressTypes.includes( AddressTypeModel.EMAIL )">
								{{ $t( 'donation_form_provisional_address_choice_emailonly' ) }}
								{{ $t( 'donation_form_provisional_address_choice_emailonly_notice' ) }}
								<div v-show="isDirectDebit" class="info-message has-margin-top-18">
									({{ $t( 'donation_form_address_choice_direct_debit_disclaimer' ) }})
								</div>
						</b-radio>
						<b-radio
										name="addressTypeProvisional"
										v-model="addressType"
										native-value="anonymous"
										:disabled="this.disabledAddressTypes.includes( AddressTypeModel.ANON )">
								{{ $t( 'donation_form_provisional_address_choice_noaddress' ) }}
								<div v-show="isDirectDebit" class="info-message has-margin-top-18">
									({{ $t( 'donation_form_address_choice_direct_debit_disclaimer' ) }})
								</div>
						</b-radio>
				</div>

				<legend class="subtitle" v-show="isFullAddressSelected">{{ $t( 'donation_form_section_address_header_type' ) }}</legend>
				<div class="radio-container" v-show="isFullAddressSelected">
						<b-radio
										name="addressTypeInternal"
										v-model="fullAddressType"
										native-value="person"
										>{{ $t( 'donation_form_addresstype_option_private' ) }}
						</b-radio>
						<b-radio
										v-show="isFullAddressSelected"
										name="addressTypeInternal"
										v-model="fullAddressType"
										native-value="company"
										>
								{{ $t( 'donation_form_addresstype_option_company' ) }}
						</b-radio>
				</div>

		</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressTypes, AddressTypeModel } from '@/view_models/AddressTypeModel';

const fullAddressTypeToModel = new Map( [
	[ 'person', AddressTypeModel.PERSON ],
	[ 'company', AddressTypeModel.COMPANY ],
] );

export default Vue.extend( {
	name: 'ProvisionalAddressType',
	data: function () {
		return {
			type: this.$props.initialAddressType ? AddressTypes.get( this.$props.initialAddressType ) : null,
			fullAddressType: '',
			addressType: this.$props.initialAddressType ? this.$props.initialAddressType : '',
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
		isFullAddressSelected: {
			get: function () {
				const $this = ( this as any );
				return $this.$data.addressType === 'full';
			},
		},
	},
	watch: {
		disabledAddressTypes:
				{
					handler: function ( disabledAddressTypes ) {
						const $this = ( this as any );
						if ( disabledAddressTypes.includes( $this.$data.type ) ) {
							$this.$data.addressType = 'full';
							$this.$data.fullAddressType = AddressTypeModel.PERSON;
						}
					},
					deep: true,
				},
		addressType: {
			handler: function ( newAddressType ) {
				const $this = ( this as any );
				switch ( newAddressType ) {
					case 'full':
						$this.$data.type = AddressTypeModel.UNSET;
						break;
					case 'email':
						$this.$data.type = AddressTypeModel.EMAIL;
						$this.$data.fullAddressType = '';
						break;
					case 'anonymous':
						$this.$data.type = AddressTypeModel.ANON;
						$this.$data.fullAddressType = '';
				}
				this.$emit( 'address-type', this.$data.type );
			},
		},
		fullAddressType: {
			handler: function ( newFullAddressType ) {
				const $this = ( this as any );
				if ( !fullAddressTypeToModel.has( newFullAddressType ) ) {
					return;
				}
				$this.$data.type = fullAddressTypeToModel.get( newFullAddressType );
				$this.$emit( 'address-type', this.$data.type );
			},
		},
	},
} );
</script>
