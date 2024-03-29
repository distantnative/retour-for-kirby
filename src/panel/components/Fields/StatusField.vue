<template>
	<k-field :input="_uid" v-bind="$props" class="k-select-field">
		<k-input
			:id="_uid"
			ref="input"
			v-bind="$props"
			type="select"
			theme="field"
			v-on="$listeners"
			@input="onInput"
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
	methods: {
		onInput(value) {
			this.value = value;
		},
	},
};
</script>
