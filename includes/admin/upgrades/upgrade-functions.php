<?php
/**
 * Upgrade Functions
 *
 * @package     EDD
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Perform automatic database upgrades when necessary
 *
 * @since 2.6
 * @return void
 */
function edd_do_automatic_upgrades() {

	$did_upgrade = false;
	$edd_version = preg_replace( '/[^0-9.].*/', '', get_option( 'edd_version' ) );

	if ( version_compare( $edd_version, EDD_VERSION, '<' ) ) {

		// Let us know that an upgrade has happened
		$did_upgrade = true;

	}

	if ( $did_upgrade ) {

		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );

		// Send a check in. Note: this only sends if data tracking has been enabled
		$tracking = new EDD_Tracking;
		$tracking->send_checkin( false, true );
	}

	// 2.9.2 to 2.9.3
	$fix_show_privacy_policy_setting = edd_get_option( 'show_agree_to_privacy_policy_on_checkout', false );
	if ( ! empty( $fix_show_privacy_policy_setting ) ) {
		edd_update_option( 'show_privacy_policy_on_checkout', $fix_show_privacy_policy_setting );

		edd_delete_option( 'show_agree_to_privacy_policy_on_checkout' );
	}
}
add_action( 'admin_init', 'edd_do_automatic_upgrades' );

/**
 * Display Upgrade Notices
 *
 * @since 1.3.1
 * @return void
 */
