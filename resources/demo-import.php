<?php
/**
 * Demo Import Page.
 *
 * @package RT\DemoImport
 */

/**
 * Template variables:
 *
 * @var $themeConfig   array
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
?>

<div class="wrap rtdi-demo-importer-wrap">
	<h2><?php echo esc_html__( 'Radius Demo Importer', 'radius-demo-importer' ); ?></h2>

	<?php
	if ( is_array( $themeConfig ) && ! empty( $themeConfig ) ) {
		$tags         = [];
		$pageBuilders = [];

		foreach ( $themeConfig as $demo_slug => $demo_pack ) {
			if ( isset( $demo_pack['tags'] ) && is_array( $demo_pack['tags'] ) ) {
				foreach ( $demo_pack['tags'] as $key => $tag ) {
					$tags[ $key ] = $tag;
				}
			}
		}

		foreach ( $themeConfig as $demo_slug => $demo_pack ) {
			if ( isset( $demo_pack['pageBuilder'] ) && is_array( $demo_pack['pageBuilder'] ) ) {
				foreach ( $demo_pack['pageBuilder'] as $key => $pageBuilder ) {
					$pageBuilders[ $key ] = $pageBuilder;
				}
			}
		}

		asort( $tags );
		asort( $pageBuilders );

		if ( ! empty( $tags ) || ! empty( $pageBuilders ) ) {
			?>
			<div class="rtdi-tab-filter rtdi-clearfix">
				<?php
				if ( ! empty( $tags ) ) {
					?>
					<div class="rtdi-tab-group rtdi-tag-group" data-filter-group="tag">
						<div class="rtdi-tab" data-filter="*">
							<?php esc_html_e( 'All', 'radius-demo-importer' ); ?>
						</div>
						<?php
						foreach ( $tags as $key => $value ) {
							?>
							<div class="rtdi-tab" data-filter=".<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $value ); ?>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				}

				if ( ! empty( $pageBuilders ) ) {
					?>
					<div class="rtdi-tab-group rtdi-pageBuilder-group" data-filter-group="pageBuilder">
						<div class="rtdi-tab" data-filter="*">
							<?php esc_html_e( 'All', 'radius-demo-importer' ); ?>
						</div>
						<?php
						foreach ( $pageBuilders as $key => $value ) {
							?>
							<div class="rtdi-tab" data-filter=".<?php echo esc_attr( $key ); ?>">
								<?php echo esc_html( $value ); ?>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>

		<div class="rtdi-demo-box-wrap wp-clearfix">
			<?php
			foreach ( $themeConfig as $demo_slug => $demo_pack ) {
				$tags         = '';
				$pageBuilders = '';
				$class        = '';

				if ( isset( $demo_pack['tags'] ) ) {
					$tags = implode( ' ', array_keys( $demo_pack['tags'] ) );
				}

				if ( isset( $demo_pack['pageBuilder'] ) ) {
					$pageBuilders = implode( ' ', array_keys( $demo_pack['pageBuilder'] ) );
				}

				$classes = $tags . ' ' . $pageBuilders;

				$type = ! empty( $demo_pack['type'] ) ? $demo_pack['type'] : 'free';
				?>
				<div id="<?php echo esc_attr( $demo_slug ); ?>"
					 class="rtdi-demo-box <?php echo esc_attr( $classes ); ?>">
					<div class="rtdi-demo-elements">
						<?php
						if ( 'pro' === $type ) {
							?>
							<div class="rtdi-ribbon"><span>Premium</span></div>
							<?php
						}
						?>

						<img src="<?php echo esc_url( $demo_pack['image'] ); ?> " alt="Demo Screenshot">

						<div class="rtdi-demo-actions">

							<h4><?php echo esc_html( $demo_pack['name'] ); ?></h4>

							<div class="rtdi-demo-buttons">
								<a href="<?php echo esc_url( $demo_pack['preview_url'] ); ?>" target="_blank"
								   class="button">
									<?php echo esc_html__( 'Preview', 'radius-demo-importer' ); ?>
								</a>

								<?php
								if ( 'pro' === $type ) {
									$buy_url = ! empty( $demo_pack['buy_url'] ) ? $demo_pack['buy_url'] : '#';
									?>
									<a target="_blank" href="<?php echo esc_url( $buy_url ); ?>"
									   class="button button-primary">
										<?php echo esc_html__( 'Buy Now', 'radius-demo-importer' ); ?>
									</a>
								<?php } else { ?>
									<a href="#rtdi-modal-<?php echo esc_attr( $demo_slug ); ?>"
									   class="rtdi-modal-button button button-primary">
										<?php echo esc_html__( 'Install', 'radius-demo-importer' ); ?>
									</a>
									<?php
								}
								?>
							</div>

						</div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	} else {
		?>
		<div class="rtdi-demo-wrap">
			<div class="no-demo-found">
				<?php
				esc_html_e( 'We apologize for any inconvenience, but it appears that the configuration file for the demo importer is either missing or contains errors. As a result, the installation of the demo content cannot proceed any further at this time. Thank you for your understanding.', 'radius-demo-importer' );
				?>
			</div>
		</div>
		<?php
	}
	?>

	<?php
	if ( is_array( $themeConfig ) && ! empty( $themeConfig ) ) {
		foreach ( $themeConfig as $demo_slug => $demo_pack ) {
			?>
			<div id="rtdi-modal-<?php echo esc_attr( $demo_slug ); ?>" class="rtdi-modal" style="display: none;">

				<div class="rtdi-modal-header">
					<h2>
						<?php
						printf(
						/* translators: Demo Name */
							esc_html__( 'Import %s Demo', 'radius-demo-importer' ),
							esc_html( $demo_pack['name'] )
						);
						?>
					</h2>
					<div class="rtdi-modal-back"><span class="dashicons dashicons-no-alt"></span></div>
				</div>

				<div class="rtdi-modal-wrap">
					<p>
						<?php
						echo esc_html__( 'We recommend you backup your website content before attempting to import the demo so that you can recover your website if something goes wrong.', 'radius-demo-importer' );
						?>
					</p>

					<p><?php echo esc_html__( 'This process will install all the required plugins, import contents and setup customizer and theme options.', 'radius-demo-importer' ); ?></p>

					<div class="rtdi-modal-recommended-plugins">
						<h4><?php esc_html_e( 'Required Plugins', 'radius-demo-importer' ); ?></h4>
						<p><?php esc_html_e( 'For your website to look exactly like the demo,the import process will install and activate the following plugin if they are not installed or activated.', 'radius-demo-importer' ); ?></p>
						<?php
						$plugins = ! empty( $demo_pack['plugins'] ) ? $demo_pack['plugins'] : '';

						if ( is_array( $plugins ) ) {
							?>
							<ul class="rtdi-plugin-status">
								<?php
								foreach ( $plugins as $plugin ) {
									$name   = ! empty( $plugin['name'] ) ? $plugin['name'] : '';
									$status = HDI_Demo_Importer::plugin_active_status( $plugin['file_path'] );
									if ( 'active' === $status ) {
										$plugin_class = '<span class="dashicons dashicons-yes-alt"></span>';
									} elseif ( 'inactive' === $status ) {
										$plugin_class = '<span class="dashicons dashicons-warning"></span>';
									} else {
										$plugin_class = '<span class="dashicons dashicons-dismiss"></span>';
									}
									?>
									<li class="rtdi-<?php echo esc_attr( $status ); ?>">
										<?php
										echo wp_kses_post( $plugin_class ) . ' ' . esc_html( $name ) . ' - <i>' . esc_html( $this->get_plugin_status( $status ) ) . '</i>';
										?>
									</li>
									<?php
								}
								?>
							</ul>
							<?php
						} else {
							?>
							<ul>
								<li><?php esc_html_e( 'No Required Plugins Found.', 'radius-demo-importer' ); ?></li>
							</ul>
							<?php
						}
						?>
					</div>

					<div class="rtdi-exclude-image-checkbox">
						<h4><?php esc_html_e( 'Exclude Images', 'radius-demo-importer' ); ?></h4>
						<p><?php esc_html_e( 'Check this option if importing demo fails multiple times. Excluding image will make the demo import process super quick.', 'radius-demo-importer' ); ?></p>
						<label>
							<input id="checkbox-exclude-image-<?php echo esc_attr( $demo_slug ); ?>" type="checkbox" value='1'/>
							<?php echo esc_html__( 'Yes, Exclude Images', 'radius-demo-importer' ); ?>
						</label>
					</div>

					<div class="rtdi-reset-checkbox">
						<h4><?php esc_html_e( 'Reset Website', 'radius-demo-importer' ); ?></h4>
						<p><?php esc_html_e( 'Reseting the website will delete all your post, pages, custom post types, categories, taxonomies, images and all other customizer and theme option settings.', 'radius-demo-importer' ); ?></p>
						<p><?php esc_html_e( 'It is always recommended to reset the database for a complete demo import.', 'radius-demo-importer' ); ?></p>
						<label class="rtdi-reset-website-checkbox">
							<input id="checkbox-reset-<?php echo esc_attr( $demo_slug ); ?>" type="checkbox" value='1' checked="checked"/>
							<?php echo esc_html__( 'Reset Website - Check this box only if you are sure to reset the website.', 'radius-demo-importer' ); ?>
						</label>
					</div>

					<a href="javascript:void(0)" data-demo-slug="<?php echo esc_attr( $demo_slug ); ?>" class="button button-primary rtdi-import-demo"><?php esc_html_e( 'Import Demo', 'radius-demo-importer' ); ?></a>
					<a href="javascript:void(0)" class="button rtdi-modal-cancel"><?php esc_html_e( 'Cancel', 'radius-demo-importer' ); ?></a>
				</div>
			</div>
			<?php
		}
	}
	?>
	<div id="rtdi-import-progress" style="display: none">
		<h2 class="rtdi-import-progress-header"><?php echo esc_html__( 'Demo Import Progress', 'radius-demo-importer' ); ?></h2>

		<div class="rtdi-import-progress-wrap">
			<div class="rtdi-import-loader">
				<div class="rtdi-loader-content">
					<div class="rtdi-loader-content-inside">
						<div class="rtdi-loader-rotater"></div>
						<div class="rtdi-loader-line-point"></div>
					</div>
				</div>
			</div>
			<div class="rtdi-import-progress-message"></div>
		</div>
	</div>
</div>