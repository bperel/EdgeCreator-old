<?php header('Last-Modified: ' . gmdate('D, j M Y H:i:s') . ' GMT'); // Date in the past
header('Expires: ' . gmdate( 'D, j M Y H:i:s', time() ) . ' GMT'); // always modified
header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
header('Content-Type: text/html; charset=UTF-8');
include_once APPPATH.'helpers/Ec_email_helper.php'; ?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<link rel="stylesheet" type="text/css" href="/vendor/jquery-ui/themes/sunny/jquery-ui.min.css" />
	<link rel="stylesheet" type="text/css" href="/css/edgecreator.css" />
	<link rel="stylesheet" type="text/css" href="/css/edgecreator_wizard.css" />
	<link rel="stylesheet" type="text/css" href="/css/edgecreator_gallery.css" />
	<link rel="stylesheet" type="text/css" href="/vendor/jrac/jrac/style.jrac.css" />
	<link rel="shortcut icon" href="/images/favicon.ico" />

	<script type="text/javascript" src="/vendor/jquery/jquery.min.js" ></script>
	<script type="text/javascript" src="/vendor/jquery-serializeObject/jquery.serializeObject.js" ></script>
	<script type="text/javascript" src="/vendor/jquery.ba-resize/jquery.ba-resize.min.js" ></script>
	<script type="text/javascript" src="/vendor/jrac/jrac/jquery.jrac.js" ></script>
	<script type="text/javascript" src="/vendor/jquery-ui/jquery-ui.js" ></script>

    <script src="//localhost:35729/livereload.js"></script>

	<script type="text/javascript">
		var privilege='<?=$privilege?>';
		var username = '<?=$user?>';

		var base_url='/';
		var edges_url='<?=get_ec_config('edges_url')?>';

		if (window.location.href.match(/user=/)) {
			location.replace(base_url);
		}
	</script>
	<script type="text/javascript" src="/js/edgecreator.js" ></script>
	<script type="text/javascript" src="/js/edgecreator_wizard.js" ></script>
	<script type="text/javascript" src="/js/edgecreator_wizard_imagerotate.js" ></script>

	<title><?=$title?></title>
</head>
<body id="body" style="margin:0;padding:0">
	<div class="ajout_etape tip2 template">
        <img src="/images/ajouter.png" title="Ajouter une étape ici"/>
    </div>
	<?php
	if (!empty($erreur)) {
		echo $erreur;
		?><br /><?php
	}?>