function edd_show_upgrade_notices() {
	global $wpdb;

	// Don't show notices on the upgrades page
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'edd-upgrades' ) {
		return;
	}

	$edd_version = get_option( 'edd_version', '1.3' );
	$edd_version = preg_replace( '/[^0-9.].*/', '', $edd_version );

	if ( ! get_option( 'edd_payment_totals_upgraded' ) && ! get_option( 'edd_version' ) ) {
		if ( wp_count_posts( 'edd_payment' )->publish < 1 ) {
			return; // No payment exist yet
		}

		// The payment history needs updated for version 1.2
		$url            = add_query_arg( 'edd-action', 'upgrade_payments' );
		$upgrade_notice = sprintf( __( 'The Payment History needs to be updated. %s', 'easy-digital-downloads' ), '<a href="' . wp_nonce_url( $url, 'edd_upgrade_payments_nonce' ) . '">' . __( 'Click to Upgrade', 'easy-digital-downloads' ) . '</a>' );

		EDD()->notices->add_notice( array(
			'id'      => 'edd-payments-upgrade',
			'class'   => 'error',
			'message' => $upgrade_notice
		) );
	}

	if ( version_compare( $edd_version, '1.3.2', '<' ) && ! get_option( 'edd_logs_upgraded' ) ) {
		printf(
			'<div class="notice notice-warning"><p>' . esc_html__( 'The Purchase and File Download History in Easy Digital Downloads needs to be upgraded, click %shere%s to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if ( version_compare( $edd_version, '1.3.0', '<' ) || version_compare( $edd_version, '1.4', '<' ) ) {
		printf(
			'<div class="notice notice-warning"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the plugin pages, click %shere%s to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if ( version_compare( $edd_version, '1.5', '<' ) ) {
		printf(
			'<div class="notice notice-warning"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the database, click %shere%s to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	if ( version_compare( $edd_version, '2.0', '<' ) ) {
		printf(
			'<div class="notice notice-warning"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the database, click %shere%s to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
			'<a href="' . esc_url( admin_url( 'options.php?page=edd-upgrades' ) ) . '">',
			'</a>'
		);
	}

	// Sequential Orders was the first stepped upgrade, so check if we have a stalled upgrade
	$resume_upgrade = edd_maybe_resume_upgrade();
	if ( ! empty( $resume_upgrade ) ) {

		$resume_url = add_query_arg( $resume_upgrade, admin_url( 'index.php' ) );
		printf(
			'<div class="error"><p>' . __( 'Easy Digital Downloads needs to complete a database upgrade that was previously started, click <a href="%s">here</a> to resume the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
			esc_url( $resume_url )
		);

	} else {

		// Include all 'Stepped' upgrade process notices in this else statement,
		// to avoid having a pending, and new upgrade suggested at the same time

		if ( EDD()->session->get( 'upgrade_sequential' ) && edd_get_payments() ) {
			printf(
				'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade past order numbers to make them sequential, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
				admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_sequential_payment_numbers' )
			);
		}

		if ( version_compare( $edd_version, '2.1', '<' ) ) {
			printf(
				'<div class="notice notice-warning"><p>' . esc_html__( 'Easy Digital Downloads needs to upgrade the customer database, click %shere%s to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
				'<a href="' . esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_customers_db' ) ) . '">',
				'</a>'
			);
		}

		if ( version_compare( $edd_version, '2.2.6', '<' ) ) {
			printf(
				'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the payment database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_payments_price_logs_db' ) )
			);
		}

		if ( version_compare( $edd_version, '2.3', '<' ) || ! edd_has_upgrade_completed( 'upgrade_customer_payments_association' ) ) {
			printf(
				'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the customer database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_customer_payments_association' ) )
			);
		}

		if ( version_compare( $edd_version, '2.3', '<' ) || ! edd_has_upgrade_completed( 'upgrade_payment_taxes' ) ) {
			printf(
				'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the payment database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_payment_taxes' ) )
			);
		}

		if ( version_compare( $edd_version, '2.4', '<' ) || ! edd_has_upgrade_completed( 'upgrade_user_api_keys' ) ) {
			printf(
				'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the API Key database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=upgrade_user_api_keys' ) )
			);
		}

		if ( version_compare( $edd_version, '2.4.3', '<' ) || ! edd_has_upgrade_completed( 'remove_refunded_sale_logs' ) ) {
			printf(
				'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the payments database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=remove_refunded_sale_logs' ) )
			);
		}

		if ( version_compare( $edd_version, '2.9.2', '<' ) || ! edd_has_upgrade_completed( 'update_file_download_log_data' ) ) {
			printf(
				'<div class="notice notice-warning"><p>' . __( 'Easy Digital Downloads needs to upgrade the file download logs database, click <a href="%s">here</a> to start the upgrade.', 'easy-digital-downloads' ) . '</p></div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=update_file_download_log_data' ) )
			);
		}

		if ( ! edd_has_upgrade_completed( 'migrate_discounts' ) ) {
			// Check to see if we have discounts in the Database
			$results       = $wpdb->get_row( "SELECT count(ID) as has_discounts FROM $wpdb->posts WHERE post_type = 'edd_discount' LIMIT 0, 1" );
			$has_discounts = ! empty( $results->has_discounts ) ? true : false;

			if ( ! $has_discounts ) {
				edd_set_upgrade_complete( 'migrate_discounts' );
				edd_set_upgrade_complete( 'remove_legacy_discounts' );
			} else {
				printf(
					'<div class="notice notice-warning">' .
					'<p>' .
					__( 'Easy Digital Downloads needs to upgrade the discounts records database, click <a href="%1$s">here</a> to start the upgrade. <a href="#" onClick="%2$s">Learn more about this upgrade</a>.', 'easy-digital-downloads' ) .
					'</p>' .
					'<p style="display: none;">' .
					__( '<strong>About this upgrade:</strong><br />This is a <strong><em>mandatory</em></strong> update that will migrate all discounts records and their meta data to a new custom database table. This upgrade should provider better performance and scalability.', 'easy-digital-downloads' ) .
					'<br /><br />' .
					__( '<strong>Please backup your database before starting this upgrade.</strong> This upgrade routine will be making changes to the database that are not reversible.', 'easy-digital-downloads' ) .
					'<br /><br />' .
					__( '<strong>Advanced User?</strong><br />This upgrade can also be run via WPCLI with the following command:<br /><code>wp edd migrate_discounts</code>', 'easy-digital-downloads' ) .
					'</p>' .
					'</div>',
					esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=discounts_migration' ) ),
					"jQuery(this).parent().next('p').slideToggle()"
				);
			}
		}

		if ( edd_has_upgrade_completed( 'migrate_discounts' ) && ! edd_has_upgrade_completed( 'remove_legacy_discounts' ) ) {
			printf(
				'<div class="notice notice-warning">' .
				'<p>' .
				__( 'Easy Digital Downloads finished migrating <strong>discounts</strong>. Consider <a href="%1$s">removing the legacy data</a>. <a href="#" onClick="%2$s">Learn more</a>.', 'easy-digital-downloads' ) .
				'</p>' .
				'<p style="display: none;">' .
				__( 'All discounts were successfully migrated to their new homes.', 'easy-digital-downloads' ) .
				'<br />' .
				__( '<strong>Back up your database</strong> as this step <em>intentionally</em> destroys old copies of these items.', 'easy-digital-downloads' ) .
				'</p>' .
				'</div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=remove_legacy_discounts' ) ),
				"jQuery(this).parent().next('p').slideToggle()"
			);
		}

		if ( ! edd_has_upgrade_completed( 'migrate_logs' ) ) {
			// Check to see if we have logs in the Database
			$results  = $wpdb->get_row( "SELECT count(ID) as has_logs FROM $wpdb->posts WHERE post_type = 'edd_log' LIMIT 0, 1" );
			$has_logs = ! empty( $results->has_logs ) ? true : false;

			if ( ! $has_logs ) {
				edd_set_upgrade_complete( 'migrate_logs' );
				edd_set_upgrade_complete( 'remove_legacy_logs' );
			} else {
				printf(
					'<div class="notice notice-warning">' .
					'<p>' .
					__( 'Easy Digital Downloads needs to upgrade the logs records database, click <a href="%1$s">here</a> to start the upgrade. <a href="#" onClick="%2$s">Learn more about this upgrade</a>.', 'easy-digital-downloads' ) .
					'</p>' .
					'<p style="display: none;">' .
					__( '<strong>About this upgrade:</strong><br />This is a <strong><em>mandatory</em></strong> update that will migrate all logs records and their meta data to a new custom database table. This upgrade should provider better performance and scalability.', 'easy-digital-downloads' ) .
					'<br />' .
					__( '<strong>Please backup your database before starting this upgrade.</strong> This upgrade routine will be making changes to the database that are not reversible.', 'easy-digital-downloads' ) .
					'<br />' .
					__( '<strong>Advanced User?</strong><br />This upgrade can also be run via WPCLI with the following command:<br /><code>wp edd migrate_logs</code>', 'easy-digital-downloads' ) .
					'</p>' .
					'</div>',
					esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=logs_migration' ) ),
					"jQuery(this).parent().next('p').slideToggle()"
				);
			}
		}

		if ( edd_has_upgrade_completed( 'migrate_logs' ) && ! edd_has_upgrade_completed( 'remove_legacy_logs' ) ) {
			printf(
				'<div class="notice notice-warning">' .
				'<p>' .
				__( 'Easy Digital Downloads finished migrating <strong>logs</strong>. Consider <a href="%1$s">removing the legacy data</a>. <a href="#" onClick="%2$s">Learn more</a>.', 'easy-digital-downloads' ) .
				'</p>' .
				'<p style="display: none;">' .
				__( 'All logs were successfully migrated to their new homes.', 'easy-digital-downloads' ) .
				'<br />' .
				__( '<strong>Back up your database</strong> as this step <em>intentionally</em> destroys old copies of these items.', 'easy-digital-downloads' ) .
				'</p>' .
				'</div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=remove_legacy_logs' ) ),
				"jQuery(this).parent().next('p').slideToggle()"
			);
		}

		if ( ! edd_has_upgrade_completed( 'migrate_notes' ) ) {
			$results   = $wpdb->get_row( "SELECT count(comment_ID) as has_notes FROM $wpdb->comments WHERE comment_type = 'edd_payment_note' LIMIT 0, 1" );
			$has_notes = ! empty( $results->has_notes ) ? true : false;

			if ( ! $has_notes ) {
				edd_set_upgrade_complete( 'migrate_notes' );
				edd_set_upgrade_complete( 'remove_legacy_notes' );
			} else {
				printf(
					'<div class="notice notice-warning">' .
					'<p>' .
					__( 'Easy Digital Downloads needs to upgrade the notes table, click <a href="%1$s">here</a> to start the upgrade. <a href="#" onClick="%2$s">Learn more about this upgrade</a>.', 'easy-digital-downloads' ) .
					'</p>' .
					'<p style="display: none;">' .
					__( '<strong>About this upgrade:</strong><br />This is a <strong><em>mandatory</em></strong> update that will migrate all notes and their meta data to a new custom database table. This upgrade should provider better performance and scalability.', 'easy-digital-downloads' ) .
					'<br />' .
					__( '<strong>Please backup your database before starting this upgrade.</strong> This upgrade routine will be making changes to the database that are not reversible.', 'easy-digital-downloads' ) .
					'<br />' .
					__( '<strong>Advanced User?</strong><br />This upgrade can also be run via WPCLI with the following command:<br /><code>wp edd migrate_notes</code>', 'easy-digital-downloads' ) .
					'</p>' .
					'</div>',
					esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=notes_migration' ) ),
					"jQuery(this).parent().next('p').slideToggle()"
				);
			}
		}

		if ( edd_has_upgrade_completed( 'migrate_notes' ) && ! edd_has_upgrade_completed( 'remove_legacy_notes' ) ) {
			printf(
				'<div class="notice notice-warning">' .
				'<p>' .
				__( 'Easy Digital Downloads finished migrating <strong>notes</strong>. Consider <a href="%1$s">removing the legacy data</a>. <a href="#" onClick="%2$s">Learn more</a>.', 'easy-digital-downloads' ) .
				'</p>' .
				'<p style="display: none;">' .
				__( 'All notes were successfully migrated to their new homes.', 'easy-digital-downloads' ) .
				'<br />' .
				__( '<strong>Back up your database</strong> as this step <em>intentionally</em> destroys old copies of these items.', 'easy-digital-downloads' ) .
				'</p>' .
				'</div>',
				esc_url( admin_url( 'index.php?page=edd-upgrades&edd-upgrade=remove_legacy_notes' ) ),
				"jQuery(this).parent().next('p').slideToggle()"
			);
		}

		/*
		 *  NOTICE:
		 *
		 *  When adding new upgrade notices, please be sure to put the action into the upgrades array during install:
		 *  /includes/install.php @ Appox Line 156
		 *
		 */

		// End 'Stepped' upgrade process notices
	}
}
add_action( 'admin_notices', 'edd_show_upgrade_notices' );

