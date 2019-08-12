<template>
	<div class="fund-section" @click="toggle()" :class="[ isOpen ? 'accordion' : '' ]">
		<div :class="[ isOpen ? 'has-text-primary has-text-weight-bold' : 'accordion-heading', 'icon-inline' ]">
			<div class="fund-text-inline">
				<span>{{ title }}</span>
				<div v-if="!isOpen" class="money-progress">{{ amount.replace(/ /g, '.') }} €</div>
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
		description: String,
		visibleFundId: String,
		fundId: String,
	},
	computed: {
		isOpen: {
			get: function (): boolean {
				return this.$props.fundId === this.$props.visibleFundId;
			},
		},
	},
	methods: {
		toggle: function () {
			if ( !this.isOpen ) {
				this.$emit( 'fund-opened', this.$props.fundId );
			} else {
				this.$emit( 'fund-opened' ); // close the current fund section when the arrow up icon is clicked
			}
		},
	},
} );
</script>
<style lang="scss">
@import "../scss/custom";

.accordion {
	padding: 18px;
	box-sizing: content-box;
	border: 1px solid $fun-color-gray-light-transparency;
	&-heading {
		padding: 18px;
		padding-bottom: 0px;
		border-bottom: 2px solid $fun-color-gray-light-transparency;
		cursor: pointer;
	}
	&-content {
		padding: 36px;
	}
}
.icon-inline {
	display: flex;
	justify-content: space-between;
	flex-wrap: nowrap;
	align-items: center;
}
.fund-text-inline {
	display: flex;
	flex-direction: column;
	flex-wrap: nowrap;
	justify-content: flex-start;
	align-items: stretch;
	align-content: stretch;
	cursor: pointer;
}
.money-progress {
	border: 1px solid $fun-color-primary;
	background: $fun-color-primary;
	width: 240px;
	height: 35px;
	white-space: nowrap;
	color: $fun-color-bright;
	padding-left: 18px;
}
.has-text-primary {
	padding: 18px;
	padding-bottom: 0;
}
</style>
