<?php

class Valider_Modele extends EC_Controller {

	function index($nom_image,$createurs,$photographes) {
		$this->init_model();
		$this->load->library('email');
		$this->load->helper('email');
        $username=$this->session->userdata('user');
		$this->Modele_tranche->setUsername($username);

        $this->Modele_tranche->copier_image_temp_vers_gen($nom_image);
		
		$message=" <a href=\"http://www.ducksmanager.net/edges/new_edges_query_to_forum_changelog.php\">Publication des tranches</a>";

//        $config['send_multipart'] = FALSE;
//        $config['protocol'] = 'smtp';
//        $config['smtp_auth'] = true;
//        $config['smtp_host'] = 'smtp.1and1.com';
//        $config['smtp_port'] = '25';
//        $config['smtp_crypto'] = 'tls';
//
//        $this->email->initialize($config);
//        $this->email->set_mailtype("html");
//        $this->email->from(get_admin_email(), 'DucksManager - '.$username);
//		$this->email->to(get_admin_email());
//
//		$this->email->subject('Proposition de modele de tranche de '.$username);
//		$this->email->message($message);
//		$this->email->send();

        $this->Modele_tranche->marquer_modele_comme_pret_publication($createurs,$photographes);
	}
}