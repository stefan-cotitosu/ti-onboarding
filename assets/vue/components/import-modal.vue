<template>
	<div class="import-modal__wrapper">
		<div class="modal__item" v-on-clickaway="closeModal">
			<div class="modal__header">
				<div class="background" :style="{ backgroundImage: 'url(' + siteData.screenshot + ')' }"></div>
				<button type="button" class="close" @click="closeModal">×</button>
				<h2 class="title ellipsis">{{siteData.title}}</h2>
			</div>
			<div class="modal__content">
				<template v-if="currentStep !== 'done'">
					<div class="disclaimers">
						<strong><i class="dashicons dashicons-info"></i>{{strings.note}}:</strong>
						<ol>
							<li>{{strings.backup_disclaimer}}</li>
							<li>{{strings.placeholders_disclaimer}}</li>
						</ol>
					</div>

					<div class="import__options">
						<h4>{{strings.general}}:</h4>
						<ul class="features">
							<li class="option_toggle">
								<label class="option-toggle-label"
										:class="importOptions.content ? 'active' : 'inactive'"><span
										class="dashicons dashicons-admin-post"></span><span>{{strings.content}}</span></label>
								<toggle-button @change="adjustImport( 'content' )" :value="importOptions.content"
										color="#008ec2"></toggle-button>
							</li>
							<li class="option_toggle">
								<label class="option-toggle-label"
										:class="importOptions.customizer ? 'active' : 'inactive'"><span
										class="dashicons dashicons-admin-customizer"></span><span>{{strings.customizer}}</span></label>
								<toggle-button @change="adjustImport( 'customizer' )" :value="importOptions.customizer"
										color="#008ec2"></toggle-button>
							</li>
							<li class="option_toggle">
								<label class="option-toggle-label"
										:class="importOptions.widgets ? 'active' : 'inactive'"><span
										class="dashicons dashicons-admin-generic"></span><span>{{strings.widgets}}</span></label>
								<toggle-button @change="adjustImport( 'widgets' )" :value="importOptions.widgets"
										color="#008ec2"></toggle-button>
							</li>
						</ul>
						<h4>{{strings.plugins}}:</h4>
						<ul class="features">
							<li class="option_toggle" v-for="( plugin, index ) in siteData.recommended_plugins">
								<label class="option-toggle-label ellipsis"
										:class="{ 'active' : importOptions.installablePlugins[index] }">
									<span class="dashicons dashicons-admin-plugins"></span>
									<span v-html="plugin"></span></label>
								<toggle-button @change="adjustPlugins( index, plugin )"
										:value="importOptions.installablePlugins[index]"
										color="#008ec2"></toggle-button>
							</li>
						</ul>
					</div>
				</template>
				<h3 v-else>{{strings.import_done}}</h3>
			</div>

			<div class="modal__footer" v-if="! importing">
				<template v-if="currentStep !== 'done'">
					<button class="button button-secondary" v-on:click="closeModal">{{strings.cancel_btn}}</button>
					<button class="button button-primary" :disabled="! checIfShouldImport" v-on:click="startImport">
						{{strings.import_btn}}
					</button>
				</template>
				<div v-else class="after__actions">
					<a class="button-link" v-if="this.$store.state.onboard !== 'yes'" v-on:click="resetImport">{{strings.back}}</a>
					<button class="button button-secondary" v-on:click="redirectToHome">{{strings.go_to_site}}</button>
					<button class="button button-primary" v-on:click="editTemplate">{{strings.edit_template}}</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import { directive as onClickaway } from 'vue-clickaway'
	import Stepper from './stepper.vue'
	import Loader from './loader.vue'
	import Tabs from './tabs.vue'

	export default {
		name: 'import-modal',
		data: function () {
			return {
				strings: this.$store.state.strings,
				homeUrl: this.$store.state.homeUrl,
				siteData: this.$store.state.previewData,
				advancedExpanded: false,
			}
		},
		computed: {
			currentStep() {
				return this.$store.state.currentStep;
			},
			importing() {
				return this.$store.state.importing;
			},
			checIfShouldImport() {
				if (
					this.$store.state.importOptions.content ||
					this.$store.state.importOptions.customizer ||
					this.$store.state.importOptions.widgets
				) {
					return true;
				}
				return false;
			},
			importOptions() {
				return this.$store.state.importOptions;
			}
		},
		methods: {
			toggleAdvanced() {
				this.advancedExpanded = !this.advancedExpanded
			},
			adjustPlugins: function ( index, plugin ) {
				let plugins = this.$store.state.importOptions.installablePlugins;
				plugins[ index ] = !plugins[ index ];
				this.$store.commit( 'updatePlugins', plugins );
			},
			adjustImport: function ( context ) {
				let options = this.$store.state.importOptions;
				options[ context ] = !options[ context ];
				this.$store.commit( 'updateImportOptions', options );
			},
			getEditor: function () {
				return this.$store.state.editor;
			},
			getPageId: function () {
				return this.$store.state.frontPageId;
			},
			closeModal: function () {
				if ( this.importing ) {
					return false
				}
				if ( this.currentStep === 'done' ) {
					return false
				}
				this.$store.commit( 'showImportModal', false )
			},
			startImport: function () {
				this.$store.dispatch( 'importSite', {
					req: 'Import Site',
					plugins: this.siteData.recommended_plugins,
					content: {
						'content_file': this.siteData.content_file,
						'front_page': this.siteData.front_page,
						'shop_pages': this.siteData.shop_pages,
					},
					themeMods: {
						'theme_mods': this.siteData.theme_mods,
						'source_url': this.siteData.demo_url
					},
					widgets: this.siteData.widgets,
					source: this.siteData.source,
				} )
			},
			redirectToHome: function () {
				window.location.replace( this.homeUrl );
			},
			resetImport: function () {
				this.$store.commit( 'resetStates' );
			},
			editTemplate: function () {
				var editor = this.getEditor();
				var pageId = this.getPageId();
				var url = this.homeUrl;
				if ( editor === 'elementor' ) {
					url = this.homeUrl + '/wp-admin/post.php?post=' + pageId + '&action=elementor';
				}
				if ( editor === 'gutenberg' ) {
					url = this.homeUrl + '/wp-admin/post.php?post=' + pageId + '&action=edit';
				}
				window.location.replace( url );
			}
		},
		beforeMount() {
			let body = document.querySelectorAll( '#ti-sites-library .is__onboarding' )[ 0 ];
			body.style.overflow = 'hidden';
		},
		beforeDestroy() {
			let body = document.querySelectorAll( '#ti-sites-library .is__onboarding' )[ 0 ];
			body.style.overflow = '';
		},
		directives: {
			onClickaway,
		},
		components: {
			Stepper,
			Loader,
			Tabs
		}
	}
</script>