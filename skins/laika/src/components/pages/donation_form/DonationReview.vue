<template>
	<div class="has-padding-18">
		<div class="donation-review">
			<span>{{ this.$store.state.payment.values.amount }}</span><br>
			<span>{{ this.$store.state.payment.values.interval }}</span><br>
			<span>{{ this.$store.state.payment.values.type }}</span><br>
			<span>{{ this.$store.state.address.addressType }}</span><br>
			<span>{{ this.$store.state.address.values.salutation }}</span><br>
			<span>{{ this.$store.state.address.values.email }} </span><br>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'DonationReview',
} );
</script>
