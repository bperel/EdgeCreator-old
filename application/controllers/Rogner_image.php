<?php
class Rogner_Image extends EC_Controller {
	
	function index($pays=null,$magazine=null,$numero_original=null,$numero,$nom=null,$source=null,$destination=null,
				   $x1=null,$x2=null,$y1=null,$y2=null) {
		if (in_array(null,array($pays,$magazine,$numero,$nom,$source,$destination,$x1,$x2,$y1,$y2))) {
			$this->load->view('errorview',array('Erreur'=>'Nombre d\'arguments insuffisant'));
			exit();
		}
		
		$this->db->query('SET NAMES UTF8');
		$this->init_model();
		
		new Rogner($pays, $magazine, $numero_original, $numero, $nom, $source, $destination, $x1, $x2, $y1, $y2);
	}
}