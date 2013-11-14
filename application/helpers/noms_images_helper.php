<?php
function get_nom_fichier($nom, $pays, $magazine, $numero, $est_photo_tranche) {
    $extension_cible='.jpg';
    $dossier = getcwd().'/../edges/'
              .(is_null($pays) ? 'tranches_multiples' : ($pays.'/'.( $est_photo_tranche ? 'photos' : 'elements' )))
              .'/';
    @mkdir($dossier,0777,true);
    if ($est_photo_tranche) {
        if (isset($pays)) {
            $fichier=$magazine.'.'.$numero.'.photo';
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
        if (strpos($nom, $magazine) === 0) {
            $fichier = basename($nom);
        }
        else {
            $fichier = basename($magazine.'.'.$nom);
        }
    }
    $fichier=str_replace(' ','_',$fichier);
    return array($dossier,$fichier);
}