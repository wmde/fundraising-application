<template>
  <table class="company_budgets">
    <tr v-for="company in companies" :class="'company_budgets__row--' + company.name.toLowerCase()" :key="company.name">
      <td class="company_budgets__col--company">{{ company.name }} </td>
      <td class="company_budgets__col--graph">
						<span class="company_budgets__budget_line"
                  :style="{ width: ( company.budget / highestBudget * 100 ) + '%' }">&#xa0;</span>
      </td>
      <td class="company_budgets__col--budget_number has-text-right">
        <span class="company_budgets__number">{{
						company.budget > 1 ? billionFormatter(company.budget) : millionFormatter(company.budget)
					}}</span>
        <span class="company_budgets__inline-citation"><CompanyCitation :company="company" :citation-label="citationLabel" /></span>
      </td>
      <td class="company_budgets__col--citation has-text-right"><CompanyCitation :company="company" :citation-label="citationLabel" /></td>
    </tr>
  </table>
</template>

<script lang="ts">

import { computed, defineComponent, PropType } from '@vue/composition-api';
import formatter from 'format-number';
import CompanyCitation from './CompanyCitation.vue';

interface CompanyInterface {
  name: string;
  budget: number;
  budgetCitation?: string
}

export default defineComponent( {
	name: 'CompanyBudgets',
	components: {
		CompanyCitation,
	},
	props: {
		companies: {
			type: Array as PropType<Array<CompanyInterface>>,
			required: true,
		},
		locale: {
			type: String,
			required: true,
		},
		citationLabel: {
			type: String,
			required: true,
		},
	},
	setup( props ) {
		const highestBudget = computed( () => props.companies.reduce( ( highestBudget: number, company: CompanyInterface ) =>
			Math.max( highestBudget, company.budget ), 0 )
		);
		const billionFormatter = props.locale === 'en' ?
			formatter( { round: 0, prefix: '€', suffix: ' billion' } ) :
			formatter( { round: 0, suffix: ' Mrd. €' } );
		const millionFormatter = props.locale === 'en' ?
			formatter( { round: 2, prefix: '€', suffix: ' billion', padRight: 1 } ) :
			formatter( { round: 2, decimal: ',', suffix: ' Mrd. €', padRight: 1 } );

		return {
			highestBudget,
			millionFormatter,
			billionFormatter,
		};
	},
} );
</script>

<style scoped>

</style>
