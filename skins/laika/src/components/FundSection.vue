<template>
	<div class="fund-section" @click="toggle()">
		<div :class="[ 'accordion-heading', 'icon-inline' ]">
			<div class="fund-text-inline">
				<span>{{ title }}</span>
				<div class="money-progress" :style="{ width: width }">{{ amount.replace(/ /g, '.') }} €</div>
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
		width: String,
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
	&-heading {
		padding: 18px;
		padding-bottom: 0px;
		border-bottom: 2px solid $fun-color-gray-light-transparency;
		cursor: pointer;
	}
	&-content {
		padding: 36px;
		border: 1px solid $fun-color-gray-light-transparency;
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
	width: 100%;
}
.money-progress {
	border: 1px solid $fun-color-primary-lighter;
	background: $fun-color-primary-lighter;
	white-space: nowrap;
	color: $fun-color-dark;
	padding: 9px;
}
.has-text-primary {
	padding: 18px;
	padding-bottom: 0;
}
</style>
