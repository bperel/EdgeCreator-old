<?php
class EdgeCreatorg extends Ec_Controller {
	function login() {
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		
		$this->logout();
		
		global $erreur;$erreur='';
		
		$privilege=$this->Modele_tranche->get_privilege();
		if (is_null($privilege))
			echo 'Erreur - '.$erreur;
		else
			echo $privilege;
	}
	
	function logout() {
		$this->session->unset_userdata('user');
		$this->session->unset_userdata('pass');
		$this->session->unset_userdata('mode_expert');
	}
	
	function index()
	{
		$this->load->helper('url');
        $this->session->set_userdata('id_modele', null);
		
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		
		$privilege=$this->Modele_tranche->get_privilege();
		
		global $erreur;
		$erreur = '';
		
		
		$data = [
				'user'=>$this->session->userdata('user'),
				'mode_expert'=>$this->session->userdata('mode_expert'),
				'just_connected'=>$this->Modele_tranche->get_just_connected(),
				'privilege' => $privilege,
				'erreur' => $erreur,
				'title' => 'EdgeCreator',
        ];
		$this->load->view('headergview',$data);
		$this->load->view('wizarddialogsview',$data);
		$this->load->view('edgecreatorgview',$data);
		$this->load->view('footerview',$data);
	}	
}