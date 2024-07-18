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
		quoteSlider: {
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
						<div v-for="(item, index) in items" :key="index" class="quote-item">
							<k-writer
								class="label"
								ref="text"
								:nodes="false"
								:marks="false"
								:value="item.text"
								@input="updateItem(content, index, 'text', $event)"
							/>
							<k-writer
								ref="author"
								:inline="true"
								:marks="false"
								:value="item.author"
								class="author"
								@input="updateItem(content, index, 'author', $event)"
							/>
						</div>
					</div>
					<div v-else>Noch keine Zitate vorhanden.</div>
				</div>
			`,
		},

		iconlist: {
			computed: {
				items() {
					return this.content.list || { marks: true };
				},
			},
			methods: {
				updateItem(content, index, fieldName, value) {
					content.list[index][fieldName] = value;
					this.$emit("update", {
						...this.content,
						...content,
					});
				},
			},
			template: `
			  <div class="k-iconlist">
				<div v-if="items.length">
				  <details v-for="(item, index) in items" :key="index">
					<summary>
					  <k-writer
						ref="text"
						:inline="true"
						:nodes="false"
					  	:marks="false"
						:value="item.text"
						@input="updateItem(content, index, 'text', $event)"
					  />
				  </details>
				</div>
				<div v-else>Noch keine Icon Liste Elemente vorhanden.</div>
			  </div>
			`,
		},
		divider: `
			<div class="k-divider k-grid">
				<div class="k-column" data-width="1/4">
				<span>⬆️ 📱</span>
				<input type="number" :value="content.spacingmobiletop" :placeholder="0" @input="update({ spacingmobiletop: $event.target.value })">
				<span>px</span>
				</div>
				<div class="k-column" data-width="1/4">
				<span>⬇️ 📱</span>
				<input type="number" :value="content.spacingmobilebottom" :placeholder="0" @input="update({ spacingmobilebottom: $event.target.value })">
				<span>px</span>
				</div>
				<div class="k-column" data-width="1/4">
				<span>⬆️ 🖥️</span>
				<input type="number" :value="content.spacingdesktoptop" :placeholder="0" @input="update({ spacingdesktoptop: $event.target.value })">
				<span>px</span>
				</div>
				<div class="k-column" data-width="1/4">
				<span>⬇️ 🖥️</span>
				<input type="number" :value="content.spacingdesktopbottom" :placeholder="0" @input="update({ spacingdesktopbottom: $event.target.value })">
				<span>px</span>
				</div>
			</div>
    `,
		button: `
		<k-button class="linkButton" icon="url">Button</k-button>
  `,
	},
});
