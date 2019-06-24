<template>
	<div>
		<input type="hidden" name="impCount" :value="trackingData.bannerImpressionCount">
		<input type="hidden" name="bImpCount" :value="trackingData.impressionCount">
		<input type="hidden" name="trackingSource" :value="trackingData.impressionCount">
		<input type="hidden" name="trackingCode" :value="trackingData.impressionCount">
	</div>
</template>

<script lang="ts">
	import Vue from 'vue';

	export default Vue.extend( {
		name: 'SubmitValues',
		props: [
			'trackingData',
		],
	} );
</script>
