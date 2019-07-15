<template>
    <fieldset class="has-margin-top-36">
        <legend class="title is-size-5">{{ $t('membership_membershiptype_legend') }}</legend>
        <div>
            <b-radio :class="{ 'is-active': selectedType === MembershipTypeModel.SUSTAINING }"
                    type="radio"
                    id="sustaining"
                    name="type"
                    v-model="selectedType"
                    :native-value="MembershipTypeModel.SUSTAINING"
                    @change.native="setType">
                <span>{{ $t( 'membership_membershiptype_option_sustaining' ) }}</span>
                <p class="has-text-dark-lighter">{{ $t( 'membership_membershiptype_option_sustaining_legend' ) }}</p>
            </b-radio>
            <b-radio :class="{ 'is-active': selectedType === MembershipTypeModel.ACTIVE }"
                    type="radio"
                    id="active"
                    name="type"
                    v-model="selectedType"
                    :native-value="MembershipTypeModel.ACTIVE"
                    :disabled="isActiveTypeDisabled"
                    @change.native="setType">
                {{ $t( 'membership_membershiptype_option_active' ) }}
                <p class="has-text-dark-lighter">{{ $t( 'membership_membershiptype_option_active_legend' ) }}</p>
            </b-radio>
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
				return this.$store.getters[ NS_MEMBERSHIP_ADDRESS + '/addressType' ] === AddressTypeModel.COMPANY;
			},
			set: function (): void {
				if ( this.$store.getters[ NS_MEMBERSHIP_ADDRESS + '/addressType' ] === AddressTypeModel.COMPANY ) {
					this.$data.selectedType = MembershipTypeModel.SUSTAINING;
					this.setType();
				}
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
<style lang="scss" scoped>
    .b-radio.radio {
        margin-left: 0;
        height: 6.5em;
    }
</style>
