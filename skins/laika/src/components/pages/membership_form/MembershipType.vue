<template>
	<fieldset class="has-margin-top-36 column is-full">
		<legend class="title is-size-5">{{ $t('membership_form_membershiptype_legend') }}</legend>
		<div class="membership-type">
			<b-radio :class="{ 'is-active': selectedType === MembershipTypeModel.SUSTAINING }"
					id="sustaining"
					name="type"
					v-model="selectedType"
					:native-value="MembershipTypeModel.SUSTAINING"
					@change.native="setType">
				{{ $t( 'membership_form_membershiptype_option_sustaining' ) }}
				<p class="has-text-dark-lighter">{{ $t( 'membership_form_membershiptype_option_sustaining_legend' ) }}</p>
			</b-radio>
			<b-radio :class="{ 'is-active': selectedType === MembershipTypeModel.ACTIVE && !isActiveTypeDisabled }"
					id="active"
					name="type"
					:type="isActiveTypeDisabled ? 'is-gray-dark' : ''"
					v-model="selectedType"
					:native-value="MembershipTypeModel.ACTIVE"
					:disabled="isActiveTypeDisabled"
					@change.native="setType">
				{{ $t( 'membership_form_membershiptype_option_active' ) }}
				<p class="has-text-dark-lighter">{{ $t( 'membership_form_membershiptype_option_active_legend' ) }}</p>
			</b-radio>
			<span v-if="activeTypeSelectedAndDisabled" class="help is-danger">{{ $t( 'membership_form_membershiptype_error' ) }}</span>
		</div>
	</fieldset>
</template>

<script lang="ts">
import Vue from 'vue';
import { MembershipTypeModel } from '@/view_models/MembershipTypeModel';
import { AddressTypeModel } from '@/view_models/AddressTypeModel';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';
import { setMembershipType } from '@/store/membership_address/actionTypes';
import { action } from '@/store/util';
import { mapGetters } from 'vuex';

export default Vue.extend( {
	name: 'MembershipType',
	data: function () {
		return {
			selectedType: MembershipTypeModel.SUSTAINING,
		};
	},
	computed: {
		activeTypeSelectedAndDisabled: {
			get: function () {
				return ( this as any ).selectedType === MembershipTypeModel.ACTIVE && ( this as any ).isActiveTypeDisabled;
			},
		},
		MembershipTypeModel: {
			get: function () {
				return MembershipTypeModel;
			},
		},
		AddressTypeModel: {
			get: function () {
				return AddressTypeModel;
			},
		},
		...mapGetters( NS_MEMBERSHIP_ADDRESS, [ 'addressType' ] ),
		isActiveTypeDisabled: {
			get: function (): boolean {
				return ( this as any ).addressType === AddressTypeModel.COMPANY;
			},
		},
	},
	methods: {
		setType(): void {
			this.$store.dispatch( action( NS_MEMBERSHIP_ADDRESS, setMembershipType ), this.$data.selectedType );
		},
	},
} );
</script>
<style lang="scss">
@import "../../../scss/custom.scss";
	.membership-type {
		.b-radio.radio {
			height: 6.5em;
			& + .radio {
				margin-left: 0;
			}
		}
	}
</style>
