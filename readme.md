There is but one thing needed for this plugin to work and that is Patchwork

You must include this at the bottom of your wp-config file before the wp-settings file

$patchFile = ABSPATH . 'wp-content/plugins/seatdropper-boost/vendor/antecedent/patchwork/Patchwork.php';
if ( file_exists( $patchFile ) ) {
	require_once( $patchFile );
}

