<template>
	<div class="organization-section">
		<h2 class="title has-margin-top-18 as-margin-bottom-18">
			{{ title }}<br>
			{{ overallAmount.replace(/ /g, '.') }} €
		</h2>
		<p>{{ description }}</p>
		<fund-section v-for="(fund, index) in funds"
					:title="fund.title"
					:amount="fund.amount"
					:description="fund.description"
					:width="calculateProgressBarWidth( fund.amount )"
					:key="index">
		</fund-section>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import FundSection from '@/components/FundSection.vue';

export default Vue.extend( {
	name: 'OrganizationSection',
	components: {
		FundSection,
	},
	props: {
		title: String,
		description: String,
		overallAmount: String,
		funds: {}
	},
	methods: {
		calculateProgressBarWidth: function ( amount: string ) : string {
			let castedOverAllAmount: number = Number( this.overallAmount.replace( / /g, '' ) );
			let castedAmountNumber: number = Number( amount.replace( / /g, '' ) );
			let barWidthPercentage: number = castedAmountNumber / castedOverAllAmount * 100;
			return barWidthPercentage.toString() + '%';
		},
	},
} );
</script>
