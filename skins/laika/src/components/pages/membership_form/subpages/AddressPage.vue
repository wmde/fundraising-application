<template>
	<div class="address-page">
		<h1 class="title is-size-1">{{ $t('membership_form_headline' ) }}</h1>
		<membership-type v-if="showMembershipTypeOption"></membership-type>
		<address-fields v-bind="$props" ref="address"></address-fields>
		<div class="level has-margin-top-18">
			<div class="level-left">
				<b-button id="next" :class="[ 'is-form-input-width', $store.getters.isValidating ? 'is-loading' : '', 'level-item']"
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
		showMembershipTypeOption: Boolean,
	},
	methods: {
		next() {
			( this.$refs.address as any ).validateForm().then( () => {
				if ( this.$store.getters[ NS_MEMBERSHIP_ADDRESS + '/requiredFieldsAreValid' ]
					&& this.$store.getters[ NS_MEMBERSHIP_ADDRESS + '/membershipTypeIsValid' ] ) {
					this.$emit( 'next-page' );
				} else {
					document.getElementsByClassName( 'is-danger' )[ 0 ].scrollIntoView( { behavior: 'smooth', block: 'center', inline: 'nearest' } );
				}
			} );
		},
	},
} );
</script>
