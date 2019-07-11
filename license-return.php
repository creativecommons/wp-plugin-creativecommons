<?php
/**
 * Plugin Name: Creative Commons
 * Description: Official Creative Commons plugin for WordPress. Allows users to select and display
 * Creative Commons licenses for their content. Partially inspired by the License plugin by mitcho
 * (Michael Yoshitaka Erlewine) and Brett Mellor, as well as the original WpLicense plugin by CC CTO
 * Nathan R. Yergler.
 * Version: 2.0-beta
 * Author: Bjorn Wijers <burobjorn@burobjorn.nl>, Tarmo Toikkanen <tarmo@iki.fi>, Matt Lee <mattl@creativecommons.org>, Rob Myers <rob@creativecommons.org>
 * License: GPLv2 or later versions
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @link http://wiki.creativecommons.org/WpLicense
 *
 * @package CC_WordPress_Plugin
 */

if ( ! empty( $_GET['url'] ) ) {
	$license['url'] = $_GET['url'];
}
if ( ! empty( $_GET['name'] ) ) {
	$license['name'] = $_GET['name'];
}
if ( ! empty( $_GET['button'] ) ) {
	$license['button'] = $_GET['button'];
}
if ( ! empty( $_GET['deed'] ) ) {
	$license['deed'] = $_GET['deed'];
}

$license = array_map(
	function( $retval ) {
		return filter_var( $retval, FILTER_SANITIZE_STRING );
	},
	$license
);

?>
<html>
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	</head>
	<body>
		<script>
			jQuery(function($) {
			parent.setLicense( $.parseJSON( '<?php echo json_encode( $license ); ?>' ) );
				parent.tb_remove();
			});
		</script>
	</body>
</html>
