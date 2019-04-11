<?php

class Viewer_wizard extends EC_Controller {
	static $image;
	static $largeur;
	static $hauteur;
	static $pays;
	static $magazine;
	static $numero;
	static $random_id;
	static $parametrage;
	static $fond_noir;
	static $zoom;
	static $etapes_actives= [];
	static $externe=false;
	static $is_debug=false;
	static $etape_en_cours;

    static $zoom_save = 1.5;
	
	function etape($zoom,$etapes_actives,$parametrage,$save,$fond_noir,$externe,$random_ou_username=null,$debug=false) {
        $id_modele = $this->session->userdata('id_modele');
        $pays = $this->session->userdata('pays');
        $magazine = $this->session->userdata('magazine');
        $numero = $this->session->userdata('numero');

        $this->index($id_modele, $pays, $magazine, $numero, $zoom, $etapes_actives, $parametrage, $save, $fond_noir, $externe, $random_ou_username, $debug);
    }

	function index($id_modele,$pays,$magazine,$numero,$zoom,$etapes_actives,$parametrage,$save,$fond_noir,$externe,$random_ou_username=null,$debug=false) {
		if ($etapes_actives === 'final')
			$etapes_actives='all';
		if ($etapes_actives === 'all') {
			preg_match('#^(\d+)\.#',$parametrage,$matches_num_etape_parametrage);
			if (count($matches_num_etape_parametrage) === 0)
				$num_etape_parametrage=null;
			else {
				$num_etape_parametrage=$matches_num_etape_parametrage[1];
				$parametrage=substr($parametrage,strlen($num_etape_parametrage)+1,strlen($parametrage));
			}
		}
		parse_str($parametrage,$parametrage);
		$fond_noir = $fond_noir === 'true';
		if ($save==='save')
			$zoom=self::$zoom_save;
		self::$is_debug=$debug;
		self::$zoom=$zoom;
		self::$externe=$externe;
		$this->load->library('email');
		$this->load->helper('url');
		
		$this->init_model();

		if (is_null($pays) || is_null($magazine)) {
			$this->load->view('errorview', ['Erreur'=>'Nombre d\'arguments insuffisant']);
			exit();
		}

        if (is_null($numero)) {
            header('Content-type: image/png');
            self::$image=imagecreatetruecolor(1, 1);
            imagepng(self::$image);
            exit();
        }

        if ($numero === 'Aucun') {
            $largeur=20;
            $hauteur=250;
            self::$image=imagecreatetruecolor(z($largeur), z($hauteur));
            $blanc=imagecolorallocate(self::$image, 255,255,255);
            imagefill(self::$image,0,0,$blanc);
            $noir=imagecolorallocate(self::$image, 0,0,0);
            imagettftext(self::$image,z(10),-90,
                         z(5),z(5),
                         $noir,BASEPATH.'fonts/Arial.TTF','Aucun numero selectionne');
            $dimensions=new stdClass();
            $dimensions->Dimension_x=$largeur;
            $dimensions->Dimension_y=$hauteur;
            new Dessiner_contour($dimensions);

            header('Content-type: image/png');
            imagepng(self::$image);
            exit();
        }
        self::$pays=$pays;
		self::$magazine=$magazine;
		self::$random_id=$random_ou_username;
        self::$numero=$numero;
		if ($externe === 'true') {
			if (!self::$is_debug) {
				header('Content-type: image/png');
			}
			$image_externe=imagecreatefrompng('https://edges.ducksmanager.net/edges/'.self::$pays.'/gen/'.self::$magazine.'.'.self::$numero.'.png');
            $largeur_externe = imagesx($image_externe);
            $hauteur_externe = imagesy($image_externe);
            $largeur_preview = $largeur_externe * (self::$zoom/self::$zoom_save);
            $hauteur_preview = $hauteur_externe * (self::$zoom/self::$zoom_save);
            $image_externe_zoom_adapte = imagecreatetruecolor($largeur_preview, $hauteur_preview);
            imagecopyresized($image_externe_zoom_adapte, $image_externe, 0, 0, 0, 0, $largeur_preview, $hauteur_preview, $largeur_externe, $hauteur_externe);
			imagepng($image_externe_zoom_adapte);
		}
		else {
            $this->Modele_tranche->setPays(self::$pays);
            $this->Modele_tranche->setMagazine(self::$magazine);
            $this->Modele_tranche->setRandomId($random_ou_username);
            if (strpos($save,'integrate') !== false) {
                $username_modele=substr($save,strlen('integrate_'));
                $this->Modele_tranche->setUsername($username_modele);
            }
            else
                $this->Modele_tranche->setUsername($this->session->userdata('user'));
            self::$parametrage=$parametrage;
            self::$fond_noir=$fond_noir;
            self::$etapes_actives=explode('-', $etapes_actives);
            self::$etape_en_cours=[];

            if (empty($id_modele)) {
                $id_modele = $this->Modele_tranche->get_id_modele($pays,$magazine,$numero);
            }
            $etapes=$this->Modele_tranche->get_etapes_by_id($id_modele);
            $dimensions= [];

			$fond_noir_fait=false;
			try {
                foreach($etapes as $num_etape=>$nom_fonction) {
                    if ($num_etape>-1 && $fond_noir && !$fond_noir_fait) {
                        $options=new stdClass();
                        $options->Pos_x=$options->Pos_y=0;
                        $options->Couleur='000000';
                        new Remplir($options);
                        $fond_noir_fait=true;
                    }

                    if ($num_etape<0 || in_array($num_etape,self::$etapes_actives) || self::$etapes_actives === ['all']) {
                        self::$etape_en_cours['num_etape']=$num_etape;
                        self::$etape_en_cours['nom_fonction']=$nom_fonction;
                        $options2=$this->Modele_tranche->get_options_ec_v2($num_etape, false, null, null, $id_modele);
                        if ($num_etape === -1)
                            $dimensions=$options2;
                        if ((self::$etapes_actives === ['all'] && ($num_etape_parametrage == $num_etape || is_null($num_etape_parametrage)))
                            || self::$etapes_actives !== ['all']
                        ) {
                            foreach(self::$parametrage as $parametre=>$valeur) {
                                $options2->$parametre=$valeur;
                            }
                        }

                        $nom_classe = $nom_fonction;
                        if (!class_exists($nom_classe)) {
                            echo 'Etape '.$num_etape.' : La classe '.$nom_classe.' n\'existe pas';
                            exit;
                        }
                        new $nom_classe(clone $options2);
                    }
                }
			}
			catch(Exception $e) {
		    	echo 'Exception reçue : ',  $e->getMessage(), "\n";
		    	echo '<pre>';print_r($e->getTrace());echo '</pre>';
			}

			new Dessiner_contour($dimensions);
			
			$this->Modele_tranche::rendu_image($save === 'save');
		}
	}
}
