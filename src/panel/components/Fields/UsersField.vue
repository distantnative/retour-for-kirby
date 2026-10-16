<template>
	<k-field
		v-bind="$props"
		:input="false"
		:class="['k-retour-users-field', $attrs.class]"
		:style="$attrs.style"
	>
		<k-collection
			:empty="{ icon: 'users', text: empty }"
			:items="value"
			theme="disabled"
		/>
	</k-field>
</template>

<script>
/** Read-only list of users, e.g. the creator of a redirect */
export default {
	inheritAttrs: false,
	props: {
		empty: String,
		help: String,
		label: String,
		name: String,
		type: String,
		value: {
			type: Array,
			default: () => [],
		},
	},
};
</script>
