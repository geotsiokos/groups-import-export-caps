<?php
if ( !defined( 'ABSPATH' ) ) {
	die;
}

if ( !current_user_can( 'manage_options' ) ) {
	wp_die( __( 'Access denied.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ) );
}
?>

<div>
<form enctype="multipart/form-data" name="import-users" method="post" action="">
<div>
<br/>
<?php
	echo '<p>';
	echo __( 'Import groups capabilities from a text file &hellip;', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
	echo '</p>';
?>
<?php wp_nonce_field( 'import', 'groups-caps-import', true, true ); ?>

<div class="buttons">

<p>
<label>
<?php _e( 'Import capabilities from file', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ); ?> <input type="file" name="file" />
</label>
</p>

<style type="text/css">
div.selectize-input { width: 95%; }
</style>

<p>
<input class="import button-primary" type="submit" name="submit" value="<?php echo __( 'Import', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ); ?>" />
</p>

<input type="hidden" name="action" value="import_users" />
</div>
</div>
</form>
</div>

<?php
	echo '<p>';
	echo __( 'An import can not be undone, when in doubt, run the import on a test installation first.', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
	echo '</p>';
