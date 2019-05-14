<template>
	<div class="fund-section">
		<h3 class="title is-size-3">{{ title }}</h3>
		<div class="inline border-bottom clickable" @click="isOpen = !isOpen">
			<div class="money-progress" :style="{ width: width }">{{ amount.replace(/ /g, '.') }} €</div>
			<i v-bind:class="[ isOpen ? 'icon-keyboard_arrow_up' : 'icon-keyboard_arrow_down' ]"></i>
		</div>
		<div class="is-bordered" v-show="isOpen">{{ description }}</div>
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
		width: String,
	},
	data: function () {
		return {
			isOpen: false,
		};
	},
} );
</script>
