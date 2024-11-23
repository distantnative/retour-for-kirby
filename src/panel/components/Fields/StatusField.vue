<template>
	<k-field
		v-bind="$props"
		:input="id"
		:class="['k-select-field', $attrs.class]"
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
import color from "../../mixins/color.js";

export default {
	extends: "k-select-field",
	mixins: [color],
	mounted() {
        if (!this.value && this.default) {
            this.$emit('input', this.default);
        }
    }
};
</script>