/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via AJAX
 *
 * @since 1.3.1
 * @return void
 */
function edd_trigger_upgrades() {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$edd_version = get_option( 'edd_version' );

	if ( ! $edd_version ) {
		// 1.3 is the first version to use this option so we must add it
		$edd_version = '1.3';
		add_option( 'edd_version', $edd_version );
	}

	if ( version_compare( EDD_VERSION, $edd_version, '>' ) ) {
		edd_v131_upgrades();
	}

	if ( version_compare( $edd_version, '1.3.0', '<' ) ) {
		edd_v134_upgrades();
	}

	if ( version_compare( $edd_version, '1.4', '<' ) ) {
		edd_v14_upgrades();
	}

	if ( version_compare( $edd_version, '1.5', '<' ) ) {
		edd_v15_upgrades();
	}

	if ( version_compare( $edd_version, '2.0', '<' ) ) {
		edd_v20_upgrades();
	}

	update_option( 'edd_version', EDD_VERSION );

	// Let AJAX know that the upgrade is complete
	if ( edd_doing_ajax() ) {
		die( 'complete' );
	}
}
add_action( 'wp_ajax_edd_trigger_upgrades', 'edd_trigger_upgrades' );

/**
 * For use when doing 'stepped' upgrade routines, to see if we need to start somewhere in the middle
 * @since 2.2.6
 * @return mixed   When nothing to resume returns false, otherwise starts the upgrade where it left off
 */
function edd_maybe_resume_upgrade() {

	$doing_upgrade = get_option( 'edd_doing_upgrade', false );

	if ( empty( $doing_upgrade ) ) {
		return false;
	}

	return $doing_upgrade;
}

/**
 * Adds an upgrade action to the completed upgrades array
 *
 * @since  2.3
 * @param  string $upgrade_action The action to add to the copmleted upgrades array
 * @return bool                   If the function was successfully added
 */
function edd_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = edd_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'edd_completed_upgrades', $completed_upgrades );
}

/**
 * Converts old sale and file download logs to new logging system
 *
 * @since 1.3.1
 * @uses WP_Query
 * @uses EDD_Logging
 * @return void
 */
function edd_v131_upgrades() {
	if ( get_option( 'edd_logs_upgraded' ) ) {
		return;
	}

	if ( version_compare( get_option( 'edd_version' ), '1.3', '>=' ) ) {
		return;
	}

	edd_set_time_limit();

	$query = new WP_Query( array(
		'post_type' 		=> 'download',
		'posts_per_page' 	=> -1,
		'post_status' 		=> 'publish'
	) );
	$downloads = $query->get_posts();

	if ( $downloads ) {
		$edd_log = new EDD_Logging();
		foreach ( $downloads as $download ) {
			// Convert sale logs
			$sale_logs = edd_get_download_sales_log( $download->ID, false );

			if ( $sale_logs ) {
				foreach ( $sale_logs['sales'] as $sale ) {
					$log_data = array(
						'post_parent'	=> $download->ID,
						'post_date'		=> $sale['date'],
						'log_type'		=> 'sale'
					);

					$log_meta = array(
						'payment_id'=> $sale['payment_id']
					);

					$log = $edd_log->insert_log( $log_data, $log_meta );
				}
			}

			// Convert file download logs
			$file_logs = edd_get_file_download_log( $download->ID, false );

			if ( $file_logs ) {
				foreach ( $file_logs['downloads'] as $log ) {
					$log_data = array(
						'post_parent'	=> $download->ID,
						'post_date'		=> $log['date'],
						'log_type'		=> 'file_download'

					);

					$log_meta = array(
						'user_info'	=> $log['user_info'],
						'file_id'	=> $log['file_id'],
						'ip'		=> $log['ip']
					);

					$log = $edd_log->insert_log( $log_data, $log_meta );
				}
			}
		}
	}
	add_option( 'edd_logs_upgraded', '1' );
}

/**
 * Upgrade routine for v1.3.0
 *
 * @since 1.3.0
 * @return void
 */
function edd_v134_upgrades() {
	$general_options = get_option( 'edd_settings_general' );

	// Settings already updated
	if ( isset( $general_options['failure_page'] ) ) {
		return;
	}

	// Failed Purchase Page
	$failed = wp_insert_post(
		array(
			'post_title'     => __( 'Transaction Failed', 'easy-digital-downloads' ),
			'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'easy-digital-downloads' ),
			'post_status'    => 'publish',
			'post_author'    => 1,
			'post_type'      => 'page',
			'post_parent'    => $general_options['purchase_page'],
			'comment_status' => 'closed'
		)
	);

	$general_options['failure_page'] = $failed;

	update_option( 'edd_settings_general', $general_options );
}

/**
 * Upgrade routine for v1.4
 *
 * @since 1.4
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_v14_upgrades() {

	/** Add [edd_receipt] to success page **/
	$success_page = get_post( edd_get_option( 'success_page' ) );

	// Check for the [edd_receipt] shortcode and add it if not present
	if ( strpos( $success_page->post_content, '[edd_receipt' ) === false ) {
		$page_content = $success_page->post_content .= "\n[edd_receipt]";
		wp_update_post( array( 'ID' => edd_get_option( 'success_page' ), 'post_content' => $page_content ) );
	}

	/** Convert Discounts to new Custom Post Type **/
	$discounts = get_option( 'edd_discounts' );

	if ( $discounts ) {
		foreach ( $discounts as $discount ) {

			$discount_id = wp_insert_post( array(
				'post_type'   => 'edd_discount',
				'post_title'  => isset( $discount['name'] ) ? $discount['name'] : '',
				'post_status' => 'active'
			) );

			$meta = array(
				'code'        => isset( $discount['code'] ) ? $discount['code'] : '',
				'uses'        => isset( $discount['uses'] ) ? $discount['uses'] : '',
				'max_uses'    => isset( $discount['max'] ) ? $discount['max'] : '',
				'amount'      => isset( $discount['amount'] ) ? $discount['amount'] : '',
				'start'       => isset( $discount['start'] ) ? $discount['start'] : '',
				'expiration'  => isset( $discount['expiration'] ) ? $discount['expiration'] : '',
				'type'        => isset( $discount['type'] ) ? $discount['type'] : '',
				'min_price'   => isset( $discount['min_price'] ) ? $discount['min_price'] : ''
			);

			foreach ( $meta as $meta_key => $value ) {
				update_post_meta( $discount_id, '_edd_discount_' . $meta_key, $value );
			}
		}

		// Remove old discounts from database
		delete_option( 'edd_discounts' );
	}
}


/**
 * Upgrade routine for v1.5
 *
 * @since 1.5
 * @return void
 */
function edd_v15_upgrades() {
	// Update options for missing tax settings
	$tax_options = get_option( 'edd_settings_taxes' );

	// Set include tax on checkout to off
	$tax_options['checkout_include_tax'] = 'no';

	// Check if prices are displayed with taxes
	$tax_options['prices_include_tax'] = isset( $tax_options['taxes_on_prices'] )
		? 'yes'
		: 'no';

	update_option( 'edd_settings_taxes', $tax_options );

	// Flush the rewrite rules for the new /edd-api/ end point
	flush_rewrite_rules( false );
}

