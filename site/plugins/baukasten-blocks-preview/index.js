panel.plugin("baukasten-blocks-preview/preview", {
	blocks: {
		textCustom: `
		<div
			@dblclick="open"
		>
			<div class="k-grid">
			<template><div v-html="content.text" class="k-column" data-width="4/4"></div></template>
			</div>
			<div data-theme="help" class="k-text k-field-help"><span>Text</div>
		</div>
    `,
		divider: `
		<div
			@dblclick="open"
		>
			<div class="k-grid">
			<template>
				<div class="k-column" data-width="1/4">
				<span>⬆️ Mobile:</span>
				<span v-html="content.spacingmobiletop"></span>
				<span>px</span>
				</div>
				<div class="k-column" data-width="1/4">
				<span>⬇️ Mobile:</span>
				<span v-html="content.spacingmobilebottom"></span>
				<span>px</span>
				</div>
				<div class="k-column" data-width="1/4">
				<span>⬆️ Desktop</span>
				<span v-html="content.spacingdesktoptop"></span>
				<span>px</span>
				</div>
				<div class="k-column" data-width="1/4">
				<span>⬇️ Desktop:</span>
				<span v-html="content.spacingdesktopbottom"></span>
				<span>px</span>
				</div>
			</template>
			</div>
			<div data-theme="help" class="k-text k-field-help"><span>Abstände</div>
		</div>
    `,
	},
});
