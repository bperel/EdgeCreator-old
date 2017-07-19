<?php

class Upload_Wizard extends EC_Controller {

    var $contenu = '';
	
	function index() {
        $this->init_model();

        $est_photo_tranche = (isset($_POST['photo_tranche']) && $_POST['photo_tranche'] == 1)
                          || (isset($_GET ['photo_tranche']) && $_GET ['photo_tranche'] == 1)
            ? 1
            : 0;

        if (!isset($_POST['MAX_FILE_SIZE'])) {
            header('Location: '.preg_replace('#/[^/]+\?#','/image_upload.php?',$_SERVER['REQUEST_URI']));
            exit;
        }

        $pays     = isset($_POST['pays'])     ? $_POST['pays']     : null;
        $magazine = isset($_POST['magazine']) ? $_POST['magazine'] : null;
        $numero   = isset($_POST['numero'])   ? $_POST['numero']   : null;

        $this->load->helper('noms_images');

        if (isset($_FILES['image']['error'])) {
            switch( $_FILES['image']['error'] ) {
                case UPLOAD_ERR_OK:
                    break;

                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $erreur = get_message_fichier_trop_gros();
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $erreur = 'L\'envoi a �t� interrompu';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $erreur = 'Le fichier envoy� est vide';
                    break;
                default:
                    $erreur = 'Erreur interne lors de l\'envoi : #'.$_FILES['image']['error'];
                    break;
            }
        }

        if (isset($erreur)) {
            $this->contenu .= $erreur;
            $this->contenu .= get_message_retour($est_photo_tranche);
        }
        else {

            list($dossier,$fichier) = get_nom_fichier($_FILES['image']['name'], $pays, $magazine, $numero, $est_photo_tranche);
            $extension = strtolower(strrchr($_FILES['image']['name'], '.'));

            $taille_maxi = $_POST['MAX_FILE_SIZE'];
            $taille = filesize($_FILES['image']['tmp_name']);
            $extensions = $est_photo_tranche ? ['.jpg','.jpeg'] : ['.png'];
            //D�but des v�rifications de s�curit�...
            if(!in_array($extension, $extensions)) //Si l'extension n'est pas dans le tableau
            {
                $erreur = 'Vous devez uploader un fichier de type '.implode(' ou ',$extensions);
            }

            if($taille>$taille_maxi)
            {
                $erreur = get_message_fichier_trop_gros();
            }
            if (file_exists($dossier . $fichier)) {
                $erreur = 'Echec de l\'envoi : ce fichier existe d&eacute;j&agrave; ! '
                    .'Demandez &agrave; un admin de supprimer le fichier existant ou renommez le v&ocirc;tre !';
            }

            if ($est_photo_tranche) {
                $limite_atteinte = $this->Modele_tranche->est_limite_photos_atteinte();

                if ($limite_atteinte) {
                    $erreur = 'Votre limite d\'envoi quotidienne a été atteinte';
                }
                else {
                    $file_hash = sha1_file($_FILES['image']['tmp_name']);
                    $photo_existante = $this->Modele_tranche->get_photo_existante($file_hash);
                    if (!is_null($photo_existante)) {
                        $erreur = 'Vous avez déjà envoyé cette photo';
                    }
                }
            }

            if(!isset($erreur)) //S'il n'y a pas d'erreur, on upload
            {
                //On formate le nom du fichier ici...
                $fichier = strtr($fichier,
                    '����������������������������������������������������',
                    'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');

                if (@opendir($dossier) === false) {
                    mkdir($dossier,0777,true);
                }

                if(move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $fichier)) {
                    if ($est_photo_tranche) {
                        $this->Modele_tranche->ajouter_photo_tranches_multiples($fichier, sha1_file($dossier . $fichier));

                        ob_start();
                        ?>
                        <script type="text/javascript">
                            if (window.parent.$('wizard-photos')
                             && window.parent.$('wizard-photos').parent().is(':visible')) {
                                window.parent.lister_images_gallerie('Photos');
                            }
                            else {
                                window.parent.afficher_photo_tranche();
                            }
                        </script><?php
                        $this->contenu.= ob_get_flush();
                    }
                    $this->contenu .= 'Envoi r&eacute;alis&eacute; avec succ&egrave;s !';
                    if (isset($pays)) {
                        $this->contenu .= get_message_retour($est_photo_tranche);
                    }
                    else {
                        ob_start();
                        ?>
                        <script type="text/javascript">
                            window.parent.nom_photo_tranches_multiples = '<?=$fichier?>';
                            window.parent.$('.ui-dialog:visible')
                                .find('button')
                                .filter(function() {
                                    return window.parent.$(this).text() === 'Suivant';
                                }).button('option','disabled', false);
                        </script><?php
                        $this->contenu.= ob_get_flush();
                    }
                }
                else {
                    $this->contenu .= 'Echec de l\'envoi !'.$dossier . $fichier;
                    $this->contenu .= get_message_retour($est_photo_tranche);
                }
            }
            else {
                $this->contenu .= $erreur;
                $this->contenu .= get_message_retour($est_photo_tranche);
            }
        }
        $this->load->view('helperview', ['contenu'=>$this->contenu]);
    }
}

function get_message_fichier_trop_gros() {
    return 'Le fichier est trop gros (taille maximale : '.$_POST['MAX_FILE_SIZE'].' octets';
}

function get_message_retour($est_photo_tranche) {
    return '<br /><a href="'.preg_replace('#\?.*$#', '', $_SERVER['HTTP_REFERER']).'?photo_tranche='.$est_photo_tranche.'">Autre envoi</a>';
}