/**
 * Upgrades for EDD v2.0
 *
 * @since 2.0
 * @return void
 */
function edd_v20_upgrades() {
	global $edd_options, $wpdb;

	edd_set_time_limit();

	// Upgrade for the anti-behavior fix - #2188
	if ( ! empty( $edd_options['disable_ajax_cart'] ) ) {
		unset( $edd_options['enable_ajax_cart'] );
	} else {
		$edd_options['enable_ajax_cart'] = '1';
	}

	// Upgrade for the anti-behavior fix - #2188
	if ( ! empty( $edd_options['disable_cart_saving'] ) ) {
		unset( $edd_options['enable_cart_saving'] );
	} else {
		$edd_options['enable_cart_saving'] = '1';
	}

	// Properly set the register / login form options based on whether they were enabled previously - #2076
	if ( ! empty( $edd_options['show_register_form'] ) ) {
		$edd_options['show_register_form'] = 'both';
	} else {
		$edd_options['show_register_form'] = 'none';
	}

	// Remove all old, improperly expired sessions. See https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/2031
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_wp_session_expires_%' AND option_value+0 < 2789308218" );

	update_option( 'edd_settings', $edd_options );
}

/**
 * Upgrades for EDD v2.0 and sequential payment numbers
 *
 * @since 2.0
 * @return void
 */
