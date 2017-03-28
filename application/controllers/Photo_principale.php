<?php
class Photo_Principale extends EC_Controller {
	
	function index() {

		$this->init_model();
        $this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		$nom_photo_principale=$this->Modele_tranche->get_photo_principale();

		$data = [
			'nom_photo_principale'=>$nom_photo_principale
        ];

		$this->load->view('photo_principaleview',$data);
	}
}