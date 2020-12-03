<template>
	<div class="funds_distribution_accordion">
		<div v-for="fundsItem in applicationOfFundsData"
			:key="fundsItem.id"
			:class="[
					'funds_distribution_info_item',
					'funds_distribution_info_item--' + fundsItem.id,
					activeInfo[fundsItem.id] ? 'active' : ''
					]"
		>
			<div class="funds_distribution_info_item__title" @click="setActive( fundsItem.id )">
				{{ fundsItem.title }} {{ fundsItem.percentage }}%
			</div>
			<div class="funds_distribution_info_item__text">
				{{ fundsItem.text }}
			</div>
		</div>
	</div>
</template>

<script lang="ts">
import { defineComponent, reactive } from '@vue/composition-api';
import { FundsItem } from '@/view_models/useOfFunds';

interface ActiveInfo {
	[index: string]: boolean
}

export default defineComponent( {
	name: 'FundsDistributionAccordion',
	props: {
		applicationOfFundsData: {
			type: Array as () => Array<FundsItem>,
			required: true,
		},
	},
	setup( props ) {
		const fundsData: FundsItem[] = props.applicationOfFundsData;
		const itemKeyState: ActiveInfo = fundsData.reduce<ActiveInfo>( ( activeInfo: ActiveInfo, fundsItem: FundsItem ): ActiveInfo => {
			const itemId = fundsItem.id;
			return { ...activeInfo, [ itemId ]: false };
		}, {} as ActiveInfo );
		const activeInfo = reactive<ActiveInfo>( itemKeyState );
		const setActive = ( id: string ) => {
			activeInfo[ id ] = !activeInfo[ id ];
		};

		return {
			activeInfo,
			setActive,
		};
	},
} );
</script>
