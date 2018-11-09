<template>
	<div :class="{ 'is__onboarding' : this.$store.state.onboard === 'yes' && ! previewOpen } ">

		<div :class="! isLoading ? 'library-wrapper' : '' ">
			<Loader v-if="isLoading" :loading-message="loadingString"></Loader>
			<template v-else>
				<template v-if="this.$store.state.onboard === 'yes'">
					<div class="header">
						<h1>{{strings.onboard_header}}</h1>
						<p>{{strings.onboard_description}}</p>
					</div>
				</template>

				<div class="migration-wrapper" v-if="this.migrationData">
					<div class="ti-sites-lib">
						<div class="site-box">
							<div class="preview-image">
								<img :src="migrationData.screenshot" :alt="migrationData.theme_name">
							</div>
							<div class="footer">
								<h4>{{migrationData.theme_name}}</h4>
								<div class="theme-actions">
									<button class="button button-primary" @click="runMigration()">
										{{strings.import_btn}}
									</button>
								</div>
							</div>
						</div>
						<div class="migrate-description">
							<h3>{{strings.migration_title}}</h3>
							<p>{{migrationData.description}}</p>
						</div>
					</div>
					<hr/>
				</div>

				<h3>{{strings.templates_title}}</h3>
				<p>{{strings.templates_description}}</p>

				<div class="ti-sites-lib">
					<div v-for="site in sites.local">
						<SiteItem :site_data="site"></SiteItem>
					</div>
					<div v-for="site in sites.remote">
						<SiteItem :site_data="site"></SiteItem>
					</div>
					<Preview v-if="previewOpen"></Preview>
				</div>
			</template>
		</div>
		<div class="skip-wrap" v-if="this.$store.state.onboard === 'yes' && ! isLoading">
			<a @click="cancelOnboarding" class="skip-onboarding button button-primary">
				{{strings.later}}
			</a>
		</div>
		<import-modal v-if="modalOpen">
		</import-modal>
	</div>
</template>

<script>
	import Loader from './loader.vue'
	import SiteItem from './site-item.vue'
	import Preview from './preview.vue'
	import ImportModal from './import-modal.vue'

	module.exports = {
		name: 'app',
		data: function () {
			return {
				strings: this.$store.state.strings,
			}
		},
		computed: {
			isLoading: function () {
				return this.$store.state.ajaxLoader
			},
			sites: function () {
				return this.$store.state.sitesData
			},
			previewOpen: function () {
				return this.$store.state.previewOpen
			},
			loadingString: function () {
				return this.$store.state.strings.loading;
			},
			modalOpen: function () {
				return this.$store.state.importModalState
			},
			migrationData: function () {
				return this.$store.state.sitesData.migrate_data
			}
		},
		methods: {
			cancelOnboarding: function () {
				this.$store.state.onboard = null;
			},
			runMigration: function () {
				this.$store.dispatch( 'migrateTemplate', {
					req: 'Migrate Site',
					template: this.migrationData.template,
				} )
			}
		},
		components: {
			Loader,
			SiteItem,
			Preview,
			ImportModal,
		},
	}
</script>

<style>
	h4 {
		display: block;
		white-space: nowrap;
		text-overflow: ellipsis;
		margin: 0;
		overflow: hidden;
		max-width: 70%;
		font-size: 15px;
	}

	.site-box {
		border: 1px solid #ccc;
	}

	.site-box:hover .footer .theme-actions {
		display: block;
	}

	.footer {
		position: relative;
		border-top: 1px solid #ccc;
		display: flex;
		padding: 15px;
		flex-wrap: wrap;
		align-items: center;
	}

	.footer .theme-actions {
		display: none;
		position: absolute;
		right: 0;
		padding: 10px 15px;
		background-color: rgba(244, 244, 244, 0.7);
		border-left: 1px solid rgba(0, 0, 0, 0.05);
	}
</style>