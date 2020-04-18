<?php
$json = [];
if (isset($id_modele)) {
    $json['id_modele'] = $id_modele;
}
if (isset($infos_insertion)) {
    $json['infos_insertion'] = $infos_insertion;
}

echo json_encode($json);
