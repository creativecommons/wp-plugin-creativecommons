<html>
<head>
<script src="http://code.jquery.com/jquery-latest.min.js"></script>
</head>
<body>
<?php
$license = array(
	'url' => $_GET["url"],
	'name' => $_GET["name"],
	'button' => $_GET["button"],
	'deed' => $_GET["deed"]
);
?>
<script>
jQuery(function($) {
	parent.setLicense($.parseJSON('<?php echo json_encode($license); ?>'));
	parent.tb_remove();
});
</script>
</body>
</html>