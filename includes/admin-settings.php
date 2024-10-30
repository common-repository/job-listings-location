<?php
/**
 * settings.php
 *
 * @package:
 * @since  : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function jlt_setting_location_fields() {
	$location_lvl       = jlt_get_location_setting( 'location_lvl', 2 );
	$location_lvl_label = jlt_get_location_setting( 'location_lvl_label', 'Country|City' );
	?>
	<tr>
		<th>
			<?php _e( 'Location Levels', 'job-listings-location' ) ?>
		</th>
		<td>
			<fieldset>
				<label><input type="radio" <?php checked( $location_lvl, 1 ); ?>
				              name="jlt_location[location_lvl]"
				              value="1"><?php _e( '1 Level', 'job-listings-location' ); ?></label>
				<br/>
				<label><input type="radio" <?php checked( $location_lvl, 2 ); ?>
				              name="jlt_location[location_lvl]"
				              value="2"><?php _e( '2 Levels', 'job-listings-location' ); ?></label><br/>
				<label><input type="radio" <?php checked( $location_lvl, 3 ); ?>
				              name="jlt_location[location_lvl]"
				              value="3"><?php _e( '3 Levels', 'job-listings-location' ); ?></label><br/>
			</fieldset>
			<p>
				<small><?php _e( 'Select the location level.', 'job-listings-location' ); ?></small>
			</p>
		</td>
	</tr>
	<tr>
		<th>
			<?php _e( 'Location Labels', 'job-listings-location' ) ?>
		</th>
		<td>
			<fieldset>
				<input type="text" name="jlt_location[location_lvl_label]" class="regular-text"
				       value="<?php echo $location_lvl_label; ?>"/>
			</fieldset>
			<p>
				<small><?php _e( 'Each level separated by | Example: Country|State|City', 'job-listings-location' ); ?></small>
			</p>
		</td>
	</tr>

	<?php do_action( 'jlt_admin_setting_location_advanced_field' ); ?>
	<?php
}

add_action( 'jlt_admin_setting_location_fields', 'jlt_setting_location_fields' );