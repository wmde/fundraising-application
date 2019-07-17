<template>
    <div class="column is-full">
        <membership-type v-if="showMembershipTypeOption"></membership-type>
		<address-fields v-bind="$props" ref="address"></address-fields>
		<div class="level has-margin-top-36">
			<div class="level-left">
				<b-button id="next" class="level-item"
						@click="next()"
						type="is-primary is-main">
					{{ $t('donation_form_section_continue') }}
				</b-button>
			</div>
		</div>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import MembershipType from '@/components/pages/membership_form//MembershipType.vue';
import AddressFields from '@/components/pages/membership_form/Address.vue';
import { TrackingData } from '@/view_models/SubmitValues';
import { NS_MEMBERSHIP_ADDRESS } from '@/store/namespaces';

export default Vue.extend( {
	name: 'AddressPage',
	components: {
		MembershipType,
		AddressFields,
	},
	props: {
		validateAddressUrl: String,
		countries: Array as () => Array<String>,
		trackingData: Object as () => TrackingData,
		showMembershipTypeOption: Boolean,
		initialFormValues: [ Object, String ],
	},
	methods: {
		next() {
			( this.$refs.address as any ).validateForm().then( () => {
				if ( this.$store.getters[ NS_MEMBERSHIP_ADDRESS + '/requiredFieldsAreValid' ] ) {
					this.$emit( 'next-page' );
				}
			} );
		},
	},
} );
</script>
