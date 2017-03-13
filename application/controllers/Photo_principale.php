<?php
class Photo_Principale extends EC_Controller {
	
	function index($pays=null,$magazine=null,$numero=null) {
		if (in_array(null, [$pays,$magazine,$numero])) {
			$this->load->view('errorview', ['Erreur'=>'Nombre d\'arguments insuffisant']);
			exit();
		}
		

		$this->init_model();
        $this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		$nom_photo_principale=$this->Modele_tranche->get_photo_principale($pays,$magazine,$numero,true);

		$data = [
			'nom_photo_principale'=>$nom_photo_principale
        ];

		$this->load->view('photo_principaleview',$data);
	}
}