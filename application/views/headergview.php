<?php header("Last-Modified: " . gmdate( "D, j M Y H:i:s" ) . " GMT"); // Date in the past
header("Expires: " . gmdate( "D, j M Y H:i:s", time() ) . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", FALSE);
header("Pragma: no-cache");
header("Content-Type: text/html; charset=UTF-8"); ?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<link rel="stylesheet" type="text/css" href="<?=base_url()?>vendor/jquery-ui/themes/sunny/jquery-ui.min.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator_wizard.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>css/edgecreator_gallery.css" />
	<link rel="stylesheet" type="text/css" href="<?=base_url()?>vendor/jrac/jrac/style.jrac.css" />
	<link rel="shortcut icon" href="<?=base_url()?>images/favicon.ico" />

	<script type="text/javascript" src="<?=base_url()?>vendor/jquery/jquery.min.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>vendor/jquery-serializeObject/jquery.serializeObject.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>vendor/jquery.ba-resize/jquery.ba-resize.min.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>vendor/jrac/jrac/jquery.jrac.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>vendor/jquery-ui/jquery-ui.js" ></script>

	<script type="text/javascript" src="<?=base_url()?>js/edgecreatorlib.js" ></script>

	<script type="text/javascript">
		var privilege='<?=$privilege?>';
		var username = '<?=$user?>';

		var base_url='<?=base_url()?>';
		var edges_url='https://edges.ducksmanager.net/edges';

		if (window.location.href.match(/user=/)) {
			location.replace(base_url);
		}

		var urls=[];
		<?php
		$controleurs= [
            'update_wizard','edgecreatorg','tranchesencours','numerosdispos','parametrageg','parametrageg_wizard',
		   				   'modifierg','supprimerg','listerg','etendre','creer_modele_wizard','insert_wizard','cloner','upload_wizard','supprimer_wizard','viewer_wizard','viewer_myfonts',
						   'dessiner','photo_principale','update_photo','rogner_image','desactiver_modele','valider_modele','check_logged_in','couleurs_frequentes','couleur_point_photo'
        ];
		foreach($controleurs as $controleur) {
			?>urls['<?=$controleur?>']='<?=site_url($controleur)?>/';<?php
		}?>
	</script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreator.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreator_wizard.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>js/edgecreator_wizard_imagerotate.js" ></script>

	<title><?=$title?></title>
</head>
<body id="body" style="margin:0;padding:0">
	<img class="ajout_etape tip2 template hidden"
		 src="<?=base_url()?>images/ajouter.png" title="Ajouter une étape ici"/>
	<?php
	if (!empty($erreur)) {
		echo $erreur;
		?><br /><?php
	}?>
