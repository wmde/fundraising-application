<template>
	<div class="funds_distribution_info">

		<div class="funds_distribution_info__graph">
			<div v-for="fundsItem in applicationOfFundsData"
				:class="[
					'funds_distribution_info_item',
					`funds_distribution_info_item--${fundsItem.id}`,
					fundsItem.id === activeItem ? 'active' : ''
				]"
				:key="fundsItem.id"
				@mouseenter="setActive(fundsItem.id)"
				@click="setActive(fundsItem.id)"
				:style="{
					width: fundsItem.percentage + '%',
					flexBase: fundsItem.percentage + '%'
				}">
			<div class="funds_distribution_info_item__title">{{ fundsItem.title }}</div>
			<div class="funds_distribution_info_item__box">{{ fundsItem.percentage }}%</div>
		</div>
	</div>

		<div v-for="fundsItem in applicationOfFundsData"
			:key="fundsItem.id"
			:class="[ 'funds_distribution_info__text', fundsItem.id === activeItem ? 'active' : '' ]"
		>
			{{ fundsItem.text }}
		</div>
	</div>
</template>

<script lang="ts">
import { defineComponent, ref } from '@vue/composition-api';
import { FundsItem } from '@/view_models/useOfFunds';

export default defineComponent( {
	name: 'FundsDistributionInfo',
	props: {
		applicationOfFundsData: {
			type: Array as () => Array<FundsItem>,
			required: true,
		},
	},
	setup( { applicationOfFundsData } ) {
		const activeItem = ref<string>( applicationOfFundsData[ 0 ].id );
		const setActive = ( id: string ) => {
			activeItem.value = id;
		};

		return {
			activeItem,
			setActive,
		};
	},
} );
</script>
