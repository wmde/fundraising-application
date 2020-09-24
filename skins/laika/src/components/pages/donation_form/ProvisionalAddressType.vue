<template>
    <fieldset class="form-input form-input__vertical-option-list">

        <legend class="subtitle">{{ $t( 'donation_form_provisional_address_choice_title' ) }}</legend>
        <div class="radio-container">
            <b-radio
                    id="fulladdress"
                    name="addressTypeProvisional"
                    @change.native="togglePersonTypeSelection( 'full' )">
                {{ $t( 'donation_form_provisional_address_choice_fulladdress' ) }}
                {{ $t( 'donation_form_provisional_address_choice_fulladdress_notice' ) }}
            </b-radio>
            <b-radio
                    id="emailonly"
                    name="addressTypeProvisional"
                    v-model="type"
                    :native-value="AddressTypeModel.EMAIL"
                    @change.native="setAddressType()">
                {{ $t( 'donation_form_provisional_address_choice_emailonly' ) }}
                {{ $t( 'donation_form_provisional_address_choice_emailonly_notice' ) }}
            </b-radio>
            <b-radio
                    id="noaddress"
                    name="addressTypeProvisional"
                    v-model="type"
                    :native-value="AddressTypeModel.ANON"
                    @change.native="setAddressType()">
                {{ $t( 'donation_form_provisional_address_choice_noaddress' ) }}
            </b-radio>
        </div>

        <legend class="subtitle" v-if="isFullAddressSelected">{{ $t( 'donation_form_section_address_header_type' ) }}</legend>
        <div class="radio-container">
            <b-radio
                    v-if="isFullAddressSelected"
                    id="personal"
                    name="addressTypeInternal"
                    v-model="type"
                    :native-value="AddressTypeModel.PERSON"
                    @change.native="setAddressType()">{{ $t( 'donation_form_addresstype_option_private' ) }}
            </b-radio>
            <b-radio
                    v-if="isFullAddressSelected"
                    id="company"
                    name="addressTypeInternal"
                    v-model="type"
                    :native-value="AddressTypeModel.COMPANY"
                    @change.native="setAddressType()">
                {{ $t( 'donation_form_addresstype_option_company' ) }}
            </b-radio>
        </div>

    </fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { AddressTypes, AddressTypeModel } from '@/view_models/AddressTypeModel';
export default Vue.extend( {
	name: 'AddressSwitchDonorType',
	data: function () {
		return {
			type: this.$props.initialAddressType ? AddressTypes.get( this.$props.initialAddressType ) : null,
			isFullAddressSelected: false,
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
			this.togglePersonTypeSelection( '' );
		},
		togglePersonTypeSelection: function ( changeType: string ) {
			this.$data.isFullAddressSelected = ( changeType === 'full' );
			// TODO: does not work properly yet, maybe there's a better way
		},
	},
} );
</script>
