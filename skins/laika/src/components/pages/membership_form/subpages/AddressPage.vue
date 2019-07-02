<template>
    <div class="column is-full">
        <membership-type></membership-type>
		<address-fields v-bind="$props" ref="address"></address-fields>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import MembershipType from '@/components/pages/membership_form//MembershipType.vue';
import AddressFields from '@/components/pages/membership_form/Address.vue';
import { TrackingData } from '@/view_models/SubmitValues';

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
	},
} );
</script>