function edd_v20_upgrade_sequential_payment_numbers() {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] )  ? absint( $_GET['step'] )  : 1;
	$total  = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$payments = edd_count_payments();
		foreach ( $payments as $status ) {
			$total += $status;
		}
	}

	$payments = new EDD_Payments_Query( array(
		'number' => 100,
		'page'   => $step,
		'status' => 'any',
		'order'  => 'ASC'
	) );
	$payments = $payments->get_payments();

	if ( $payments ) {

		$prefix  = edd_get_option( 'sequential_prefix' );
		$postfix = edd_get_option( 'sequential_postfix' );
		$number  = ! empty( $_GET['custom'] ) ? absint( $_GET['custom'] ) : intval( edd_get_option( 'sequential_start', 1 ) );

		foreach ( $payments as $payment ) {

			// Re-add the prefix and postfix
			$payment_number = $prefix . $number . $postfix;

			edd_update_payment_meta( $payment->ID, '_edd_payment_number', $payment_number );

			// Increment the payment number
			$number++;
		}

		// Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_sequential_payment_numbers',
			'step'        => $step,
			'custom'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more payments found, finish up
	} else {
		EDD()->session->set( 'upgrade_sequential', null );
		delete_option( 'edd_doing_upgrade' );

		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_sequential_payment_numbers', 'edd_v20_upgrade_sequential_payment_numbers' );

/**
 * Upgrades for EDD v2.1 and the new customers database
 *
 * @since 2.1
 * @return void
 */
function edd_v21_upgrade_customers_db() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}


	edd_set_time_limit();

	if ( ! get_option( 'edd_upgrade_customers_db_version' ) ) {
		// Create the customers database on the first run
		@EDD()->customers->create_table();
	}

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 20;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	$emails = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_user_email' LIMIT %d,%d;", $offset, $number ) );

	if ( $emails ) {

		foreach ( $emails as $email ) {

			if ( EDD()->customers->exists( $email ) ) {
				continue; // Allow the upgrade routine to be safely re-run in the case of failure
			}

			$payments = new EDD_Payments_Query( array(
				'user'    => $email,
				'order'   => 'ASC',
				'orderby' => 'ID',
				'number'  => -1,
				'page'    => $step
			) );
			$payments = $payments->get_payments();

			if ( $payments ) {

				$total_value = 0.00;
				$total_count = 0;

				foreach ( $payments as $payment ) {

					$status = get_post_status( $payment->ID );
					if ( 'revoked' == $status || 'publish' == $status ) {

						$total_value += $payment->total;
						$total_count += 1;

					}

				}

				$ids  = wp_list_pluck( $payments, 'ID' );

				$user = get_user_by( 'email', $email );

				$args = array(
					'email'          => $email,
					'user_id'        => $user ? $user->ID : 0,
					'name'           => $user ? $user->display_name : '',
					'purchase_count' => $total_count,
					'purchase_value' => round( $total_value, 2 ),
					'payment_ids'    => implode( ',', array_map( 'absint', $ids ) ),
					'date_created'   => $payments[0]->date
				);

				$customer_id = EDD()->customers->add( $args );

				foreach ( $ids as $id ) {
					update_post_meta( $id, '_edd_payment_customer_id', $customer_id );
				}
			}
		}

		// Customers found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_customers_db',
			'step'        => $step
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more customers found, finish up
	} else {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		delete_option( 'edd_doing_upgrade' );

		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_customers_db', 'edd_v21_upgrade_customers_db' );

/**
 * Fixes the edd_log meta for 2.2.6
 *
 * @since 2.2.6
 * @return void
 */
function edd_v226_upgrade_payments_price_logs_db() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 25;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;
	if ( 1 === $step ) {
		// Check if we have any variable price products on the first step
		$sql = "SELECT ID FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id WHERE m.meta_key = '_variable_pricing' AND m.meta_value = 1 LIMIT 1";
		$has_variable = $wpdb->get_col( $sql );
		if ( empty( $has_variable ) ) {
			// We had no variable priced products, so go ahead and just complete
			update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$payment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_payment' ORDER BY post_date DESC LIMIT %d,%d;", $offset, $number ) );
	if ( ! empty( $payment_ids ) ) {
		foreach ( $payment_ids as $payment_id ) {
			$payment_downloads  = edd_get_payment_meta_downloads( $payment_id );
			$variable_downloads = array();

			// May not be an array due to some very old payments, move along
			if ( ! is_array( $payment_downloads ) ) {
				continue;
			}

			foreach ( $payment_downloads as $download ) {
				// Don't care if the download is a single price id
				if ( ! isset( $download['options']['price_id'] ) ) {
					continue;
				}
				$variable_downloads[] = array( 'id' => $download['id'], 'price_id' => $download['options']['price_id'] );
			}
			$variable_download_ids = array_unique( wp_list_pluck( $variable_downloads, 'id' ) );
			$unique_download_ids   = implode( ',', $variable_download_ids );

			// If there were no downloads, just fees, move along
			if ( empty( $unique_download_ids ) ) {
				continue;
			}

			// Get all Log Ids where the post parent is in the set of download IDs we found in the cart meta
			$logs = $wpdb->get_results( "SELECT m.post_id AS log_id, p.post_parent AS download_id FROM $wpdb->postmeta m LEFT JOIN $wpdb->posts p ON m.post_id = p.ID WHERE meta_key = '_edd_log_payment_id' AND meta_value = $payment_id AND p.post_parent IN ($unique_download_ids)", ARRAY_A );
			$mapped_logs = array();

			// Go through each cart item
			foreach ( $variable_downloads as $cart_item ) {
				// Itterate through the logs we found attached to this payment
				foreach ( $logs as $key => $log ) {
					// If this Log ID is associated with this download ID give it the price_id
					if ( (int) $log['download_id'] === (int) $cart_item['id'] ) {
						$mapped_logs[$log['log_id']] = $cart_item['price_id'];
						// Remove this Download/Log ID from the list, for multipurchase compatibility
						unset( $logs[$key] );
						// These aren't the logs we're looking for. Move Along, Move Along.
						break;
					}
				}
			}

			if ( ! empty( $mapped_logs ) ) {
				$update  = "UPDATE $wpdb->postmeta SET meta_value = ";
				$case    = "CASE post_id ";
				foreach ( $mapped_logs as $post_id => $value ) {
					$case .= "WHEN {$post_id} THEN {$value} ";
				}
				$case   .= "END ";
				$log_ids = implode( ',', array_keys( $mapped_logs ) );
				$where   = "WHERE post_id IN ({$log_ids}) AND meta_key = '_edd_log_price_id'";
				$sql     = $update . $case . $where;

				// Execute our query to update this payment
				$wpdb->query( $sql );
			}
		}
		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_payments_price_logs_db',
			'step'        => $step
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );
	} else {
		// No more payments found, finish up
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_payments_price_logs_db', 'edd_v226_upgrade_payments_price_logs_db' );

/**
 * Upgrades payment taxes for 2.3
 *
 * @since 2.3
 * @return void
 */
function edd_v23_upgrade_payment_taxes() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 50;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any payments before moving on
		$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_payment' LIMIT 1";
		$has_payments = $wpdb->get_col( $sql );

		if ( empty( $has_payments ) ) {
			// We had no payments, just complete
			update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
			edd_set_upgrade_complete( 'upgrade_payment_taxes' );
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_payments FROM $wpdb->posts WHERE post_type = 'edd_payment'";
		$results   = $wpdb->get_row( $total_sql, 0 );

		$total     = $results->total_payments;
	}

	$payment_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_payment' ORDER BY post_date DESC LIMIT %d,%d;", $offset, $number ) );

	if ( $payment_ids ) {

		// Add the new _edd_payment_meta item
		foreach ( $payment_ids as $payment_id ) {
			$payment_tax = edd_get_payment_tax( $payment_id );
			edd_update_payment_meta( $payment_id, '_edd_payment_tax', $payment_tax );
		}

		// Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_payment_taxes',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more payments found, finish up
	} else {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		edd_set_upgrade_complete( 'upgrade_payment_taxes' );
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_payment_taxes', 'edd_v23_upgrade_payment_taxes' );

/**
 * Run the upgrade for the customers to find all payment attachments
 *
 * @since  2.3
 * @return void
 */
function edd_v23_upgrade_customer_purchases() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 50;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any payments before moving on
		$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_payment' LIMIT 1";
		$has_payments = $wpdb->get_col( $sql );

		if ( empty( $has_payments ) ) {
			// We had no payments, just complete
			update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
			edd_set_upgrade_complete( 'upgrade_customer_payments_association' );
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total = EDD()->customers->count();
	}

	$customers = edd_get_customers( array( 'number' => $number, 'offset' => $offset ) );

	if ( ! empty( $customers ) ) {

		foreach ( $customers as $customer ) {

			// Get payments by email and user ID
			$select = "SELECT ID FROM $wpdb->posts p ";
			$join   = "LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id ";
			$where  = "WHERE p.post_type = 'edd_payment' ";

			if ( ! empty( $customer->user_id ) && intval( $customer->user_id ) > 0 ) {
				$where .= "AND ( ( m.meta_key = '_edd_payment_user_email' AND m.meta_value = '$customer->email' ) OR ( m.meta_key = '_edd_payment_customer_id' AND m.meta_value = '$customer->id' ) OR ( m.meta_key = '_edd_payment_user_id' AND m.meta_value = '$customer->user_id' ) )";
			} else {
				$where .= "AND ( ( m.meta_key = '_edd_payment_user_email' AND m.meta_value = '$customer->email' ) OR ( m.meta_key = '_edd_payment_customer_id' AND m.meta_value = '$customer->id' ) ) ";
			}

			$sql            = $select . $join . $where;
			$found_payments = $wpdb->get_col( $sql );

			$unique_payment_ids  = array_unique( array_filter( $found_payments ) );

			if ( ! empty( $unique_payment_ids ) ) {

				$unique_ids_string = implode( ',', $unique_payment_ids );

				$customer_data = array( 'payment_ids' => $unique_ids_string );

				$purchase_value_sql = "SELECT SUM( m.meta_value ) FROM $wpdb->postmeta m LEFT JOIN $wpdb->posts p ON m.post_id = p.ID WHERE m.post_id IN ( $unique_ids_string ) AND p.post_status IN ( 'publish', 'revoked' ) AND m.meta_key = '_edd_payment_total'";
				$purchase_value     = $wpdb->get_col( $purchase_value_sql );

				$purchase_count_sql = "SELECT COUNT( m.post_id ) FROM $wpdb->postmeta m LEFT JOIN $wpdb->posts p ON m.post_id = p.ID WHERE m.post_id IN ( $unique_ids_string ) AND p.post_status IN ( 'publish', 'revoked' ) AND m.meta_key = '_edd_payment_total'";
				$purchase_count     = $wpdb->get_col( $purchase_count_sql );

				if ( ! empty( $purchase_value ) && ! empty( $purchase_count ) ) {

					$purchase_value = $purchase_value[0];
					$purchase_count = $purchase_count[0];

					$customer_data['purchase_count'] = $purchase_count;
					$customer_data['purchase_value'] = $purchase_value;
				}

			} else {
				$customer_data['purchase_count'] = 0;
				$customer_data['purchase_value'] = 0;
				$customer_data['payment_ids']    = '';
			}

			if ( ! empty( $customer_data ) ) {
				$customer = new EDD_Customer( $customer->id );
				$customer->update( $customer_data );
			}
		}

		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_customer_payments_association',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );
		edd_redirect( $redirect );

	// No more customers found, finish up
	} else {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		edd_set_upgrade_complete( 'upgrade_customer_payments_association' );
		delete_option( 'edd_doing_upgrade' );

		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_customer_payments_association', 'edd_v23_upgrade_customer_purchases' );

/**
 * Upgrade the Usermeta API Key storage to swap keys/values for performance
 *
 * @since  2.4
 * @return void
 */
function edd_upgrade_user_api_keys() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$number = 10;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	if ( $step < 2 ) {
		// Check if we have any users with API Keys before moving on
		$sql     = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key' LIMIT 1";
		$has_key = $wpdb->get_col( $sql );

		if ( empty( $has_key ) ) {
			// We had no key, just complete
			update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
			edd_set_upgrade_complete( 'upgrade_user_api_keys' );
			delete_option( 'edd_doing_upgrade' );
			edd_redirect( admin_url() );
		}
	}

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total = $wpdb->get_var( "SELECT count(user_id) FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key'" );
	}

	$keys_sql   = $wpdb->prepare( "SELECT user_id, meta_key, meta_value FROM $wpdb->usermeta WHERE meta_key = 'edd_user_public_key' OR meta_key = 'edd_user_secret_key' ORDER BY user_id ASC LIMIT %d,%d;", $offset, $number );
	$found_keys = $wpdb->get_results( $keys_sql );

	if ( ! empty( $found_keys ) ) {
		foreach ( $found_keys as $key ) {
			$user_id    = $key->user_id;
			$meta_key   = $key->meta_key;
			$meta_value = $key->meta_value;

			// Generate a new entry
			update_user_meta( $user_id, $meta_value, $meta_key );

			// Delete the old one
			delete_user_meta( $user_id, $meta_key );
		}

		// More Payments found so upgrade them
		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'upgrade_user_api_keys',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more customers found, finish up
	} else {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		edd_set_upgrade_complete( 'upgrade_user_api_keys' );
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}
add_action( 'edd_upgrade_user_api_keys', 'edd_upgrade_user_api_keys' );

/**
 * Remove sale logs from refunded orders
 *
 * @since  2.4.3
 * @return void
 */
function edd_remove_refunded_sale_logs() {
	global $edd_logs;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$step    = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$total   = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : edd_count_payments()->refunded;
	$refunds = edd_get_payments( array( 'status' => 'refunded', 'number' => 20, 'page' => $step ) );

	if ( ! empty( $refunds ) ) {

		// Refunded Payments found so process them

		foreach ( $refunds as $refund ) {

			if ( 'refunded' !== $refund->post_status ) {
				continue; // Just to be safe
			}

			// Remove related sale log entries
			$edd_logs->delete_logs(
				null,
				'sale',
				array(
					array(
						'key'   => '_edd_log_payment_id',
						'value' => $refund->ID
					)
				)
			);
		}

		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'remove_refunded_sale_logs',
			'step'        => $step,
			'total'       => $total
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more refunded payments found, finish up
	} else {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		edd_set_upgrade_complete( 'remove_refunded_sale_logs' );
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}
add_action( 'edd_remove_refunded_sale_logs', 'edd_remove_refunded_sale_logs' );

/**
 * Migrates all discounts and their meta to the new custom table
 *
 * @since 3.0
 * @return void
 */
function edd_discounts_migration() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] )   ? absint( $_GET['step'] )   : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 10;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	edd_debug_log( 'Beginning step ' . $step . ' of discounts migration' );

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_discounts FROM $wpdb->posts WHERE post_type = 'edd_discount'";
		$results   = $wpdb->get_row( $total_sql, 0 );
		$total     = $results->total_discounts;
		edd_debug_log( $total . ' to migrate' );
	}

	if ( 1 === $step ) {
		$discounts = edd_get_component_interface( 'discount', 'table' );
		if ( ! $discounts->exists() ) {
			$discounts->create();
			edd_debug_log( $discounts->table_name . ' created successfully' );
		}

		$discount_meta = edd_get_component_interface( 'discount', 'meta' );
		if ( ! $discount_meta->exists() ) {
			$discount_meta->create();
			edd_debug_log( $discount_meta->table_name . ' created successfully' );
		}
	}

	$discounts = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $wpdb->posts WHERE post_type = 'edd_discount' ORDER BY ID ASC LIMIT %d,%d;",
			$offset,
			$number
		)
	);

	if ( ! empty( $discounts ) ) {

		// Discounts found so migrate them
		foreach ( $discounts as $old_discount ) {
			$old_discount = get_post( $old_discount->ID );

			if ( 'edd_discount' !== $old_discount->post_type ) {
				continue;
			}

			$args = array();
			$meta = get_post_custom( $old_discount->ID );
			$meta_to_migrate = array();

			foreach ( $meta as $key => $value ) {
				if ( false === strpos( $key, '_edd_discount' ) ) {
					// This is custom meta from another plugin that needs to be migrated to the new meta table
					$meta_to_migrate[ $key ] = maybe_unserialize( $value[0] );
					continue;
				}

				$value = maybe_unserialize( $value[0] );
				$args[ str_replace( '_edd_discount_', '', $key ) ] = $value;
			}

			// If the discount name was not stored in post_meta, use value from the WP_Post object
			if ( ! isset( $args['name'] ) ) {
				$args['name'] = $old_discount->post_title;
			}

			$args['status'] = get_post_status( $old_discount->ID );

			$discount_id = edd_store_discount( $args );

			if ( ! empty( $meta_to_migrate ) ) {
				foreach ( $meta_to_migrate as $key => $value ) {
					edd_add_discount_meta( $discount_id, $key, $value );
				}
			}

			edd_debug_log( $old_discount->ID . ' successfully migrated to ' . $discount_id );
		}

		edd_debug_log( 'Step ' . $step . ' of discounts migration complete' );

		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'discounts_migration',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more discounts found, finish up
	} else {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		edd_set_upgrade_complete( 'migrate_discounts' );
		delete_option( 'edd_doing_upgrade' );

		edd_debug_log( 'All old discounts migrated, upgrade complete.' );

		edd_redirect( admin_url() );
	}
}
add_action( 'edd_discounts_migration', 'edd_discounts_migration' );

