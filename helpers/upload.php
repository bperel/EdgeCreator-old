<?php
$est_photo_tranche = $_POST['photo_tranche'] == 1 || $_GET['photo_tranche'] == 1 ? 1 : 0;

if (!isset($_POST['MAX_FILE_SIZE'])) {
	header('Location: '.preg_replace('#/[^/]+\?#','/image_upload.php?',$_SERVER['REQUEST_URI']));
	exit;
}

$pays = isset($_POST['pays']) ? $_POST['pays'] : null;

$url_root=getcwd();
$extension = strtolower(strrchr($_FILES['image']['name'], '.'));
$extension_cible='.jpg';
$dossier = $url_root.'/../edges/'
		 .(is_null($pays) ? 'tranches_multiples' : ($pays.'/'.( $est_photo_tranche ? 'photos' : 'elements' )))
		 .'/';
if ($est_photo_tranche) {
	if (isset($pays)) {
		$fichier=$_POST['magazine'].'.'.$_POST['numero'].'.photo';
    }
    else {
        $fichier='photo.multiple';
    }
    $i=1;
    while (file_exists($dossier.$fichier.'_'.$i.$extension_cible)) {
        $i++;
    }
    $fichier.='_'.$i;
    $fichier.=$extension_cible;
}
else {
	if (strpos($_FILES['image']['name'], $_POST['magazine']) === 0) {
		$fichier = basename($_FILES['image']['name']);
	}
	else {
		$fichier = basename($_POST['magazine'].'.'.$_FILES['image']['name']);
	}
}
$fichier=str_replace(' ','_',$fichier);

$taille_maxi = $_POST['MAX_FILE_SIZE'];
$taille = filesize($_FILES['image']['tmp_name']);
$extensions = $est_photo_tranche ? array('.jpg','.jpeg') : array('.png');
//Début des vérifications de sécurité...
if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
{
	 $erreur = 'Vous devez uploader un fichier de type '.implode(' ou ',$extensions);
}
if($taille>$taille_maxi)
{
	 $erreur = 'Le fichier est trop gros.';
}
if (file_exists($dossier . $fichier)) {
	$erreur = 'Echec de l\'envoi : ce fichier existe d&eacute;j&agrave; ! '
			 .'Demandez &agrave; un admin de supprimer le fichier existant ou renommez le v&ocirc;tre !';
}
if(!isset($erreur)) //S'il n'y a pas d'erreur, on upload
{
	 //On formate le nom du fichier ici...
	 $fichier = strtr($fichier,
		  'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
		  'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
	 
	 if (@opendir($dossier) === false) {
	 	mkdir($dossier,0777,true);
	 }
	 if(move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $fichier)) {
		  if ($est_photo_tranche) {
	  		if ($extension == '.png') {
		  		$im=imagecreatefrompng($dossier . $fichier);
		  		unlink($dossier . $fichier);
		  		$fichier=str_replace('.png','.jpg',$fichier);
		  		imagejpeg($im, $dossier . $fichier);
		  	}
	  		?>
	  		<script type="text/javascript">
			if (window.parent.document.getElementById('wizard-photos').parentNode.style.display === 'block') {
				window.parent.lister_images_gallerie('Photos');
			}
			else {
				window.parent.afficher_photo_tranche();
			}
	  		</script><?php
		  }
		  ?>Envoi r&eacute;alis&eacute; avec succ&egrave;s !<?php 
		  if (isset($pays)) {
			afficher_retour($est_photo_tranche);
		  }
         else {
             ?>
            <script type="text/javascript">
                window.parent.nom_photo_tranches_multiples = '<?=$fichier?>';
                window.parent.$('.ui-dialog:visible')
                    .find('button')
                        .filter(function() {
                            return window.parent.$(this).text() === 'Suivant';
                        }).button('option','disabled', false);
            </script><?php
         }
	 }
	 else {
		  echo 'Echec de l\'envoi !';
	 	  afficher_retour($est_photo_tranche);
	 }
}
else {
	 echo $erreur;
	 afficher_retour($est_photo_tranche);
}

function afficher_retour($est_photo_tranche) {
	?><br /><a href="<?=$_SERVER['REDIRECT_URL'].'?photo_tranche='.$est_photo_tranche?>">Autre envoi</a><?php
	
}
?>