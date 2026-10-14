<template>
	<k-field
		v-bind="$props"
		:input="id"
		:class="['k-select-field', $attrs.class]"
		:style="$attrs.style"
	>
		<k-input
			v-bind="$props"
			ref="input"
			type="select"
			@input="$emit('input', $event)"
		>
			<template #before>
				<k-icon type="circle-filled" :color="color" />
			</template>
		</k-input>
	</k-field>
</template>

<script>
import { color } from "../../helpers/status";

export default {
	extends: "k-select-field",
	emits: ["input"],
	computed: {
		color() {
			return color(this.value);
		},
	},
};
</script>