/**
 * Removes legacy discount date
 *
 * @since 3.0
 * @return void
 */
function edd_remove_legacy_discounts() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	edd_set_time_limit();

	$discount_ids = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'edd_discount'" );
	$discount_ids = wp_list_pluck( $discount_ids, 'ID' );
	$discount_ids = implode( ', ', $discount_ids );

	edd_debug_log( 'Beginning removal of legacy discounts' );

	if ( ! empty( $discount_ids ) ) {
		$delete_posts_query = "DELETE FROM $wpdb->posts WHERE ID IN ({$discount_ids})";
		$wpdb->query( $delete_posts_query );

		$delete_postmeta_query = "DELETE FROM $wpdb->postmeta WHERE post_id IN ({$discount_ids})";
		$wpdb->query( $delete_postmeta_query );
	}

	// No more discounts found, finish up.
	edd_set_upgrade_complete( 'remove_legacy_discounts' );

	delete_option( 'edd_doing_upgrade' );

	edd_debug_log( 'Legacy discounts removed, upgrade complete.' );

	edd_redirect( admin_url() );
}
add_action( 'edd_remove_legacy_discounts', 'edd_remove_legacy_discounts' );

/**
 * Migrates all notes and their meta to the new custom table.
 *
 * @since 3.0
 */
function edd_notes_migration() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] )   ? absint( $_GET['step'] )   : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 10;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	edd_debug_log( 'Beginning step ' . $step . ' of notes migration' );

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;
	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(comment_ID) as total_notes FROM $wpdb->comments WHERE comment_type = 'edd_payment_note'";
		$results   = $wpdb->get_row( $total_sql, 0 );
		$total     = $results->total_notes;
		edd_debug_log( $total . ' to migrate' );
	}

	if ( 1 === $step ) {
		$notes_db = edd_get_component_interface( 'note', 'table' );
		if ( ! $notes_db->exists() ) {
			@$notes_db->create();
		}

		$note_meta_db = edd_get_component_interface( 'note', 'meta' );
		if ( ! $note_meta_db->exists() ) {
			@$note_meta_db->create();
		}
	}

	$notes = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT *
			FROM {$wpdb->comments}
			WHERE comment_type = 'edd_payment_note'
			ORDER BY comment_ID ASC
			LIMIT %d,%d;
			",
			$offset,
			$number
		)
	);

	if ( ! empty( $notes ) ) {
		foreach ( $notes as $old_note ) {
			$note_data = array(
				'object_id'    => $old_note->comment_post_ID,
				'object_type'  => 'payment',
				'date_created' => $old_note->comment_date,
				'content'      => $old_note->comment_content,
				'user_id'      => $old_note->user_id,
			);

			$id = edd_add_note( $note_data );
			$note = new EDD\Notes\Note( $id );

			$meta = get_comment_meta( $old_note->comment_ID );
			if ( ! empty( $meta ) ) {
				foreach ( $meta as $key => $value ) {
					$note->add_meta( $key, $value );
				}
			}

			edd_debug_log( $old_note->comment_ID . ' successfully migrated to ' . $id );
		}

		edd_debug_log( 'Step ' . $step . ' of notes migration complete' );

		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'notes_migration',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	// No more notes found, finish up
	} else {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		edd_set_upgrade_complete( 'migrate_notes' );
		delete_option( 'edd_doing_upgrade' );

		edd_debug_log( 'All old notes migrated, upgrade complete.' );

		edd_redirect( admin_url() );
	}
}
add_action( 'edd_notes_migration', 'edd_notes_migration' );

