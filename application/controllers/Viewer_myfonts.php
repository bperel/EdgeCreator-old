<?php
include_once APPPATH.'controllers/Viewer_wizard.php';

class Viewer_myfonts extends EC_Controller {
    static $is_debug=false;

	function index($url,$couleur_texte,$couleur_fond,$largeur,$chaine,$demi_hauteur,$rotation,$largeur_tranche,$debug=false) {
		self::$is_debug = $debug === 'true';
		
		$this->init_model();
		$this->Modele_tranche->setUsername($this->session->userdata('user'));
				
		$options=new stdClass();
		$options->URL=$url;
		$options->Couleur_texte=$couleur_texte;
		$options->Couleur_fond=$couleur_fond;
		$options->Largeur=$largeur;
		$options->Chaine=$chaine;
		$options->Demi_hauteur=$demi_hauteur;
		$options->Rotation=$rotation === 'null' ? null : $rotation;
		
		Viewer_wizard::$largeur = (int)$largeur_tranche * 1.5;
		
		new TexteMyFonts($options,true,false,true,true,false);
		
		if (self::$is_debug===false) {
			header('Content-type: image/png');
			imagepng(Viewer_wizard::$image);
		}
	}
}
