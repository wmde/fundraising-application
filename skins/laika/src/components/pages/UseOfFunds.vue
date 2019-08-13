<template>
	<div class="columns is-full has-padding-18 has-background-bright" id="use-of-funds">
		<div class="column is-two-thirds">
			<h1 class="title is-1 has-margin-bottom-18">{{ $t('use_of_funds_header') }}</h1>
			<p>{{ $t('use_of_funds_description') }}</p>
			<organization-section v-for="(org, index) in content.organizations"
								:title="org.title"
								:overallAmount="org.overallAmount"
								:description="org.description"
								:funds="org.funds"
								:key="index">
			</organization-section>
		</div>
		<div class="column is-one-third">
			<ul class="list-menu list-unstyled">
				<li>
					<a :href="content.organizations.wmde.url">{{ $t('year_plan_wmde') }} {{ content.organizations.wmde.title }}</a>
				</li>
				<li>
					<a :href="content.organizations.wmf.url">{{ $t('year_plan_wmf')  }} {{ content.organizations.wmf.title }}</a>
				</li>
			</ul>
		</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';
import OrganizationSection from '@/components/OrganizationSection.vue';
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