/**
 * Removes legacy notes data.
 *
 * @since 3.0
 */
function edd_remove_legacy_notes() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	edd_set_time_limit();

	$note_ids = $wpdb->get_results( "SELECT comment_ID FROM $wpdb->comments WHERE comment_type = 'edd_payment_note'" );
	$note_ids = wp_list_pluck( $note_ids, 'comment_ID' );
	$note_ids = implode( ', ', $note_ids );

	edd_debug_log( 'Beginning removal of legacy notes' );

	if ( ! empty( $note_ids ) ) {
		$delete_query = "DELETE FROM $wpdb->comments WHERE comment_ID IN ({$note_ids})";
		$wpdb->query( $delete_query );

		$delete_postmeta_query = "DELETE FROM $wpdb->commentmeta WHERE comment_id IN ({$note_ids})";
		$wpdb->query( $delete_postmeta_query );
	}

	edd_set_upgrade_complete( 'remove_legacy_notes' );

	delete_option( 'edd_doing_upgrade' );

	edd_debug_log( 'Legacy notes removed, upgrade complete.' );

	edd_redirect( admin_url() );
}
add_action( 'edd_remove_legacy_notes', 'edd_remove_legacy_notes' );

/**
 * Migrates all logs and log meta to the new custom tables.
 *
 * @since 3.0
 */
function edd_logs_migration() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] )   ? absint( $_GET['step'] )   : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 10;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	$logs_db = edd_get_component_interface( 'log', 'table' );
	if ( ! $logs_db->exists() ) {
		@$logs_db->create();
	}

	$log_meta_db = edd_get_component_interface( 'log', 'meta' );
	if ( ! $log_meta_db->exists() ) {
		@$log_meta_db->create();
	}

	$log_api_request_db = edd_get_component_interface( 'log_api_request', 'table' );
	if ( ! $log_api_request_db->exists() ) {
		@$log_api_request_db->create();
	}

	$log_file_download_db = edd_get_component_interface( 'log_file_download', 'table' );
	if ( ! $log_file_download_db->exists() ) {
		@$log_file_download_db->create();
	}

	edd_debug_log( 'Beginning step ' . $step . ' of logs migration' );

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_logs FROM $wpdb->posts WHERE post_type = 'edd_log'";
		$results   = $wpdb->get_row( $total_sql, 0 );
		$total     = $results->total_logs;
		edd_debug_log( $total . ' to migrate' );
	}

	$logs = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT p.*, t.slug
			FROM {$wpdb->posts} AS p
			LEFT JOIN {$wpdb->term_relationships} AS tr ON (p.ID = tr.object_id)
			LEFT JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id)
			LEFT JOIN {$wpdb->terms} AS t ON (tt.term_id = t.term_id)
			WHERE p.post_type = 'edd_log' AND t.slug != 'sale'
			GROUP BY p.ID
			LIMIT %d,%d;
			",
			$offset, $number
		)
	);

	if ( ! empty( $logs ) ) {
		foreach ( $logs as $old_log ) {
            $new_log_id = 0;

            if ( 'file_download' === $old_log->slug ) {
                $meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $old_log->ID ) );

                $post_meta = array();

                foreach ( $meta as $meta_item ) {
                    $post_meta[ $meta_item->meta_key ] = maybe_unserialize( $meta_item->meta_value );
                }

                $log_data = array(
                    'download_id'   => $old_log->post_parent,
                    'file_id'       => $post_meta['_edd_log_file_id'],
                    'payment_id'    => $post_meta['_edd_log_payment_id'],
                    'price_id'      => isset( $post_meta['_edd_log_price_id'] ) ? $post_meta['_edd_log_price_id'] : 0,
                    'user_id'       => isset( $post_meta['_edd_log_user_id'] ) ? $post_meta['_edd_log_user_id'] : 0,
                    'ip'            => $post_meta['_edd_log_ip'],
                    'date_created'  => $old_log->post_date_gmt,
                    'date_modified' => $old_log->post_modified_gmt,
                );

                $new_log_id = edd_add_file_download_log( $log_data );
            } elseif ( 'api_request' === $old_log->slug ) {
                $meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d", $old_log->ID ) );

                $post_meta = array();

                foreach ( $meta as $meta_item ) {
                    $post_meta[ $meta_item->meta_key ] = maybe_unserialize( $meta_item->meta_value );
                }

                $log_data = array(
                    'ip'            => $post_meta['_edd_log_request_ip'],
                    'user_id'       => isset( $post_meta['_edd_log_user'] ) ? $post_meta['_edd_log_user'] : 0,
                    'api_key'       => isset( $post_meta['_edd_log_key'] ) ? $post_meta['_edd_log_key'] : 'public',
                    'token'         => isset( $post_meta['_edd_log_token'] ) ? $post_meta['_edd_log_token'] : 'public',
                    'version'       => $post_meta['_edd_log_version'],
                    'time'          => $post_meta['_edd_log_time'],
                    'request'       => $old_log->post_excerpt,
                    'error'         => $old_log->post_content,
                    'date_created'  => $old_log->post_date_gmt,
                    'date_modified' => $old_log->post_modified_gmt,
                );

                $new_log_id = edd_add_api_request_log( $log_data );
            } else {
                $post = WP_Post::get_instance( $old_log->ID );

                $log_data = array(
                    'object_id'     => $post->post_parent,
                    'object_type'   => 'download',
                    'type'          => $old_log->slug,
                    'title'         => $old_log->post_title,
                    'message'       => $old_log->post_content,
                    'date_created'  => $old_log->post_date_gmt,
                    'date_modified' => $old_log->post_modified_gmt,
                );

                $meta            = get_post_custom( $old_log->ID );
                $meta_to_migrate = array();

                foreach ( $meta as $key => $value ) {
                    $meta_to_migrate[ $key ] = maybe_unserialize( $value[0] );
                }

                $new_log_id = edd_add_log( $log_data );
                $new_log    = new EDD\Logs\Log( $new_log_id );

                if ( ! empty( $meta_to_migrate ) ) {
                    foreach ( $meta_to_migrate as $key => $value ) {
                        $new_log->add_meta( $key, $value );
                    }
                }
            }

			edd_debug_log( $old_log->ID . ' successfully migrated to ' . $new_log_id );
		}

		edd_debug_log( 'Step ' . $step . ' of logs migration complete' );

		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'logs_migration',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	} else {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		edd_set_upgrade_complete( 'migrate_logs' );
		delete_option( 'edd_doing_upgrade' );

		edd_debug_log( 'All old logs migrated, upgrade complete.' );

		edd_redirect( admin_url() );
	}
}
add_action( 'edd_logs_migration', 'edd_logs_migration' );

