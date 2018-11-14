<template>
	<div class="org-section">
		<p class="org-title">
			{{ title }}<br>
			{{ overallAmount.replace(/ /g, '.') }} €
		</p>
		<p class="subtitle">{{ description }}</p>
		<fund-section v-for="fund in funds"
					  :title="fund.title"
					  :amount="fund.amount"
					  :description="fund.description"
					  :width="calculateProgressBarWidth( fund.amount )">
		</fund-section>
	</div>
</template>

<script>
import FundSection from './FundSection.vue';

export default {
	name: 'org-section',
	components: {
		FundSection
	},
	props: [ 'title', 'overallAmount', 'description', 'funds' ],
	methods: {
		calculateProgressBarWidth: function ( amount ) {
			let castedOverAllAmount = Number( this.overallAmount.replace( / /g, '' ) );
			let castedAmountNumber = Number( amount.replace( / /g, '' ) );
			let barWidthPercentage = castedAmountNumber / castedOverAllAmount * 100;
			return barWidthPercentage.toString() + '%';
		}
	}
};
</script>