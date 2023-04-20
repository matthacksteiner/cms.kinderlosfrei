panel.plugin("baukasten-blocks-preview/preview", {
	blocks: {
		// image: `
		// 	<div>
		// 		<ul
		// 			@dblclick="open"
		// 		>
		// 			<template>
		// 				<li v-for="image in content.image" :key="image.id">
		// 					<img :src="image.url" :srcset="image.image.srcset" :alt="image.alt" />
		// 				</li>
		// 			</template>
		// 		</ul>
		// 		<div data-theme="help" class="k-text k-field-help">
		// 			<k-icon type="images" />
		// 			<span>Bilder</span>
		// 		</div>
		// 	</div>
		// `,
		// title: `
		// 	<div
		// 		@dblclick="open"
		// 	>
		// 		<div class="k-grid">
		// 			<template>
		// 				<h3 v-html="content.title" class="k-column" data-width="4/4">
		// 				</h3>
		// 			</template>
		// 			</div>
		// 			<div data-theme="help" class="k-text k-field-help">
		// 				<k-icon type="title" />
		// 				<span>Title</span>
		// 			</div>
		// 	</div>
		// `,
		// textCustom: `
		// 	<div
		// 		@dblclick="open"
		// 	>
		// 		<div class="k-grid">
		// 			<template>
		// 				<div v-html="content.text" class="k-column" data-width="4/4">
		// 				</div>
		// 			</template>
		// 			</div>
		// 			<div data-theme="help" class="k-text k-field-help">
		// 				<k-icon type="text" />
		// 				<span>Text</span>
		// 			</div>
		// 	</div>
		// `,
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
		</div>
    `,
		button: `
		<k-button class="linkButton" icon="url">Button</k-button>
  `,
	},
});