/**
 * Removes legacy logs data.
 *
 * @since 3.0
 */
function edd_remove_legacy_logs() {
	global $wpdb;

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	edd_set_time_limit();

	$step   = isset( $_GET['step'] )   ? absint( $_GET['step'] )   : 1;
	$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 10;
	$offset = $step == 1 ? 0 : ( $step - 1 ) * $number;

	edd_debug_log( 'Beginning step ' . $step . ' of removal of legacy logs' );

	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : false;

	if ( empty( $total ) || $total <= 1 ) {
		$total_sql = "SELECT COUNT(ID) as total_logs FROM $wpdb->posts WHERE post_type = 'edd_log'";
		$results   = $wpdb->get_row( $total_sql, 0 );
		$total     = $results->total_logs;
		edd_debug_log( $total . ' to remove' );
	}

	$log_ids = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT ID
			FROM {$wpdb->posts}
			WHERE post_type = 'edd_log'
			LIMIT %d,%d;
			",
			$offset, $number
		)
	);
	$log_ids = wp_list_pluck( $log_ids, 'ID' );
	$log_ids = implode( ', ', $log_ids );

	if ( ! empty( $log_ids ) ) {
		$delete_posts_query = "DELETE FROM {$wpdb->posts} WHERE post_type = 'edd_log'";
		$wpdb->query( $delete_posts_query );

		$delete_postmeta_query = "DELETE FROM {$wpdb->postmeta} WHERE post_id IN ({$log_ids})";
		$wpdb->query( $delete_postmeta_query );

		edd_debug_log( 'Step ' . $step . ' of removal of legacy logs complete' );

		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'remove_legacy_logs',
			'step'        => $step,
			'number'      => $number,
			'total'       => $total
		), admin_url( 'index.php' ) );

		edd_redirect( $redirect );

	} else {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		edd_set_upgrade_complete( 'remove_legacy_logs' );
		delete_option( 'edd_doing_upgrade' );

		edd_debug_log( 'Legacy logs removed, upgrade complete.' );

		edd_redirect( admin_url() );
	}
}
add_action( 'edd_remove_legacy_logs', 'edd_remove_legacy_logs' );

/**
 * Output the results of the file-download log data update
 *
 * @since 2.9.2
 */
function edd_upgrade_render_update_file_download_log_data() {
	$migration_complete = edd_has_upgrade_completed( 'update_file_download_log_data' );

	if ( $migration_complete ) : ?>
		<div id="edd-sl-migration-complete" class="notice notice-success">
			<p>
				<?php _e( '<strong>Migration complete:</strong> You have already completed the update to the file download logs.', 'easy-digital-downloads' ); ?>
			</p>
		</div>
		<?php

		delete_option( 'edd_doing_upgrade' );
		return;
	endif; ?>

	<div id="edd-migration-ready" class="notice notice-success" style="display: none;">
		<p>
			<?php _e( '<strong>Database Upgrade Complete:</strong> All database upgrades have been completed.', 'easy-digital-downloads' ); ?>
			<br /><br />
			<?php _e( 'You may now leave this page.', 'easy-digital-downloads' ); ?>
		</p>
	</div>

	<div id="edd-migration-nav-warn" class="notice notice-warning">
		<p>
			<?php _e( '<strong>Important:</strong> Please leave this screen open and do not navigate away until the process completes.', 'easy-digital-downloads' ); ?>
		</p>
	</div>

	<style>
		.dashicons.dashicons-yes { display: none; color: rgb(0, 128, 0); vertical-align: middle; }
	</style>
	<script>
		jQuery( function($) {
			$(document).ready(function () {
				$(document).on("DOMNodeInserted", function (e) {
					var element = e.target;

					if (element.id === 'edd-batch-success') {
						element = $(element);

						element.parent().prev().find('.edd-migration.allowed').hide();
						element.parent().prev().find('.edd-migration.unavailable').show();
						var element_wrapper = element.parents().eq(4);
						element_wrapper.find('.dashicons.dashicons-yes').show();

						var next_step_wrapper = element_wrapper.next();
						if (next_step_wrapper.find('.postbox').length) {
							next_step_wrapper.find('.edd-migration.allowed').show();
							next_step_wrapper.find('.edd-migration.unavailable').hide();

							if (auto_start_next_step) {
								next_step_wrapper.find('.edd-export-form').submit();
							}
						} else {
							$('#edd-migration-nav-warn').hide();
							$('#edd-migration-ready').slideDown();
						}

					}
				});
			});
		});
	</script>

	<div class="metabox-holder">
		<div class="postbox">
			<h2 class="hndle">
				<span><?php _e( 'Update file download logs', 'easy-digital-downloads' ); ?></span>
				<span class="dashicons dashicons-yes"></span>
			</h2>
			<div class="inside migrate-file-download-logs-control">
				<p>
					<?php _e( 'This will update the file download logs to remove some <abbr title="Personally Identifiable Information">PII</abbr> and make file download counts more accurate.', 'easy-digital-downloads' ); ?>
				</p>
				<form method="post" id="edd-fix-file-download-logs-form" class="edd-export-form edd-import-export-form">
			<span class="step-instructions-wrapper">

				<?php wp_nonce_field( 'edd_ajax_export', 'edd_ajax_export' ); ?>

				<?php if ( ! $migration_complete ) : ?>
					<span class="edd-migration allowed">
						<input type="submit" id="migrate-logs-submit" value="<?php _e( 'Update File Download Logs', 'easy-digital-downloads' ); ?>" class="button-primary"/>
					</span>
				<?php else: ?>
					<input type="submit" disabled="disabled" id="migrate-logs-submit" value="<?php _e( 'Update File Download Logs', 'easy-digital-downloads' ); ?>" class="button-secondary"/>
					&mdash; <?php _e( 'File download logs have already been updated.', 'easy-digital-downloads' ); ?>
				<?php endif; ?>

				<input type="hidden" name="edd-export-class" value="EDD_File_Download_Log_Migration" />
				<span class="spinner"></span>

			</span>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div>

	<?php
}

/**
 * Register the batch file-download log migration
 *
 * @since 2.9.2
 */
function edd_register_batch_file_download_log_migration() {
	add_action( 'edd_batch_export_class_include', 'edd_include_file_download_log_migration_batch_processor', 10, 1 );
}
add_action( 'edd_register_batch_exporter', 'edd_register_batch_file_download_log_migration', 10 );

/**
 * Include the file-download log batch processor
 *
 * @since 2.9.2
 *
 * @param string $class
 */
function edd_include_file_download_log_migration_batch_processor( $class = '' ) {
	if ( 'EDD_File_Download_Log_Migration' === $class ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/upgrades/classes/class-file-download-log-migration.php';
	}
}
