<template>
	<fieldset>
		<legend class="title is-size-5">{{ $t( 'membership_form_section_address_header_type' ) }}</legend>
		<div>
			<b-radio type="radio"
					id="personal"
					name="addressTypeInternal"
					v-model="type"
					:native-value="AddressTypeModel.PERSON"
					@change.native="setAddressType()">{{ $t( 'donation_form_addresstype_option_private' ) }}
			</b-radio>
		</div>
		<div>
			<b-radio type="radio"
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
import { AddressTypeModel } from '@/view_models/AddressTypeModel';

export default Vue.extend( {
	name: 'AddressType',
	data: function () {
		return {
			type: this.$props.initialValue !== '' ? this.$props.initialValue : AddressTypeModel.PERSON,
		};
	},
	props: {
		initialValue: [ Number as () => AddressTypeModel, String ],
	},
	computed: {
		AddressTypeModel: {
			get: function () {
				return AddressTypeModel;
			},
		},
	},
	methods: {
		setAddressType: function () {
			this.$emit( 'address-type', this.$data.type );
		},
	},
} );
</script>
