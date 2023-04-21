panel.plugin("baukasten-blocks-preview/preview", {
	blocks: {
		accordion: {
			computed: {
				items() {
					return this.content.acc || { marks: true };
				},
			},
			methods: {
				updateItem(content, index, fieldName, value) {
					content.acc[index][fieldName] = value;
					this.$emit("update", {
						...this.content,
						...content,
					});
				},
			},
			template: `
			  <div>
				<div v-if="items.length">
				  <details v-for="(item, index) in items" :key="index">
					<summary>
					  <k-writer
						ref="title"
						:inline="true"
						:marks="false"
						:value="item.title"
						@input="updateItem(content, index, 'title', $event)"
					  />
					</summary>
					<k-writer
					  class="label"
					  ref="text"
					  :nodes="false"
					  :marks="false"
					  :value="item.text"
					  @input="updateItem(content, index, 'text', $event)"
					/>
				  </details>
				</div>
				<div v-else>Noch keine Akkordeon Elemente vorhanden.</div>
			  </div>
			`,
		},
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
