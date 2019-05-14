<template>
	<div class="fund-section">
		<h3>{{ title }}</h3>
		<div class="inline border-bottom clickable" @click="isOpen = !isOpen">
			<div class="money-progress" :style="{ width: width }">{{ amount.replace(/ /g, '.') }} €</div>
			<i v-bind:class="[ isOpen ? 'icon-keyboard_arrow_up' : 'icon-keyboard_arrow_down' ]"></i>
		</div>
		<div v-show="isOpen">{{ description }}</div>
	</div>
</template>

<script>
import Vue from 'vue';

export default Vue.extend( {
	name: 'FundSection',
	props: [ 'title', 'amount', 'description', 'width' ],
	data() {
		return {
			isOpen: false,
		};
	},
} );
</script>
