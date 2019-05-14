<template>
	<div class="column is-full" id="use-of-funds">
		<div>
			<h1>{{ $t('use_of_funds_header') }}</h1>
			<p>{{ $t('use_of_funds_description') }}</p>
		</div>

		<organization-section v-for="(org, index) in content.organizations"
							:title="org.title"
							:overallAmount="org.overallAmount"
							:description="org.description"
							:funds="org.funds"
							:key="index">
		</organization-section>

		<!--TODO side bar content ( 2 links, a logo ... ) are still missing, depends on final design -->
	</div>
</template>

<script>
import Vue from 'vue';
import OrganizationSection from '@/components/OrganizationSection';

export default Vue.extend( {
	name: 'use-of-funds',
	components: {
		OrganizationSection,
	},
	props: [ 'content' ],
	data() {
		return {
			title: '',
			overallAmount: '',
			funds: {},
		};
	},
} );
</script>
