<?php
class EC_Controller extends CI_Controller {

    /**
     * @var Modele_tranche_Wizard
     */
    var $Modele_tranche;

    function init_model() {
        $this->load->model('Modele_tranche_Wizard','Modele_tranche');
    }
}