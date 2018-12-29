<?php
function get_nom_fichier($nom, $multiple, $est_photo_tranche, $pays, $magazine, $numero) {
    $dossier = getcwd().'/../edges/'
              .($multiple ? 'tranches_multiples' : ($pays.'/'.( $est_photo_tranche ? 'photos' : 'elements' )))
              .'/';
    @mkdir($dossier,0777,true);

    if (isset($nom) && !$est_photo_tranche && preg_match('#\.(jpg)|(png)$#', $nom)) {  // On utilise le nom du fichier d'upload
        if (isset($magazine) && strpos($nom, $magazine.'.') !== 0) {
            $fichier=$magazine.'.'.$nom;
        }
        else {
            $fichier=$nom;
        }
    }
    else {
        if ($est_photo_tranche) {
            if ($multiple) {
                $fichier='photo.multiple';
            }
            else {
                $fichier=$magazine.'.'.$numero.'.photo';
            }
        }
        else if (isset($magazine)) {
            $fichier = basename($magazine.'.'.$nom);
        }
        else {
            $fichier = basename($nom);
        }
        $fichier=get_prochain_nom_fichier_dispo($dossier, $fichier, '.jpg');
    }
    $fichier=str_replace(' ','_',$fichier);
    return [$dossier,$fichier];
}

function get_prochain_nom_fichier_dispo($dossier, $fichier, $extension_cible) {
    $i=1;
    while (file_exists($dossier.$fichier.'_'.$i.$extension_cible)) {
        $i++;
    }
    $fichier.='_'.$i.$extension_cible;
    return $fichier;
}
