<?php
function get_nom_fichier($nom, $pays, $magazine, $numero, $est_photo_tranche) {
    $dossier = getcwd().'/../edges/'
              .(is_null($pays) ? 'tranches_multiples' : ($pays.'/'.( $est_photo_tranche ? 'photos' : 'elements' )))
              .'/';
    @mkdir($dossier,0777,true);

    if (isset($nom) && !$est_photo_tranche && preg_match('#.*\.(jpg)|(png)$#', $nom)) {
        $fichier=$nom; // On utilise le nom du fichier d'upload
    }
    else {
        $extension_cible='.jpg';
        if ($est_photo_tranche) {
            if (isset($pays)) {
                $fichier=$magazine.'.'.$numero.'.photo';
            }
            else {
                $fichier='photo.multiple';
            }
            $fichier=get_prochain_nom_fichier_dispo($dossier, $fichier, $extension_cible);
        }
        else { // Photo d'élément
            if (strpos($nom, $magazine) === 0) {
                $fichier = basename($nom);
            }
            else {
                $fichier = basename($magazine.'.'.$nom);
            }
            $fichier=get_prochain_nom_fichier_dispo($dossier, $fichier, $extension_cible);
        }
        $fichier=str_replace(' ','_',$fichier);
    }
    return array($dossier,$fichier);
}

function get_prochain_nom_fichier_dispo($dossier, $fichier, $extension_cible) {
    $i=1;
    while (file_exists($dossier.$fichier.'_'.$i.$extension_cible)) {
        $i++;
    }
    $fichier.='_'.$i;
    $fichier.=$extension_cible;
    return $fichier;
}