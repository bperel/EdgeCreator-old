<?php
class Photo_Principale extends CI_Controller {
	
	function index($pays=null,$magazine=null,$numero=null) {
		if (in_array(null,array($pays,$magazine,$numero))) {
			$this->load->view('errorview',array('Erreur'=>'Nombre d\'arguments insuffisant'));
			exit();
		}
		
		$this->db->query('SET NAMES UTF8');
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');
        $this->Modele_tranche->setUsername($this->session->userdata('user'));
		
		$nom_photo_principale=$this->Modele_tranche->get_photo_principale($pays,$magazine,$numero,true);

		$data = array(
			'nom_photo_principale'=>$nom_photo_principale
		);

		$this->load->view('photo_principaleview',$data);
	}
}
?>
