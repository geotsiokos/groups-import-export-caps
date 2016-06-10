<?php
/**
 * caps-export.php
 * 
 */
?>

<?php
if ( !defined( 'ABSPATH' ) ) {
	die;
}

if ( !current_user_can( 'manage_options' ) ) {
	wp_die( __( 'Access denied.', GROUPS_IMPORT_EXPORT_PLUGIN_DOMAIN ) );
}
?>

<div>
<form enctype="multipart/form-data" name="export-users" method="post" action="">
<div>

<?php
	echo '<p>';
	echo __( 'Export groups cababilities to a text file &hellip;', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN );
	echo '</p>';
?>
<?php wp_nonce_field( 'export', 'groups-caps-export', true, true ); ?>

<style type="text/css">
div.selectize-input { width: 95%; }
</style>

<div class="buttons">
<p>
<input class="export button-primary" type="submit" name="submit" value="<?php echo __( 'Export', GROUPS_IMPORT_EXPORT_CAPS_PLUGIN_DOMAIN ); ?>" />
</p>
<input type="hidden" name="action" value="export_users" />
</div>

</div>
</form>
</div>
