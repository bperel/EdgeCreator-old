<?php
include_once APPPATH.'helpers/dm_client.php';
include_once APPPATH.'helpers/error_handler.php';

class EC_Controller extends CI_Controller {

    /**
     * @var Modele_tranche_Wizard
     */
    var $Modele_tranche;

    function __construct()
    {
        parent::__construct();
        DmClient::init($this->session->userdata());
    }

    function init_model() {
        $this->load->model('Modele_tranche_Wizard','Modele_tranche');
    }
}
