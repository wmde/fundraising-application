<template>
	<div class="organization-section">
		<h3 class="title is-3 has-margin-top-36 has-margin-bottom-18 has-margin-left-18">
			{{ title }}<br>
			{{ overallAmount.replace(/ /g, '.') }} €
		</h3>
		<p class="has-margin-left-18">{{ description }}</p>
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
