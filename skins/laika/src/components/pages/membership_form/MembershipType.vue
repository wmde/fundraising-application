<template>
    <fieldset class="has-margin-top-36">
        <legend class="title is-size-5">{{ $t('membership_type_legend') }}</legend>
        <div>
            <b-radio :class="{ 'is-active': selectedType === 'sustaining' }"
                    type="radio"
                    id="sustaining"
                    name="type"
                    v-model="selectedType"
                    native-value="sustaining"
                    @change.native="setType">
                {{ $t( 'membership_membershiptype_option_sustaining' ) }}
            </b-radio>
            <b-radio :class="{ 'is-active': selectedType === 'active' }"
                    type="radio"
                    id="active"
                    name="type"
                    v-model="selectedType"
                    native-value="active"
                    @change.native="setType">
                {{ $t( 'membership_membershiptype_option_active' ) }}
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
    }
</style>