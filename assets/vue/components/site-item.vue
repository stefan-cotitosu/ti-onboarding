<template>
	<div class="site-box" :class="site_data.pricing">
		<div class="preview-image" :class="{ 'demo-pro' : site_data.in_pro }">
			<img :src="site_data.screenshot" :alt="site_data.title">
		</div>
		<div class="footer">
			<h4>{{site_data.title}}</h4>
            <div class="theme-actions">
                <button class="button button-secondary" v-on:click="showPreview()">
                    {{this.$store.state.strings.preview_btn}}
                </button>
                <button class="button button-primary" v-if="! site_data.in_pro" v-on:click="importSite()">
                    {{strings.import_btn}}
                </button>
            </div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'site-item',
        data: function() {
            return {
                strings: this.$store.state.strings
            }
        },
		props: {
			site_data: {
				default: {},
				type: Object,
				required: true,
			},
		},
		methods: {
            setupImportData: function () {
				let plugins = Object.keys( this.site_data.recommended_plugins ).reduce( function ( previous, current ) {
					previous[ current ] = true;
					return previous;
				}, {} );

				this.$store.commit( 'updatePlugins', plugins );
			},
            importSite: function() {
                this.setupImportData();
                this.$store.commit( 'populatePreview', this.site_data );
                this.$store.commit( 'showImportModal', true );
            },
            showPreview: function() {
                this.setupImportData();
                this.$store.commit( 'showPreview', true );
                this.$store.commit( 'populatePreview', this.site_data );
            }
		},
	}
</script>