<?php
namespace HouzezStudio\admin\metaboxes;

use HouzezStudio\admin\fieldsManager as FieldManager;

defined( 'ABSPATH' ) || exit;

class Houzez_Studio_Metaboxes {

	/**
	 * Version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';


	/**
	 * The single instance of the class.
	 *
	 * @var Houzez_Studio_Metaboxes
	 * @since 1.0
	 */
	private static $_instance;

	/**
	 * Main Houzez_Studio_Metaboxes Instance.
	 *
	 * Ensures only one instance of Houzez_Studio_Metaboxes is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return Houzez_Studio_Metaboxes - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}



	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden.', 'houzez-studio' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'houzez-studio' ), '1.0' );
	}


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'metaboxes' ) );
		add_action( 'save_post', array( $this, 'save_metabox_data' ) );
	}


	/**
	 * Meta Box In btf_builder post type.
	 */
	public function metaboxes() {
		add_meta_box( 'fts_metaboxes_setting', 'Template Settings', array( $this, 'fts_metaboxes_output' ), 'fts_builder', 'normal', 'high' );
	}


	/**
	 * Render Meta field.
	 *
	 * @param  POST $post Currennt post object which is being displayed.
	 */
	function fts_metaboxes_output( $post ) {
		$values            = get_post_custom( $post->ID );
		$template_type     = isset( $values['fts_template_type'] ) ? esc_attr( $values['fts_template_type'][0] ) : '';
		
		// We'll use this nonce field later on when saving.
		wp_nonce_field( 'fts_meta_nounce', 'fts_meta_nounce' );
		?>
		<table class="houzez-custom-wpadmin-table houzez-fts-options-table fts-options-none">
			<tbody>
				<tr>
					<th scope="row">
						<label for="select_id"><?php _e( 'Template Type', 'houzez-studio' ); ?></label>
					</th>
					<td>
						<div class="houzez-custom-wpadmin-form-row">
							<select name="fts_template_type" id="fts_template_type">
								<option value="" <?php selected( $template_type, '' ); ?>><?php _e( 'Select Option', 'houzez-studio' ); ?></option>
								
								<option value="tmp_header" <?php selected( $template_type, 'tmp_header' ); ?>><?php _e( 'Header', 'houzez-studio' ); ?></option>

								<option value="tmp_before_header" <?php selected( $template_type, 'tmp_before_header' ); ?>><?php _e( 'Before Header', 'houzez-studio' ); ?></option>

								<option value="tmp_after_header" <?php selected( $template_type, 'tmp_after_header' ); ?>><?php _e( 'After Header', 'houzez-studio' ); ?></option>

								<option value="tmp_footer" <?php selected( $template_type, 'tmp_footer' ); ?>><?php _e( 'Footer', 'houzez-studio' ); ?></option>
								<option value="tmp_after_footer" <?php selected( $template_type, 'tmp_after_footer' ); ?>><?php _e( 'After Footer', 'houzez-studio' ); ?></option>

								<option value="tmp_before_footer" <?php selected( $template_type, 'tmp_before_footer' ); ?>><?php _e( 'Before Footer', 'houzez-studio' ); ?></option>

								<option value="tmp_megamenu" <?php selected( $template_type, 'tmp_megamenu' ); ?>><?php _e( 'Mega Menu', 'houzez-studio' ); ?></option>

								<option value="tmp_custom_block" <?php selected( $template_type, 'tmp_custom_block' ); ?>><?php _e( 'Custom Block', 'houzez-studio' ); ?></option>
							</select>
						</div><!-- houzez-custom-wpadmin-form-row -->
					</td>
				</tr>
				
				<?php $this->display_rules_tab(); ?>

				<tr class="fts-row fts-shortcode-row">
					<th scope="row">
						<label for="fts_shortcode">
							<?php esc_html_e( 'Shortcode', 'houzez-studio' ); ?>
						</label>
					</th>
					<td>
						<span class="fts-shortcode-wrap">
							<input type="text" onfocus="this.select();" readonly="readonly" value="[fts_template id='<?php echo esc_attr( $post->ID ); ?>']" class="code">
						</span>
					</td>
				</tr>

			</tbody>
		</table>
		<?php
	}

	/**
	 * Markup for Display Rules Tabs.
	 *
	 * @since  1.0.0
	 */
	public function display_rules_tab() {
	
		$included_settings = get_post_meta( get_the_id(), 'fts_included_options', true );
		$excluded_settings = get_post_meta( get_the_id(), 'fts_excluded_options', true );
		?>
		<tr class="fts-row">
			<th scope="row">
				<label for="fts_included_display_rules">
					<?php esc_html_e( 'Display Location', 'houzez-studio' ); ?>
				</label>
			</th>
			<td>
				<?php
				FieldManager\Favethemes_Field_Manager::fts_FieldSettings(
					'fts_included_display_rules',
					[
						'rule_type'      => 'display',
						'button_label' => __( 'Create Display Rule', 'houzez-studio' ),
					],
					$included_settings
				);
				?>
			</td>
		</tr>

		<tr class="fts-row hidden">
			<th scope="row">
				<label for="fts_included_display_rules">
					<?php esc_html_e( 'Exlcude Location', 'houzez-studio' ); ?>
				</label>
			</th>
			<td>
				<?php
				FieldManager\Favethemes_Field_Manager::fts_FieldSettings(
					'fts_excluded_display_rules',
					[
						'rule_type'      => 'exclude',
						'button_label' => __( 'Create Exlcude Rule', 'houzez-studio' ),
					],
					$excluded_settings
				);
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save metabox data for the current post.
	 *
	 * @param int $postId Current post ID.
	 */
	public function save_metabox_data($postId) {
	    if ($this->should_not_save_metabox($postId)) {
	        return;
	    }

	    $this->update_template_rules_metadata($postId, $_POST);
	    $this->update_template_type_metadata($postId, $_POST);
	}

	/**
	 * Determine if metabox data should not be saved.
	 *
	 * @param int $postId Current post ID.
	 * @return bool True if data should not be saved, false otherwise.
	 */
	private function should_not_save_metabox($postId) {
	    return defined('DOING_AUTOSAVE') && DOING_AUTOSAVE
	        || !isset($_POST['fts_meta_nounce'])
	        || !wp_verify_nonce(sanitize_text_field($_POST['fts_meta_nounce']), 'fts_meta_nounce')
	        || !current_user_can('edit_post', $postId);
	}

	/**
	 * Update target location metadata.
	 *
	 * @param int   $postId Current post ID.
	 * @param array $postData POST data.
	 */
	private function update_template_rules_metadata($postId, $postData) {
	    $targetLocations = FieldManager\Favethemes_Field_Manager::format_rule_metadata($postData, 'fts_included_display_rules');
	    $targetExclusion = FieldManager\Favethemes_Field_Manager::format_rule_metadata($postData, 'fts_excluded_display_rules');
	    
	    update_post_meta($postId, 'fts_included_options', $targetLocations);
	    update_post_meta($postId, 'fts_excluded_options', $targetExclusion);
	}

	/**
	 * Update template type metadata.
	 *
	 * @param int   $postId Current post ID.
	 * @param array $postData POST data.
	 */
	private function update_template_type_metadata($postId, $postData) {
	    if (isset($postData['fts_template_type'])) {
	        update_post_meta($postId, 'fts_template_type', sanitize_text_field($postData['fts_template_type']));
	    }
	}

}
Houzez_Studio_Metaboxes::instance();