<?php
if (isset($tranches_en_attente, $tranches_en_attente_d_edition)) {
    echo json_encode([
        'tranches_en_cours' => $tranches_en_cours,
        'tranches_en_attente' => $tranches_en_attente,
        'tranches_en_attente_d_edition' => $tranches_en_attente_d_edition
    ]);
}
else {
    echo json_encode([
        'tranches_en_cours' => $tranches_en_cours
    ]);
}
