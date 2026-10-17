import { config } from "@vue/test-utils";
import dayjs from "dayjs";

config.global.mocks = {
	$library: { dayjs },
	$t: (key: string) => key,
};

// like Kirby's own tests, unresolved `k-*` components render as their tags
config.global.config.warnHandler = (msg, instance, trace) => {
	if (msg.includes("Failed to resolve component: k-")) {
		return;
	}

	console.warn(msg + trace);
};
