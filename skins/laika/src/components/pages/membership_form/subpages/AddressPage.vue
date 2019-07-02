<template>
    <div class="column is-full">
        <membership-type></membership-type>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import MembershipType from '@/components/pages/membership_form//MembershipType.vue';

export default Vue.extend( {
    name: 'AddressPage',
	components: {
		MembershipType,
	},
} );
</script>
