<template>
    <fieldset class="form-input form-input__vertical-option-list">

        <legend class="subtitle">{{ $t( 'donation_form_provisional_address_choice_title' ) }}</legend>
        <div class="radio-container">
            <b-radio
                    id="fulladdress"
                    v-model="fullAddressTypeModel"
                    :native-value="'full'"
                    name="addressTypeProvisional"
                    @change.native="togglePersonTypeSelection( true )">
                {{ $t( 'donation_form_provisional_address_choice_fulladdress' ) }}
                {{ $t( 'donation_form_provisional_address_choice_fulladdress_notice' ) }}
            </b-radio>
            <b-radio
                    id="emailonly"
                    name="addressTypeProvisional"
                    v-model="type"
                    :native-value="AddressTypeModel.EMAIL"
                    @change.native="setAddressType()"
                    :disabled="this.disabledAddressTypes.includes( AddressTypeModel.EMAIL )">
                {{ $t( 'donation_form_provisional_address_choice_emailonly' ) }}
                {{ $t( 'donation_form_provisional_address_choice_emailonly_notice' ) }}
                <div v-show="isDirectDebit" class="info-message has-margin-top-18">({{ $t( 'donation_form_address_choice_direct_debit_disclaimer' ) }})</div>
            </b-radio>
            <b-radio
                    id="noaddress"
                    name="addressTypeProvisional"
                    v-model="type"
                    :native-value="AddressTypeModel.ANON"
                    @change.native="setAddressType()"
                    :disabled="this.disabledAddressTypes.includes( AddressTypeModel.ANON )">
                {{ $t( 'donation_form_provisional_address_choice_noaddress' ) }}
                <div v-show="isDirectDebit" class="info-message has-margin-top-18">({{ $t( 'donation_form_address_choice_direct_debit_disclaimer' ) }})</div>
            </b-radio>
        </div>

        <legend class="subtitle" v-show="isFullAddressSelected">{{ $t( 'donation_form_section_address_header_type' ) }}</legend>
        <div class="radio-container">
            <b-radio
                    v-show="isFullAddressSelected"
                    id="personal"
                    name="addressTypeInternal"
                    v-model="type"
                    :native-value="AddressTypeModel.PERSON"
                    @change.native="setAddressType()">{{ $t( 'donation_form_addresstype_option_private' ) }}
            </b-radio>
            <b-radio
                    v-show="isFullAddressSelected"
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
	name: 'ProvisionalAddressType',
	data: function () {
		return {
			type: this.$props.initialAddressType ? AddressTypes.get( this.$props.initialAddressType ) : null,
			isFullAddressSelected: false,
			fullAddressTypeModel: 'other',
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
			const showInternalSelection = ( this.$data.type === AddressTypeModel.PERSON || this.$data.type === AddressTypeModel.COMPANY );
			this.togglePersonTypeSelection( showInternalSelection );
		},
		togglePersonTypeSelection: function ( changeType: boolean ) {
			this.$data.fullAddressTypemodel = changeType ? 'full' : 'other';
			this.$data.isFullAddressSelected = changeType;
		},
	},
} );
</script>
