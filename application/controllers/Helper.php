<?php
class Helper extends EC_Controller {
	
	function index($nom=null) {
		
		if (in_array(null, [$nom])) {
			$this->load->view('errorview', ['Erreur'=>'Nombre d\'arguments insuffisant']);
			exit();
		}
		$this->load->model($this->session->userdata('mode_expert') === true ? 'Modele_tranche' : 'Modele_tranche_Wizard','Modele_tranche');
		
		
		$privilege=$this->Modele_tranche->get_privilege();
		
		//echo '<div style="display:none">'.$this->session->userdata('user').','.$this->session->userdata('pass').'</div>';
//		if ($privilege == 'Affichage') {
//			echo 'Vous n\'avez pas les permissions suffisantes pour r&eacute;aliser cette action !';
//			return;
//		}
		
		ob_start();
		include_once('helpers/'.$nom);
		$contenu=ob_get_clean();
		
		$data= ['contenu'=>$contenu];
		
		$this->load->view('helperview',$data);
		
	}
}