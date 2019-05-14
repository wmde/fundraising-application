<template>
	<div class="organization-section">
		<h2 class="title is-size-2">
			{{ title }}<br>
			{{ overallAmount.replace(/ /g, '.') }} €
		</h2>
		<div>{{ description }}</div>

		<fund-section v-for="(fund, index) in funds"
					:title="fund.title"
					:amount="fund.amount"
					:description="fund.description"
					:width="calculateProgressBarWidth( fund.amount )"
					:key="index">
		</fund-section>

	</div>
</template>

<script>
import Vue from 'vue';
import FundSection from '@/components/FundSection';

export default Vue.extend( {
	name: 'OrganizationSection',
	components: {
		FundSection,
	},
	props: [ 'title', 'description', 'overallAmount', 'funds' ],                // TODO datentypen!
	methods: {
		calculateProgressBarWidth: function ( amount ) {
			let castedOverAllAmount = Number( this.overallAmount.replace( / /g, '' ) );
			let castedAmountNumber = Number( amount.replace( / /g, '' ) );
			let barWidthPercentage = castedAmountNumber / castedOverAllAmount * 100;
			return barWidthPercentage.toString() + '%';
		},
	},
} );
</script>
