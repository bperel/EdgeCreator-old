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

        $multiple = isset($_POST['multiple']) && $_POST['multiple'] === '1';
        $pays     = $_POST['pays'] ?? null;
        $magazine = $_POST['magazine'] ?? null;
        $numero   = $_POST['numero'] ?? null;

        $upload_results = [
            'est_photo_tranche' => $est_photo_tranche
        ];

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
                    $erreur = 'L\'envoi a été interrompu';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $erreur = 'Le fichier envoyé est vide';
                    break;
                default:
                    $erreur = 'Erreur interne lors de l\'envoi : #'.$_FILES['image']['error'];
                    break;
            }
        }

        if (isset($erreur)) {
            ErrorHandler::error_log($erreur);
            $upload_results['erreur'] = $erreur;
            $upload_results['proposer_autre_envoi'] = true;
        }
        else {
            list($dossier,$fichier) = get_nom_fichier($_FILES['image']['name'], $multiple, $est_photo_tranche, $pays, $magazine, $numero);
            $extension = strtolower(strrchr($_FILES['image']['name'], '.'));

            $taille_maxi = $_POST['MAX_FILE_SIZE'];
            $taille = filesize($_FILES['image']['tmp_name']);
            $extensions = $est_photo_tranche ? ['.jpg','.jpeg'] : ['.png'];
            if (!in_array($extension, $extensions)) {
                $erreur = 'Vous devez uploader un fichier de type '.implode(' ou ',$extensions);
            }

            if($taille>$taille_maxi) {
                $erreur = get_message_fichier_trop_gros();
            }
            if (file_exists($dossier . $fichier)) {
                $modeles_utisant_fichier = $this->Modele_tranche->get_autres_modeles_utilisant_fichier($fichier);
                if (count($modeles_utisant_fichier) > 0) {
                    $erreur = 'Echec de l\'envoi : ce fichier est peut-être utilisé par d\'autres modeles : <pre>'
                        .print_r($modeles_utisant_fichier, true)
                        .'</pre>';
                }
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

            if(isset($erreur)) {
                ErrorHandler::error_log($erreur);
                $upload_results['erreur'] = $erreur;
                $upload_results['proposer_autre_envoi'] = true;
            }
            else {
                $fichier = strtr($fichier,
                    'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ',
                    'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');

                if (@opendir($dossier) === false) {
                    mkdir($dossier,0777,true);
                }

                if(move_uploaded_file($_FILES['image']['tmp_name'], $dossier . $fichier)) {
                    if ($est_photo_tranche) {
                        $this->Modele_tranche->ajouter_photo_tranches_multiples($fichier, sha1_file($dossier . $fichier));
                    }
                    if (!$multiple) {
                        $upload_results['proposer_autre_envoi'] = true;
                    }
                    $upload_results['nomFichier'] = $fichier;
                }
                else {
                    ErrorHandler::error_log('Echec de la copie vers '.$dossier . $fichier);
                    $upload_results['erreur'] = 'Erreur technique.';
                    $upload_results['proposer_autre_envoi'] = true;
                }
            }
        }
        $upload_results['self_url'] =
                preg_replace('#\?.*$#', '', $_SERVER['HTTP_REFERER'])
                .'?'.http_build_query(array_merge(
                    ['photo_tranche' => $est_photo_tranche],
                    $multiple ? ['multiple' => '1'] : []
                ));
        $this->load->view('upload_resultsview', $upload_results);
    }
}

function get_message_fichier_trop_gros() {
    return 'Le fichier est trop gros (taille maximale : '.$_POST['MAX_FILE_SIZE'].' octets';
}
