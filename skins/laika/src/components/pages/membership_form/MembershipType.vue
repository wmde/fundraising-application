<template>
    <fieldset class="has-margin-top-36">
        <legend class="title is-size-5">{{ $t('membership_membershiptype_legend') }}</legend>
        <div>
            <b-radio :class="{ 'is-active': selectedType === 'sustaining' }"
                    type="radio"
                    id="sustaining"
                    name="type"
                    v-model="selectedType"
                    native-value="sustaining"
                    @change.native="setType">
                <span>{{ $t( 'membership_membershiptype_option_sustaining' ) }}</span>
                <p class="has-text-dark-lighter">{{ $t( 'membership_membershiptype_option_sustaining_legend' ) }}</p>
            </b-radio>
            <b-radio :class="{ 'is-active': selectedType === 'active' }"
                    type="radio"
                    id="active"
                    name="type"
                    v-model="selectedType"
                    native-value="active"
                    @change.native="setType">
                {{ $t( 'membership_membershiptype_option_active' ) }}
                <p class="has-text-dark-lighter">{{ $t( 'membership_membershiptype_option_active_legend' ) }}</p>
            </b-radio>
        </div>
    </fieldset>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend( {
	name: 'MembershipType',
	data: function () {
		return {
			selectedType: 'sustaining',
		};
	},
	methods: {
		setType(): void {
			// TODO: store the chosen membership type in the membership address store once it exists
		},
	},
} );
</script>
<style lang="scss" scoped>
    .b-radio.radio {
        margin-left: 0;
        height: 6.5em;
    }
</style>
