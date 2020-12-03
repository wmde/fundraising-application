<template>
	<div class="use_of_funds">
		<div class="use_of_funds__section">
			<div class="use_of_funds__section_intro">
				<h1 class="title is-1 has-margin-bottom-18">{{ content.intro.headline }}</h1>
				<div>{{ content.intro.text }}</div>
			</div>
		</div>

		<FundsDistributionAccordion :application-of-funds-data="content.applicationOfFundsData" />
		<FundsDistributionInfo :application-of-funds-data="content.applicationOfFundsData" />

		<div class="use_of_funds__section use_of_funds__section--two-cols-info">
			<div class="use_of_funds__column--info" style="display:none">
				<span>{{ content.detailedReports.international.intro }}</span>
				<a :href="content.detailedReports.international.linkUrl" target="_blank">
					{{ content.detailedReports.international.linkName }}
				</a>
			</div>
			<div class="use_of_funds__column--info">
				<span>{{ content.detailedReports.germany.intro }}</span>
				<a :href="content.detailedReports.germany.linkUrl" target="_blank">
					{{ content.detailedReports.germany.linkName }}
				</a>
			</div>
		</div>
		<div class="use_of_funds__section use_of_funds__section--two-cols">
			<div class="use_of_funds__column">
				<div class="use_of_funds__benefits_list">
					<h2>{{ content.benefitsList.headline }}</h2>
					<ul class="use_of_funds__icon-list">
						<li v-for="benefit in content.benefitsList.benefits"
								:class="'use_of_funds__icon-list_item--' + benefit.icon"
								:key=benefit.text>
							{{ benefit.text }}
						</li>
					</ul>
				</div>
			</div>
			<div class="use_of_funds__column">
				<div class="use_of_funds__comparison">
					<h2>{{ content.comparison.headline }}</h2>
					<div>
						<p v-for="text in content.comparison.paragraphs" :key="text">{{ text }}</p>
						<h3>{{ content.comparison.subhead }}</h3>
					</div>
					<CompanyBudgets
							:companies="content.comparison.companies"
							:citation-label="content.comparison.citationLabel"
							:locale="locale" />
				</div>
			</div>
		</div>
		<div class="use_of_funds__section use_of_funds__section--orgchart">
			<div class="use_of_funds__orgchart_text">
				<h2>{{ content.orgchart.headline }}</h2>
				<div>
					<p><span v-for="part in highlightedOrganization" :key="part.text" :class="part.className">{{ ' ' }}{{ part.text }}{{ ' ' }}</span> </p>
					<p v-for="para in content.orgchart.paragraphs.slice( 1 )" :key="para">{{ para }}</p>
				</div>
			</div>
			<div class="use_of_funds__orgchart_image">
				<img :src="assetsPath + '/images/WMDE-funds-forwarding.gif'" />
			</div>
		</div>
		<div class="banner_model__section use_of_funds__section--call_to_action">
			<button class="use_of_funds__button" onclick="location.href='/'">{{ content.callToAction }}</button>
		</div>
		<div style="text-align: left; font-size: small; padding-bottom: 16px;">{{ content.provisional }}</div>
	</div>
</template>

<script lang="ts">

import CompanyBudgets from '@/components/pages/use_of_funds/CompanyBudgets.vue';
import FundsDistributionAccordion from '@/components/pages/use_of_funds/FundsDistributionAccordion.vue';
import FundsDistributionInfo from '@/components/pages/use_of_funds/FundsDistributionInfo.vue';
import { defineComponent, computed } from '@vue/composition-api';

function splitStringAt( splitWords: string[], str: string ) {
	const rx = new RegExp( '(' + splitWords.join( '|' ) + ')', 'g' );
	return str.split( rx ).filter( w => w !== '' );
}

export default defineComponent( {
	name: 'use-of-funds',
	components: {
		CompanyBudgets,
		FundsDistributionAccordion,
		FundsDistributionInfo,
	},
	props: {
		content: {
			type: Object,
			required: true,
		},
		locale: {
			type: String,
			required: true,
		},
		assetsPath: {
			type: String,
			required: true,
		},
	},
	setup( props ) {
		const highlightedOrganization = computed( () => {
			const organizationClassLookup = new Map<string, string>( Object.entries( props.content.orgchart.organizationClasses ) );
			const getHighlightClassName = ( part: string ) => organizationClassLookup.has( part ) ?
				`use_of_funds__org use_of_funds__org--${organizationClassLookup.get( part )}` : '';

			return splitStringAt( Array.from( organizationClassLookup.keys() ), props.content.orgchart.paragraphs[ 0 ] ).map( part => {
				return { text: part, className: getHighlightClassName( part ) };
			} );
		} );
		return {
			highlightedOrganization,
		};
	},
} );
</script>

<style lang="scss">
@import '../../scss/use_of_funds/FundsContent';
@import '../../scss/use_of_funds/CompanyBudgets';
@import'../../scss/use_of_funds/FundsDistributionInfo';
@import'../../scss/use_of_funds/FundsDistributionAccordion';
</style>
