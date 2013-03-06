<?php

class Group_Buying_Merchant_Meta extends Group_Buying_Controller {

	const META_FEATURED = 'gb_merchant_featured';
	const TAX = 'gb_merchant_featured';
	const TERM = 'merchants';
	const REWRITE_SLUG = 'featured';
	const DEBUG = TRUE;

	private static $meta_keys = array(
		'legal_docs' => '_gb_legal_docs', // string
		'financial_statement' => '_gb_financial_statement', // string
		'prev_financial_statement' => '_gb_prev_financial_statement', // string
		'annual_report' => '_gb_annual_report', // string
		'articles_corp' => '_gb_articles_corp', // string
		'board_members' => '_gb_board_members', // string
		'agreements' => '_gb_agreements', // string
	); // A list of meta keys this class cares about. Try to keep them in alphabetical order.

	public static function init() {

		add_action( 'init', array( get_class(), 'init_tax' ), 0 );
		// Template
		add_filter( 'template_include', array( get_class(), 'override_template' ) );
		// Meta Boxes
		add_action( 'add_meta_boxes', array( get_class(), 'add_meta_boxes' ) );
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );

		// Filter existing fields
		add_filter( 'gb_merchant_register_contact_info_fields', array( get_class(), 'filter_existing_contact_fields' ), 10, 2 );
		// Save new fields
		add_action( 'register_merchant', array( get_class(), 'process_post' ) );
		add_action( 'edit_merchant', array( get_class(), 'process_post' ) );
		// Validate
		add_filter( 'gb_validate_merchant_registration', array( get_class(), 'validate_fields' ), 10, 2 );

	}

	public function filter_existing_contact_fields( $fields, $merchant ) {


		$fields['legal_docs'] = array(
			'weight' => 70,
			'label' => self::__( 'Legal Documents of Registration' ),
			'type' => 'file',
			'required' => TRUE,
			'default' => '',
			'description' => gb__( '' )
		);
		$fields['financial_statement'] = array(
			'weight' => 75,
			'label' => self::__( 'Financial Statement for Current Year' ),
			'type' => 'file',
			'required' => TRUE,
			'default' => '',
			'description' => gb__( '' )
		);
		$fields['prev_financial_statement'] = array(
			'weight' => 80,
			'label' => self::__( 'Financial Statement for Last Year' ),
			'type' => 'file',
			'required' => TRUE,
			'default' => '',
			'description' => gb__( '' )
		);
		$fields['annual_report'] = array(
			'weight' => 85,
			'label' => self::__( 'Annual Report for Last Year' ),
			'type' => 'file',
			'required' => TRUE,
			'default' => '',
			'description' => gb__( '' )
		);
		$fields['articles_corp'] = array(
			'weight' => 90,
			'label' => self::__( 'Articles of Incorporation' ),
			'type' => 'file',
			'required' => TRUE,
			'default' => '',
			'description' => gb__( '' )
		);
		$fields['board_members'] = array(
			'weight' => 95,
			'label' => self::__( 'Name List of Staff and Board Members' ),
			'type' => 'file',
			'required' => TRUE,
			'default' => '',
			'description' => gb__( '' )
		);
		$fields['agreements'] = array(
			'weight' => 100,
			'label' => self::__( 'Agreement on Terms and Conditions <i>(to be reviewed and electronically signed)</i>' ),
			'type' => 'file',
			'required' => TRUE,
			'default' => '',
			'description' => gb__( 'Below this title, word file of terms and conditions of my org should be attached in order that applicants review. Terms and Conditions are not yet ready.' )
		);

		$fields['phone']['required'] = TRUE;
		return $fields;
	}

	public function process_post( Group_Buying_Merchant $merchant ) {
		error_log( "files: " . print_r( $_FILES, true ) );
		$merchant_id = $merchant->get_id();
		if ( !empty( $_FILES['gb_contact_legal_docs'] ) ) {
			// Set the uploaded field as an attachment
			$attach_id = self::_set_attachement( 'gb_contact_legal_docs', $merchant_id );
			error_log( "attach id: " . print_r( $attach_id, true ) );
			self::set_legal_docs( $merchant_id, (int) $attach_id );
		}
		if ( !empty( $_FILES['gb_contact_financial_statement'] ) ) {
			// Set the uploaded field as an attachment
			$attach_id = self::_set_attachement( 'gb_contact_financial_statement', $merchant_id );
			self::set_financial_statement( $merchant_id, (int) $attach_id );
		}
		if ( !empty( $_FILES['gb_contact_prev_financial_statement'] ) ) {
			// Set the uploaded field as an attachment
			$attach_id = self::_set_attachement( 'gb_contact_prev_financial_statement', $merchant_id );
			self::set_prev_financial_statement( $merchant_id, (int) $attach_id );
		}
		if ( !empty( $_FILES['gb_contact_annual_report'] ) ) {
			// Set the uploaded field as an attachment
			$attach_id = self::_set_attachement( 'gb_contact_annual_report', $merchant_id );
			self::set_annual_report( $merchant_id, (int) $attach_id );
		}
		if ( !empty( $_FILES['gb_contact_articles_corp'] ) ) {
			// Set the uploaded field as an attachment
			$attach_id = self::_set_attachement( 'gb_contact_articles_corp', $merchant_id );
			self::set_articles_corp( $merchant_id, (int) $attach_id );
		}
		if ( !empty( $_FILES['gb_contact_board_members'] ) ) {
			// Set the uploaded field as an attachment
			$attach_id = self::_set_attachement( 'gb_contact_board_members', $merchant_id );
			self::set_board_members( $merchant_id, (int) $attach_id );
		}
		if ( !empty( $_FILES['gb_contact_agreements'] ) ) {
			// Set the uploaded field as an attachment
			$attach_id = self::_set_attachement( 'gb_contact_agreements', $merchant_id );
			self::set_agreements( $merchant_id, (int) $attach_id );
		}

	}

	public function validate_fields( $errors, $post ) {
		foreach ( $errors as $error ) {
			if ( strpos( $error, 'Legal' ) !== false ) {
				unset( $errors[$error] );
			}
		}
	}


	/**
	 * Add a file as a post attachment.
	 */
	private function _set_attachement( $file_id, $merchant_id ) {
		if ( !function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin' . '/includes/image.php';
			require_once ABSPATH . 'wp-admin' . '/includes/file.php';
			require_once ABSPATH . 'wp-admin' . '/includes/media.php';
		}

		$attach_id = media_handle_upload( $file_id, $merchant_id, array( 'post_title' => str_replace( 'gb_contact_', '', $file_id ) ) );
		error_log( "return : " . print_r( $attach_id, true ) );
		return $attach_id;
	}

	public static function init_tax() {
		// register taxonomy
		$taxonomy_args = array(
			'hierarchical' => TRUE,
			'labels' => array( 'name' => gb__( 'Featured Merchant' ) ),
			'show_ui' => FALSE,
			'rewrite' => array(
				'slug' => self::REWRITE_SLUG,
				'with_front' => FALSE,
				'hierarchical' => FALSE,
			),
		);
		register_taxonomy( self::TAX, array( Group_Buying_Merchant::POST_TYPE ), $taxonomy_args );
	}

	public static function get_term_slug() {
		$term = get_term_by( 'slug', self::TERM, self::TAX );
		if ( !empty( $term->slug ) ) {
			return $term->slug;
		} else {
			$return = wp_insert_term(
				self::TERM, // the term
				self::TAX, // the taxonomy
				array(
					'description'=> 'This merchant is featured.',
					'slug' => self::TERM, )
			);
			return $return['slug'];
		}

	}

	public static function get_url() {
		$url = get_term_link( self::TERM, self::TAX );
		if ( $url ) {
			return $url;
		}
	}

	public static function is_merchant_query( WP_Query $query = NULL ) {
		$taxonomy = get_query_var( 'taxonomy' );
		if ( $taxonomy == self::TAX ) {
			return TRUE;
		}
		return FALSE;
	}

	public static function override_template( $template ) {
		if ( self::is_merchant_query() ) {
			$taxonomy = get_query_var( 'taxonomy' );
			$template = self::locate_template( array(
					'business/business-'.$taxonomy.'.php',
					'business/business-type.php',
					'business/business-types.php',
					'business/businesses.php',
					'business/business-index.php',
					'business/business-archive.php',
					'merchant/business-'.$taxonomy.'.php',
					'merchant/business-type.php',
					'merchant/business-types.php',
					'merchant/businesses.php',
					'merchant/business-index.php',
					'merchant/business-archive.php',
				), $template );
		}
		return $template;
	}

	/**
	 *
	 *
	 * @return int Alternative Price
	 */
	public function is_featured( Group_Buying_Merchant $merchant ) {
		$featured = array_pop( wp_get_object_terms( $merchant->get_id(), self::TAX ) );
		if ( !empty( $featured ) && $featured->slug = self::TERM ) {
			return TRUE;
		}
		return FALSE;
	}

	public static function add_meta_boxes() {
		add_meta_box( 'gb_merchant_meta', self::__( 'Custom Information' ), array( get_class(), 'show_meta_boxes' ), Group_Buying_Merchant::POST_TYPE, 'advanced', 'high' );
	}

	public static function show_meta_boxes( $post, $metabox ) {
		switch ( $metabox['id'] ) {
		case 'gb_merchant_meta':
			self::show_meta_box( $post, $metabox );
			break;
		default:
			self::unknown_meta_box( $metabox['id'] );
			break;
		}
	}

	private static function show_meta_box( $post, $metabox ) {
		$term = array_pop( wp_get_object_terms( $post->ID, self::TAX ) );
		$featured = FALSE;
		if ( !empty( $term ) && $term->slug = self::TERM ) {
			$featured = TRUE;
		}
		$merchant_id = $post->ID;
?>
			<table class="form-table">
				<tbody>
					<tr>
						<td>
							<label for="<?php echo self::META_FEATURED ?>"><input type="checkbox" id="<?php echo self::META_FEATURED ?>" name="<?php echo self::META_FEATURED ?>" <?php checked( $featured, TRUE ) ?> value="1"/> <?php gb_e( 'Featured Merchant' ) ?></label>
						</td>
					</tr>
					<tr>
						<td>
							<strong><?php gb_e( 'Documents' ) ?></strong>
							<ul>
								<li><?php echo wp_get_attachment_link( self::get_legal_docs( $merchant_id ), array( 100, 100 ), FALSE, TRUE ); ?><br/><?php echo wp_get_attachment_link( self::get_legal_docs( $merchant_id ) ); ?></li>
								<li><?php echo wp_get_attachment_link( self::get_financial_statement( $merchant_id ), array( 100, 100 ), FALSE, TRUE ); ?><br/><?php echo wp_get_attachment_link( self::get_financial_statement( $merchant_id ) ); ?></li>
								<li><?php echo wp_get_attachment_link( self::get_prev_financial_statement( $merchant_id ), array( 100, 100 ), FALSE, TRUE ); ?><br/><?php echo wp_get_attachment_link( self::get_prev_financial_statement( $merchant_id ) ); ?></li>
								<li><?php echo wp_get_attachment_link( self::get_annual_report( $merchant_id ), array( 100, 100 ), FALSE, TRUE ); ?><br/><?php echo wp_get_attachment_link( self::get_annual_report( $merchant_id ) ); ?></li>
								<li><?php echo wp_get_attachment_link( self::get_articles_corp( $merchant_id ), array( 100, 100 ), FALSE, TRUE ); ?><br/><?php echo wp_get_attachment_link( self::get_articles_corp( $merchant_id ) ); ?></li>
								<li><?php echo wp_get_attachment_link( self::get_board_members( $merchant_id ), array( 100, 100 ), FALSE, TRUE ); ?><br/><?php echo wp_get_attachment_link( self::get_board_members( $merchant_id ) ); ?></li>
								<li><?php echo wp_get_attachment_link( self::get_agreements( $merchant_id ), array( 100, 100 ), FALSE, TRUE ); ?><br/><?php echo wp_get_attachment_link( self::get_agreements( $merchant_id ) ); ?></li>
							</ul>
						</td>
					</tr>
				</tbody>
			</table>
		<?php
	}

	public static function save_meta_boxes( $post_id, $post ) {
		// only continue if it's an account post
		if ( $post->post_type != Group_Buying_Merchant::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined( 'DOING_AJAX' ) || isset( $_GET['bulk_edit'] ) ) {
			return;
		}
		self::save_meta_box( $post_id, $post );
	}

	private static function save_meta_box( $post_id, $post ) {
		$merchant = ( isset( $_POST[self::META_FEATURED] ) && $_POST[self::META_FEATURED] == '1' ) ? self::get_term_slug() : null;
		wp_set_object_terms( $post_id, $merchant, self::TAX );
		/*/
		$legal_docs = isset( $_POST[self::$meta_keys['legal_docs']] ) ? $_POST[self::$meta_keys['legal_docs']] : '';

		self::set_legal_docs( $post_id, $legal_docs );
		self::set_financial_statement( $post_id, $financial_statement );
		self::set_prev_financial_statement( $post_id, $prev_financial_statement );
		self::set_annual_report( $post_id, $annual_report );
		self::set_articles_corp( $post_id, $articles_corp );
		self::set_board_members( $post_id, $board_members );
		self::set_agreements( $post_id, $agreements );
		/**/
	}

	public static function get_legal_docs( $merchant_id ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$meta = $merchant->get_post_meta( self::$meta_keys['legal_docs'] );
		return $meta;
	}

	public static function set_legal_docs( $merchant_id, $meta ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$merchant->save_post_meta( array(
				self::$meta_keys['legal_docs'] => $meta
			) );
		return $meta;
	}

	public static function get_financial_statement( $merchant_id ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$meta = $merchant->get_post_meta( self::$meta_keys['financial_statement'] );
		return $meta;
	}

	public static function set_financial_statement( $merchant_id, $meta ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$merchant->save_post_meta( array(
				self::$meta_keys['financial_statement'] => $meta
			) );
		return $meta;
	}

	public static function get_prev_financial_statement( $merchant_id ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$meta = $merchant->get_post_meta( self::$meta_keys['prev_financial_statement'] );
		return $meta;
	}

	public static function set_prev_financial_statement( $merchant_id, $meta ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$merchant->save_post_meta( array(
				self::$meta_keys['prev_financial_statement'] => $meta
			) );
		return $meta;
	}

	public static function get_annual_report( $merchant_id ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$meta = $merchant->get_post_meta( self::$meta_keys['annual_report'] );
		return $meta;
	}

	public static function set_annual_report( $merchant_id, $meta ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$merchant->save_post_meta( array(
				self::$meta_keys['annual_report'] => $meta
			) );
		return $meta;
	}

	public static function get_articles_corp( $merchant_id ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$meta = $merchant->get_post_meta( self::$meta_keys['articles_corp'] );
		return $meta;
	}

	public static function set_articles_corp( $merchant_id, $meta ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$merchant->save_post_meta( array(
				self::$meta_keys['articles_corp'] => $meta
			) );
		return $meta;
	}

	public static function get_board_members( $merchant_id ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$meta = $merchant->get_post_meta( self::$meta_keys['board_members'] );
		return $meta;
	}

	public static function set_board_members( $merchant_id, $meta ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$merchant->save_post_meta( array(
				self::$meta_keys['board_members'] => $meta
			) );
		return $meta;
	}

	public static function get_agreements( $merchant_id ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$meta = $merchant->get_post_meta( self::$meta_keys['agreements'] );
		return $meta;
	}

	public static function set_agreements( $merchant_id, $meta ) {
		$merchant = Group_Buying_Merchant::get_instance( $merchant_id );
		$merchant->save_post_meta( array(
				self::$meta_keys['agreements'] => $meta
			) );
		return $meta;
	}

}


// Initiate the add-on
class Group_Buying_Merchant_Meta_Addon extends Group_Buying_Controller {

	public static function init() {
		// Hook this plugin into the GBS add-ons controller
		add_filter( 'gb_addons', array( get_class(), 'gb_merchant_meta_addon' ), 10, 1 );
	}

	public static function gb_merchant_meta_addon( $addons ) {
		$addons['merchant_meta'] = array(
			'label' => self::__( 'Merchant Meta' ),
			'description' => self::__( 'Add Meta Fields to the merchant post type.' ),
			'files' => array(
				__FILE__,
			),
			'callbacks' => array(
				array( 'Group_Buying_Merchant_Meta', 'init' ),
			),
		);
		return $addons;
	}

}