<template>
	<div @click="isOpen = !isOpen" :class="[ isOpen ? 'accordion-open' : '', 'accordion-item', 'accordion-title' ]">
		<div :class="[ isOpen ? 'has-margin-left-18': 'accordion-heading', 'icon-inline' ]">
			<div class="fund-text-inline">
				<span>{{ title }}</span>
				<div class="money-progress" :style="{ width: width }">{{ amount.replace(/ /g, '.') }} {{ currencySymbol }}</div>
			</div>
			<b-icon v-if="isOpen" icon="arrow-up" class="icon-size"></b-icon>
			<b-icon v-else icon="arrow-down" class="icon-size"></b-icon>
		</div>
		<div class="accordion-content" v-show="isOpen">{{ description }}</div>
	</div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'FundSection',
	props: {
		title: String,
		amount: String,
		currencySymbol: String,
		description: String,
		width: String,
	},
	data() {
		return {
			isOpen: false,
		};
	},
} );
</script>
<style lang="scss">
@import "../../../scss/custom";

.fund-text-inline {
	display: flex;
	flex-direction: column;
	flex-wrap: nowrap;
	justify-content: flex-start;
	align-items: stretch;
	align-content: stretch;
	cursor: pointer;
	width: 100%;
}
.money-progress {
	border: 1px solid $fun-color-primary-lighter;
	background: $fun-color-primary-lighter;
	white-space: nowrap;
	color: $fun-color-dark;
	padding: 9px;
}
</style>
