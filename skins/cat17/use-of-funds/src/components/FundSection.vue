<template>
	<div class="fund-section">
		<p class="section-title">{{ title }}</p>
		<div class="inline border-bottom clickable" @click="toggle()">
			<div class="money-progress" :style="{ width: width }">{{ amount.replace(/ /g, '.') }} €</div>
			<i v-bind:class="[ isOpen ? 'icon-keyboard_arrow_up' : 'icon-keyboard_arrow_down' ]"></i>
		</div>
		<p class="border description" v-show="isOpen">{{ description }}</p>
	</div>
</template>

<script>
	export default {
		name: 'fund-section',
		props: [ 'title', 'amount', 'description', 'width' ],
		data() {
			return {
				isOpen: false
			};
		},
		methods: {
			toggle: function () {
				this.isOpen = !this.isOpen;
			}
		}
	};
</script>