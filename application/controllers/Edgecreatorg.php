<?php
class EdgeCreatorg extends Ec_Controller {
	function login() {
		$this->load->model('Modele_tranche_Wizard','Modele_tranche');

		$this->logout();

		global $erreur;$erreur='';

		$privilege=$this->Modele_tranche->get_privilege();
		if (!is_null($privilege)) {
			echo $privilege;
        }
	}

	function logout() {
		$this->session->unset_userdata('user');
		$this->session->unset_userdata('pass');
	}

	function index()
	{
		$this->load->helper('url');
        $this->session->set_userdata('id_modele', null);

		$this->load->model('Modele_tranche_Wizard','Modele_tranche');

		$privilege=$this->Modele_tranche->get_privilege();

		$data = [
				'user'=>$this->session->userdata('user'),
				'just_connected'=>$this->Modele_tranche->get_just_connected(),
				'privilege' => $privilege,
				'title' => 'EdgeCreator',
        ];
		$this->load->view('headergview',$data);
		$this->load->view('wizarddialogsview',$data);
		$this->load->view('edgecreatorgview',$data);
		$this->load->view('footerview',$data);
	}
}
