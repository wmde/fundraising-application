<template>
    <div :class="[ 'field-grp', 'field-' + name, invalid ? 'invalid' : '' ]">
        <slot></slot>
        <label :for="name" class="sr-only">{{ label }}</label>
        <span class="error-text">{{ errorMessage }}</span>
    </div>
</template>

<script>
	export default {
		name: "field-wrapper",
        props: {
            name: String,
            label: String,
            'error-message': String,
            invalid: { type: Boolean, default: false }
        }
	}
</script>

<style scoped>
    .error-text {
        display: none;
    }

    .invalid .error-text {
        display: block;
    }

</style>