panel.plugin("baukasten-blocks-preview/preview", {
	blocks: {
		textCustom: `
		<div
			@dblclick="open"
		>
			<div class="k-grid">
			<template><div v-html="content.text" class="k-column" data-width="3/4"></div></template>
			</div>
			<div data-theme="help" class="k-text k-field-help"><span>Text</div>
		</div>
    `,
	},
});
