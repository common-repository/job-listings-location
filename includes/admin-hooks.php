<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//Limit level job location taxonomy
function jlt_taxonomy_parent_dropdown_args( $dropdown_args, $taxonomy ) {
	$location_lvl_opt = jlt_get_location_setting( 'location_lvl', 2 );

	if ( 'job_location' == $taxonomy ) {

		if ( $location_lvl_opt == 1 ) {

			$dropdown_args[ 'class' ] = 'hidden'; // 1 lvl

		} elseif ( $location_lvl_opt == 3 ) {

			$dropdown_args[ 'depth' ] = 2; // 3 lvls

		} else {
			$dropdown_args[ 'depth' ] = 1; // 2 lvls
		}
	}

	return $dropdown_args;
}

add_filter( 'taxonomy_parent_dropdown_args', 'jlt_taxonomy_parent_dropdown_args', 10, 2 );

function jlt_adv_location_remove_meta_boxes() {

	remove_meta_box( 'job_locationdiv', 'jlt_job', 'side' );
}

add_action( 'admin_menu', 'jlt_adv_location_remove_meta_boxes' );

function jlt_adv_location_job_metabox_show( $post ) {
	add_meta_box( 'jlt_job_location_meta_box', __( 'Job Location', 'job-listings-location' ), 'jlt_adv_location_job_metabox', 'jlt_job', 'side' );
}

add_action( 'add_meta_boxes_jlt_job', 'jlt_adv_location_job_metabox_show' );

function jlt_adv_location_job_metabox( $post ) {

	$job_id = $post->ID;

	$location_lvl_opt_count = jlt_get_location_setting( 'location_lvl', 2 );

	$location_lvl_label = jlt_get_location_setting( 'location_lvl_label', 'Country|City' );
	$list_label         = explode( '|', $location_lvl_label );

	$term_list = wp_get_post_terms( $job_id, 'job_location', array( "fields" => "all" ) );

	$select = [ ];

	foreach ( $term_list as $term ) {

		if ( $term->parent == 0 ) {
			$select[ 1 ] = $term;
		}
		if ( ! empty( $select[ 1 ] ) ) {
			$select[ 2 ] = jlt_location_get_child( $job_id, $select[ 1 ]->term_id );
		}

		if ( ! empty( $select[ 2 ] ) ) {
			$select[ 3 ] = jlt_location_get_child( $job_id, $select[ 2 ]->term_id );
		}
	}

	echo '<div class="jlt-control">';

	for ( $i = 1; $i <= $location_lvl_opt_count; $i ++ ):

		$selected  = ! empty( $select[ $i ]->slug ) ? $select[ $i ]->slug : '';
		$parent_id = ! empty( $select[ $i - 1 ]->term_id ) ? $select[ $i - 1 ]->term_id : - 1;

		$show_option_none = ! empty( $list_label[ $i - 1 ] ) ? $list_label[ $i - 1 ] : '';

		$dropdown_args = array(
			'hide_empty'       => 0,
			'hide_if_empty'    => false,
			'taxonomy'         => 'job_location',
			'class'            => 'jlt-admin-location-select jlt-admin-location-select' . $i,
			'name'             => 'location_level_' . $i,
			'id'               => 'location_' . $i,
			'orderby'          => 'name',
			'value_field'      => 'slug',
			'selected'         => $selected,
			'show_option_none' => $show_option_none,
		);

		if ( $i == 1 ) {
			$dropdown_args[ 'parent' ] = 0;
		} else {
			$dropdown_args[ 'parent' ] = $parent_id;
		}
		echo '<label>' . $show_option_none . '</label>';
		wp_dropdown_categories( $dropdown_args );

	endfor;

	$location_address = get_post_meta( $job_id, '_location_address', true );
	echo '<label>' . __( 'Complete Address', 'job-listings-location' ) . '</label>';
	echo '<input type="text" name="_location_address" placeholder="' . __( 'Complete Address', 'job-listings-location' ) . '" class="jlt-location-address" value="' . $location_address . '" />';

	echo '</div>';
}

//Save data

function jlt_adv_location_save_data( $job_id ) {

	$full_address = [ ];

	// Insert location_level_1

	if ( isset( $_POST[ 'location_level_1' ] ) && $_POST[ 'location_level_1' ] != - 1 ) {

		$location_country = sanitize_text_field( $_POST[ 'location_level_1' ] );
		$term             = get_term_by( 'slug', sanitize_title( $location_country ), 'job_location' );

		if ( ! empty( $term ) ) {

			wp_set_post_terms( $job_id, $term->term_id, 'job_location', false );

			$full_address[] = $term->name;
		}
	}

	// Insert location_level_2

	if ( isset( $_POST[ 'location_level_2' ] ) && $_POST[ 'location_level_2' ] != - 1 ) {

		$location_state = sanitize_text_field( $_POST[ 'location_level_2' ] );
		$term           = get_term_by( 'slug', sanitize_title( $location_state ), 'job_location' );

		if ( ! empty( $term ) ) {

			wp_set_post_terms( $job_id, $term->term_id, 'job_location', true );

			$full_address[] = $term->name;
		}
	}

	// Insert location_level_3

	if ( isset( $_POST[ 'location_level_3' ] ) && $_POST[ 'location_level_3' ] != - 1 ) {

		$location_city = sanitize_text_field( $_POST[ 'location_level_3' ] );
		$term          = get_term_by( 'slug', sanitize_title( $location_city ), 'job_location' );

		if ( ! empty( $term ) ) {

			wp_set_post_terms( $job_id, $term->term_id, 'job_location', true );

			$full_address[] = $term->name;
		}
	}

	if ( isset( $_POST[ '_location_address' ] ) ) {
		$location_address = sanitize_text_field( $_POST[ '_location_address' ] );
		$full_address[]   = $location_address;
		update_post_meta( $job_id, '_location_address', $location_address );
	}

	$full_address = array_reverse( $full_address );
	$full_address = implode( ", ", $full_address );

	update_post_meta( $job_id, '_location_address_full', $full_address );
}

add_action( 'jlt_after_save_job', 'jlt_adv_location_save_data' );
add_action( 'save_post_jlt_job', 'jlt_adv_location_save_data' );

function jlt_adv_save_long_lat( $post_id ) {
	$full_address = get_post_meta( $post_id, '_location_address_full', true );
	if ( ! empty( $full_address ) ) {
		$location = jlt_adv_location_geo( $full_address );
		if ( ! empty( $location ) ) {
			update_post_meta( $post_id, '_location_lat', $location[ 'lat' ] );
			update_post_meta( $post_id, '_location_long', $location[ 'long' ] );
		}
	}
}

add_action( 'save_post_jlt_job', 'jlt_adv_save_long_lat' );
