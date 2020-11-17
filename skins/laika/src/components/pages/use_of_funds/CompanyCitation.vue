<template functional>
  <a v-if="props.company.budgetCitation"
      class="company_budgets__citation_link"
      :href=props.company.budgetCitation
      target="_blank">
    {{ props.citationLabel }}
  </a>
  <span v-else>&nbsp;</span>
</template>
