<template>
	<div class="organization-section">
		<h3 class="title is-3 has-margin-top-36 has-margin-bottom-18">
			{{ title }}<br>
			{{ overallAmount.replace(/ /g, '.') }} {{ currencySymbol }}
		</h3>
		<p class="has-margin-left-18">{{ description }}</p>
		<fund-section v-for="(fund, index) in funds"
					:title="fund.title"
					:amount="fund.amount"
					:description="fund.description"
					:currencySymbol="fund.currencySymbol"
					v-on:fund-opened="setOpenFundId( $event )"
					:fund-id="index.toString()"
					:visible-fund-id="openFundId"
					:key="index"
					:width="calculateProgressBarWidth( fund.amount )">
		</fund-section>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import FundSection from '@/components/pages/use_of_funds/FundSection.vue';

export default Vue.extend( {
	name: 'OrganizationSection',
	components: {
		FundSection,
	},
	props: {
		title: String,
		description: String,
		overallAmount: String,
		currencySymbol: String,
		funds: {},
	},
	data: function () {
		return {
			openFundId: '',
		};
	},
	methods: {
		setOpenFundId: function ( id: string ): void {
			this.$data.openFundId = id;
		},
		calculateProgressBarWidth: function ( amount: string ): string {
			let castedOverAllAmount = Number( this.$props.overallAmount.replace( / /g, '' ) );
			let castedAmountNumber = Number( amount.replace( / /g, '' ) );
			let barWidthPercentage = castedAmountNumber / castedOverAllAmount * 100;
			return barWidthPercentage.toString() + '%';
		},
	},
} );
</script>
