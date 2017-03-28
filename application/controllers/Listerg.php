<?php
class ListerG extends EC_Controller {
	
	function index($nom_option,$pays=null,$magazine=null,$format='json') {
		if (in_array(null, [$nom_option])) {
			$this->load->view('errorview', ['Erreur'=>'Nombre d\'arguments insuffisant']);
			exit();
		}
		

		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');

		$liste=$this->Modele_tranche->get_liste($nom_option,$pays,$magazine);

			$data = [
                'liste'=>$liste,
                'format'=>$format
            ];

			$this->load->view('listergview',$data);
	}
}