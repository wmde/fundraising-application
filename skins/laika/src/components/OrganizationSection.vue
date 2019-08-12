<template>
	<div class="organization-section">
		<h2 class="title has-margin-top-36 has-margin-bottom-18">
			{{ title }}<br>
			{{ overallAmount.replace(/ /g, '.') }} €
		</h2>
		<p>{{ description }}</p>
		<fund-section v-for="(fund, index) in funds"
					:title="fund.title"
					:amount="fund.amount"
					:description="fund.description"
					v-on:fund-opened="setOpenFundId( $event )"
					:fund-id="index.toString()"
					:visible-fund-id="openFundId"
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
	},
} );
</script>
