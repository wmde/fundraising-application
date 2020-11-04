<template>
	<div class="columns has-padding-18 has-background-bright use-of-funds">
		<div class="column is-two-thirds">
			<h1 class="title is-1 has-margin-bottom-18">{{ $t('use_of_funds_header') }}*</h1>
			<p>{{ $t('use_of_funds_description') }}</p>
			<organization-section v-for="(org, index) in content.organizations"
								:title="org.title"
								:overallAmount="org.overallAmount"
								:description="org.description"
								:funds="org.funds"
								:currency-symbol="org.currencySymbol"
								:key="index">
			</organization-section>
			<div style="font-size: small; margin-top: 20px;">*) vorläufig</div>
		</div>
		<div class="column is-one-third">
			<ul class="list-menu list-unstyled">
				<li>
					<a class="organization-link" :href="content.organizations.wmde.url">
						<span>{{ $t('year_plan_wmde') }}</span>
						<span>{{ content.organizations.wmde.title }}</span>
					</a>
				</li>
				<li>
					<a class="organization-link" :href="content.organizations.wmf.url">
						<span>{{ $t('year_plan_wmf')  }}</span>
						<span>{{ content.organizations.wmf.title }}</span>
					</a>
				</li>
			</ul>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import OrganizationSection from '@/components/pages/use_of_funds/OrganizationSection.vue';
import { UseOfFundsContent } from '@/view_models/useOfFunds';

export default Vue.extend( {
	name: 'use-of-funds',
	components: {
		OrganizationSection,
	},
	props: {
		content: {
			type: Object as () => UseOfFundsContent,
		},
	},
	data: function () {
		return {
			title: '',
			overallAmount: '',
			funds: {},
		};
	},
} );
</script>

<style lang="scss">
	.organization-link > span {
		display: inline-block;
	}
</style>
