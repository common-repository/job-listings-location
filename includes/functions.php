<?php
/**
 * functions.php
 *
 * @package:
 * @since  : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function jlt_adv_location_ajax_get_child( $level, $location_id ) {

	$parent        = sanitize_text_field( $_POST[ 'parent' ] );
	$location_type = sanitize_text_field( $_POST[ 'location_type' ] );

	$location_lvl_opt = jlt_get_location_setting( 'location_option', array() );

	$location_select_label_next = __( 'Select location', 'job-listings-location' );
	switch ( $location_type ) {
		case 'location_country':
			$location_select_label_next = __( 'Select State', 'job-listings-location' );
			if ( ! in_array( 'state', $location_lvl_opt ) ) {
				$location_select_label_next = __( 'Select City', 'job-listings-location' );
			}
			break;
		case 'location_state':
			$location_select_label_next = __( 'Select City', 'job-listings-location' );
			break;
	}

	$html = '';
	$html .= '<option value="-1">' . esc_html( $location_select_label_next ) . '</option>';

	$parent = trim( stripslashes( $parent ) );

	if ( $parent && $parent != - 1 ) {

		$selected_spec   = get_term_by( 'slug', $parent, 'job_location' );
		$state_parent_id = $selected_spec->term_id;
		$states_args     = array(
			'orderby'    => 'name',
			'order'      => 'ASC',
			'fields'     => 'all',
			'slug'       => '',
			'hide_empty' => false,
			'parent'     => $state_parent_id,
		);
		$values          = get_terms( 'job_location', $states_args );

		if ( isset( $values ) && $values != '' && is_array( $values ) ) {
			foreach ( $values as $key => $value ) {
				$html .= "<option value='" . $value->slug . "'>" . $value->name . "</option>";
			}
		}
	}
	echo $html;

	die();
}

add_action( "wp_ajax_jlt_adv_location_ajax_get_child", "jlt_adv_location_ajax_get_child" );
add_action( "wp_ajax_nopriv_jlt_adv_location_ajax_get_child", "jlt_adv_location_ajax_get_child" );

function jlt_location_get_child( $job_id, $term_parent_id ) {
	$result = [ ];
	$rs     = wp_get_post_terms( $job_id, 'job_location', array( 'parent' => $term_parent_id ) );
	if ( ! empty( $rs ) ) {
		$result = $rs[ 0 ];
	}

	return $result;
}

function jlt_render_location_field( $field = array(), $field_id = '', $value = array(), $form_type = '', $object = array() ) {

	if ( ! empty( $object ) && isset( $object[ 'ID' ] ) ) :
		$job_id = absint( $object[ 'ID' ] );

		$location_term = ! empty( $location_term_id ) ? get_term( $location_term_id, 'job_location' ) : '';
		$location_term = ! empty( $location_term ) ? $location_term->slug : '';

		$location_lvl_opt_count = jlt_get_location_setting( 'location_lvl', 2 );

		$location_lvl_label = jlt_get_location_setting( 'location_lvl_label', 'Country|City' );

		$list_label = ! empty( $location_lvl_label ) ? explode( '|', $location_lvl_label ) : '';

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

		echo '<div class="jlt-location-field jlt-location-col-' . $location_lvl_opt_count . '">';

		for ( $i = 1; $i <= $location_lvl_opt_count; $i ++ ):

			$selected  = ! empty( $select[ $i ]->slug ) ? $select[ $i ]->slug : '';
			$parent_id = ! empty( $select[ $i - 1 ]->term_id ) ? $select[ $i - 1 ]->term_id : - 1;

			$show_option_none = ! empty( $list_label[ $i - 1 ] ) ? $list_label[ $i - 1 ] : '';

			$dropdown_args = array(
				'hide_empty'       => 0,
				'hide_if_empty'    => false,
				'taxonomy'         => 'job_location',
				'class'            => 'jlt-form-control jlt-location-control jlt-location-' . $i,
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

			wp_dropdown_categories( $dropdown_args );

		endfor;

		echo '</div>';
		$location_address = get_post_meta( $job_id, '_location_address', true );
		echo '<input data-validation="required" type="text" name="_location_address" placeholder="' . __( 'Complete Address', 'job-listings-location' ) . '" class="jlt-form-control jlt-location-address" value="' . $location_address . '" />';

		?>

		<?php
	endif;
}

remove_filter( 'jlt_render_field_job_location', 'jlt_job_render_field_job_location' );
remove_filter( 'jlt_render_field_multi_location_input', 'jlt_job_render_field_job_location' );
remove_filter( 'jlt_render_field_multi_location', 'jlt_job_render_field_job_location' );
remove_filter( 'jlt_render_field_single_location_input', 'jlt_job_render_field_job_location' );
remove_filter( 'jlt_render_field_single_location', 'jlt_job_render_field_job_location' );

add_filter( 'jlt_render_field_job_location', 'jlt_render_location_field', 10, 5 );
add_filter( 'jlt_render_field_multi_location_input', 'jlt_render_location_field', 10, 5 );
add_filter( 'jlt_render_field_multi_location', 'jlt_render_location_field', 10, 5 );
add_filter( 'jlt_render_field_single_location_input', 'jlt_render_location_field', 10, 5 );
add_filter( 'jlt_render_field_single_location', 'jlt_render_location_field', 10, 5 );

function jlt_adv_location_get_address( $job_id ) {
	return get_post_meta( $job_id, '_location_address', true );
}

function jlt_adv_location_get_address_full( $job_id ) {
	return get_post_meta( $job_id, '_location_address_full', true );
}

function jlt_adv_location_geo( $full_address ) {

	$geo = file_get_contents( 'http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode( $full_address ) . '&sensor=false' );
	$geo = json_decode( $geo, true );

	if ( $geo[ 'status' ] == 'OK' ) {
		$location[ 'lat' ]  = $geo[ 'results' ][ 0 ][ 'geometry' ][ 'location' ][ 'lat' ];
		$location[ 'long' ] = $geo[ 'results' ][ 0 ][ 'geometry' ][ 'location' ][ 'lng' ];

		return $location;
	}

	return '';
}

function jlt_get_job_location_advanced() {
	$job_id = get_the_ID();
	$sep    = apply_filters( 'jlt_job_location_html_sep', ', ' );

	$location_complete = get_post_meta( $job_id, '_location_address', true );

	$html            = $location_complete . $sep;
	$locations_order = jlt_order_location_terms( $job_id );
	foreach ( $locations_order as $location ) {
		if ( ! empty( $location ) && ! is_wp_error( $location ) ) {

			$html .= '<a href="' . get_term_link( $location, 'job_location' ) . '" title="' . esc_attr( sprintf( __( "View all jobs in: &ldquo;%s&rdquo;", 'job-listings-location' ), $location->name ) ) . '">' . ' ' . $location->name . '</a>' . $sep;
		}
	}

	$html = trim( $html, $sep );

	return $html;
}

add_filter( 'jlt_job_location_advanced', 'jlt_get_job_location_advanced' );

function jlt_adv_location_display() {

	$location = apply_filters( 'jlt_job_location_advanced', '' );

	if ( ! empty( $location ) ) {

		$prefix = apply_filters( 'jlt_job_location_html_prefix', '' );
		$html   = '';

		$html[] = '<div class="jlt-tags job-location">' . $prefix;
		$html[] = $location;
		$html[] = '</div>';

		return implode( $html, "\n" );
	}
}

add_filter( 'jlt_job_location_html', 'jlt_adv_location_display', 99 );

function jlt_order_location_terms( $job_id ) {

	$location_lvl_setting = jlt_get_location_setting( 'location_lvl', 2 );
	$term_list            = wp_get_post_terms( $job_id, 'job_location', array( "fields" => "all" ) );

	$select = [ ];

	foreach ( $term_list as $key => $term ) {

		if ( $term->parent == 0 ) {

			$select[ 1 ] = $term;
			unset( $term_list[ $key ] );

			if ( $location_lvl_setting > 1 ) {

				foreach ( $term_list as $key2 => $term2 ) {
					if ( $term_list[ $key2 ]->parent == $select[ 1 ]->term_id ) {
						$select[ 2 ] = $term2;
						unset( $term_list[ $key2 ] );

						break;
					}
				}
				break;
			}
		}
	}

	if ( $location_lvl_setting > 2 ) {
		$select[ 3 ] = reset( $term_list );
	}

	$select = array_reverse( $select );

	return $select;
}