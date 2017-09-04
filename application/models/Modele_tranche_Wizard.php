<?php
include_once(APPPATH.'models/Modele_tranche.php');

class Modele_tranche_Wizard extends Modele_tranche {
	static $content_fields = ['Ordre', 'Nom_fonction', 'Option_nom', 'Option_valeur'];
	static $numero;

    function get_tranches_en_cours($id_modele=null, $pays=null, $magazine=null, $numero=null) {
        $username = self::$username;
		$requete="SELECT ID AS id, Pays AS pays, Magazine AS magazine, Numero AS numero, NomPhotoPrincipale AS nomphotoprincipale, username, 
                    (case when username = '$username' THEN '1' ELSE '0' end) AS est_editeur
				  FROM tranches_en_cours_modeles
				  WHERE (username='$username' OR photographes regexp '((^|,)$username(,|$))' ) AND Active=1";
		if (!is_null($id_modele)) {
			$requete.=' AND ID='.$id_modele;
		}
		elseif (!is_null($pays)) {
			$requete.=' AND Pays=\''.$pays.'\' AND Magazine=\''.$magazine.'\' AND Numero=\''.$numero.'\'';
		}
        $resultats = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator');
		self::assigner_noms_magazines($resultats);
		return $resultats;
	}

    function get_tranches_en_attente() {
        $resultats = DmClient::get_service_results_ec(
            DmClient::$dm_server, 'GET', "/edgecreator/v2/model/unassigned/all"
        );
        self::assigner_noms_magazines($resultats);

        return $resultats;
	}

	static function assigner_noms_magazines(&$resultats) {
        if (count($resultats) > 0) {
            $liste_magazines= array_map(function($resultat) {
                return implode('/', [$resultat->pays, $resultat->magazine]);
            }, $resultats);

            $noms_magazines = DmClient::get_service_results_ec(DmClient::$dm_server, 'GET', '/coa/list/publications', [implode(',', array_unique($liste_magazines))]);

            foreach($resultats as $resultat) {
                $publicationcode = implode('/', [$resultat->pays, $resultat->magazine]);
                $resultat->magazine_complet=$noms_magazines->{$publicationcode};
            }
        }
    }
	
	function get_ordres($pays,$magazine,$numero=null,$toutes_colonnes=false) {
		$resultats_ordres= [];
		$requete=' SELECT DISTINCT '.($toutes_colonnes?'*':'Ordre, Numero')
				.' FROM tranches_en_cours_modeles_vue'
			    .' WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\'';
		if (!is_null($numero)) {
			$requete.=' AND Numero=\''.$numero.'\'';
		}
		$requete.=' AND username = \''.self::$username.'\''
				 .' ORDER BY Ordre';
        $resultats = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator');
		foreach($resultats as $resultat) {
			$resultats_ordres[]=$toutes_colonnes?$resultat:$resultat->Ordre;
		}
		if (!$toutes_colonnes) {
			$resultats_ordres=array_unique($resultats_ordres);
		}
		return $resultats_ordres;
	}

	function get_etapes_simple() {
        $id_modele = $this->session->userdata('id_modele');

		$requete='SELECT '.implode(', ', self::$content_fields).' '
				.'FROM tranches_en_cours_modeles_vue '
			    .'WHERE ID_Modele = \''.$id_modele.'\' ';
		$requete.=' GROUP BY Ordre'
				 .' ORDER BY Ordre ';
        $resultats = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator');
		return $resultats;
	}

	function get_fonction_ec_v2($ordre, $id_modele = null) {
        $id_modele = $id_modele ?? $this->session->userdata('id_modele');
		$requete='SELECT '.implode(', ', self::$content_fields).' '
				.'FROM tranches_en_cours_modeles_vue '
                .'WHERE ID_Modele = \''.$id_modele.'\' AND Ordre='.$ordre;

        $premier_resultat = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')[0];
		return count($premier_resultat) == 0 ? null : new Fonction($premier_resultat);
	}

    function get_options_ec_v2(
        $ordre,
        $inclure_infos_options = false,
        $nouvelle_etape = false,
        $nom_option = null,
        $id_modele = null
    ) {
        if (is_null($id_modele)) {
            $id_modele = $this->session->userdata('id_modele');
        }

        $requete='SELECT '.implode(', ', self::$content_fields).' '
            .'FROM tranches_en_cours_modeles_vue '
            .'WHERE ID_Modele = \''.$id_modele.'\' AND Ordre='.$ordre.' AND Option_nom IS NOT NULL ';
        if (!is_null($nom_option))
            $requete.='AND Option_nom = \''.$nom_option.'\' ';
        $requete.='ORDER BY Option_nom ASC';

        $resultats = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator');
        $resultats_options=new stdClass();
        foreach($resultats as $resultat) {
            $resultats_options->{$resultat->Option_nom} = $resultat->Option_valeur;
        }
        $nom_fonction = $resultats[0]->Nom_fonction;
        $f=new $nom_fonction($resultats_options,false,false,!$nouvelle_etape); // Ajout des champs avec valeurs par défaut
        if ($inclure_infos_options) {
            $prop_champs=new ReflectionProperty(get_class($f), 'champs');
            $champs=$prop_champs->getValue();
            $prop_valeurs_defaut=new ReflectionProperty(get_class($f), 'valeurs_defaut');
            $valeurs_defaut=$prop_valeurs_defaut->getValue();
            $prop_descriptions=new ReflectionProperty(get_class($f), 'descriptions');
            $descriptions=$prop_descriptions->getValue();
            foreach(array_keys((array)$f->options) as $nom_option) {
                $intervalles_option=[];
                $intervalles_option['valeur']=$f->options->$nom_option;
                $intervalles_option['type']=$champs[$nom_option];
                $intervalles_option['description']=isset($descriptions[$nom_option]) ? $descriptions[$nom_option] : '';
                if (array_key_exists($nom_option, $valeurs_defaut))
                    $intervalles_option['valeur_defaut']=$valeurs_defaut[$nom_option];
                $f->options->$nom_option=$intervalles_option;
            }
        }
        return $f->options;
    }

	function has_no_option_ec_v2() {
        $id_modele = $this->session->userdata('id_modele');

		$requete='SELECT Option_nom '
				.'FROM tranches_en_cours_modeles_vue '
				.'WHERE ID_Modele = \''.$id_modele.'\' AND Option_nom IS NOT NULL';
        return count(DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')) === 0;
	}

	function decaler_etapes_a_partir_de($id_modele,$etape_debut, $inclure_cette_etape) {
        $inclure_cette_etape = $inclure_cette_etape ? 'inclusive' : 'exclusive';

        $resultat = DmClient::get_service_results_ec(
            DmClient::$dm_server, 'POST', "/edgecreator/v2/step/shift/$id_modele/$etape_debut/$inclure_cette_etape", []
        );

		return $resultat->shifts;
	}

	function valeur_existe($id_valeur) {
		$requete='SELECT ID FROM edgecreator_valeurs WHERE ID='.$id_valeur;
        return count(DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')) > 0;
	}
	
	function insert_to_modele($id_modele,$ordre,$nom_fonction,$option_nom,$option_valeur) {
		$option_nom=is_null($option_nom) ? 'NULL' : '\''.preg_replace("#([^\\\\])'#","$1\\'",$option_nom).'\'';
		$option_valeur=is_null($option_valeur) ? 'NULL' : '\''.preg_replace("#([^\\\\])'#","$1\\'",$option_valeur).'\'';

		$requete='INSERT INTO tranches_en_cours_valeurs (ID_Modele,Ordre,Nom_fonction,Option_nom,Option_valeur) VALUES '
				.'('.$id_modele.','.$ordre.',\''.$nom_fonction.'\','.$option_nom.','.$option_valeur.') ';
        DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator');
	}
	
	function get_id_modele($pays,$magazine,$numero,$username=null) {
		if (is_null($username)) {
			$username = self::$username;
		}
		$requete='SELECT ID FROM tranches_en_cours_modeles '
				.'WHERE Pays=\''.$pays.'\' AND Magazine=\''.$magazine.'\' AND Numero=\''.$numero.'\'';
		if (!is_null($username)) {
			$requete.=' AND username=\''.$username.'\'';
		}
        $resultat = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')[0];
		return $resultat->ID;
	}
	
	function get_nom_fonction($id_modele,$ordre) {
		$requete='SELECT Nom_fonction FROM tranches_en_cours_valeurs '
				.'WHERE ID_Modele='.$id_modele.' AND Ordre='.$ordre;
        $resultat = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')[0];
		return $resultat->Nom_fonction;
	}
	
	function creer_modele($pays, $magazine, $numero) {
        $est_editeur = in_array($this->get_privilege(), ['Edition', 'Admin']) ? '1' : '0';
        DmClient::get_service_results_ec(
            DmClient::$dm_server,
            'PUT',
            "/edgecreator/v2/model/$pays/$magazine/$numero/$est_editeur"
        );
	}
	
	function get_photo_principale() {
        $id_modele = $this->session->userdata('id_modele');
        if (is_null($id_modele)) {
            return null;
        }
        else {
            $requete="SELECT NomPhotoPrincipale FROM tranches_en_cours_modeles
                      WHERE ID=".$id_modele;
            $resultat = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')[0];
            return $resultat->NomPhotoPrincipale;
        }
	}

	function insert_etape($pos_relative, $etape, $nom_fonction) {
        $id_modele = $this->session->userdata('id_modele');
		$inclure_avant = $pos_relative==='avant' || $etape === -1;
		$infos=new stdClass();

		if ($etape > -1) {
		    $infos->decalages=$this->decaler_etapes_a_partir_de($id_modele,$etape, $inclure_avant);
        }
        else {
		    $infos->decalages = [];
        }
		
		$nouvelle_fonction=new $nom_fonction(false, null, true);
		$numero_etape=$inclure_avant ? $etape : $etape+1;

        DmClient::get_service_results_ec(
            DmClient::$dm_server,
            'POST',
            "/edgecreator/v2/step/$id_modele/$numero_etape",
            [
                'stepfunctionname' => $nom_fonction,
                'options' => $nouvelle_fonction->options
            ]
        );

		$infos->numero_etape=$numero_etape;
		return $infos;
	}

	function update_etape($etape,$parametrage) {
        $id_modele = $this->session->userdata('id_modele');

        DmClient::get_service_results_ec(
            DmClient::$dm_server,
            'POST',
            "/edgecreator/v2/step/$id_modele/$etape",
            ['options' => $parametrage]
        );
    }
	
	function update_photo_principale($nom_photo_principale) {
        $id_modele = $this->session->userdata('id_modele');
        $username = $this->session->userdata('user');

        DmClient::get_service_results_ec(
            DmClient::$dm_server,
            'PUT',
            "/edgecreator/model/v2/$id_modele/photo/main", [
                'photoname' => $nom_photo_principale,
                'username' => $username
            ]
        );
	}

	function cloner_etape_numero($pos, $etape_courante) {
        $id_modele = $this->session->userdata('id_modele');

		$inclure_avant = $pos==='avant' || $pos==='_';
		$infos=new stdClass();
		
		$infos->decalages=$this->decaler_etapes_a_partir_de($id_modele,$etape_courante, $inclure_avant);
		
		$nouvelle_etape=$inclure_avant ? $etape_courante : $etape_courante+1;

        $resultat = DmClient::get_service_results_ec(DmClient::$dm_server, 'POST', "/edgecreator/v2/step/clone/$id_modele/$etape_courante/to/$nouvelle_etape", []
        );
		
		$infos->numero_etape=$nouvelle_etape;
		$infos->nom_fonction=$resultat->functionName;
		return $infos;
	}

	function supprimer_etape($etape) {
        $id_modele = $this->session->userdata('id_modele');

        DmClient::get_service_results_ec(
            DmClient::$dm_server,
            'DELETE',
            "/edgecreator/v2/step/$id_modele/$etape"
        );
	}

	function delete_option($pays,$magazine,$etape,$nom_option) {
		if ($nom_option=='Actif')
			$requete_suppr_option='DELETE modeles, valeurs, intervalles FROM edgecreator_modeles2 modeles '
								  .'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
							      .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
							      .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' '
								  .'AND Ordre='.$etape.' AND Option_nom IS NULL AND username = \''.self::$username.'\'';
		else
			$requete_suppr_option='DELETE modeles, valeurs, intervalles FROM edgecreator_modeles2 modeles '
								  .'INNER JOIN edgecreator_valeurs AS valeurs ON modeles.ID = valeurs.ID_Option '
							      .'INNER JOIN edgecreator_intervalles AS intervalles ON valeurs.ID = intervalles.ID_Valeur '
							      .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' '
								  .'AND Ordre='.$etape.' AND Option_nom = \''.$nom_option.'\' AND username = \''.self::$username.'\'';
        DmClient::get_query_results_from_dm_server($requete_suppr_option, 'db_edgecreator');
		echo $requete_suppr_option."\n";
	}
	
	function get_id_modele_tranche_en_cours_max() {
		$requete='SELECT MAX(ID) AS Max FROM tranches_en_cours_modeles';
        return DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')[0]->Max;
	}
	
	function get_id_valeur_max() {
		$requete='SELECT MAX(ID) AS Max FROM edgecreator_valeurs';
        return DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')[0]->Max;
	}

	function etendre_numero ($pays,$magazine,$numero,$nouveau_numero) {
        $options = $this->get_valeurs_options($pays,$magazine, [$numero]);

		if (count($options[$numero]) === 0) {
			echo 'Aucune option d\'étape pour '.$pays.'/'.$magazine.' '.$numero;
			return;
		}
        $id_modele = DmClient::get_service_results_ec(
            DmClient::$dm_server,
            'PUT',
            "/edgecreator/v2/model/$pays/$magazine/$nouveau_numero/1"
        )->modelid;

        foreach($options[$numero]['etapes'] as $etape => $options_etape) {
            DmClient::get_service_results_ec(
                DmClient::$dm_server,
                'POST',
                "/edgecreator/v2/step/$id_modele/$etape", [
                    'options' => $options_etape['options'],
                    'stepfunctionname' => $options_etape['nom_fonction']
                ]
            );
        }

        // TODO return model ID and non-cloned steps
		return [
            'id_modele' => $id_modele,
            'etapes_non_clonees' => []
        ];
	}

	function get_tranches_non_pretes() {
		$username = $this->session->userdata('user');
		$id_user = $this->username_to_id($username);
		$requete=" SELECT ID, Pays,Magazine,Numero"
				." FROM numeros"
				." WHERE ID_Utilisateur=".$id_user
				."   AND CONCAT(Pays,'/',Magazine,' ',Numero) NOT IN"
				."    (SELECT CONCAT(publicationcode,' ',issuenumber)"
				."   FROM tranches_pretes)"
				." ORDER BY Pays, Magazine, Numero";

		$resultats = $this->requete_select_dm($requete);

        $publication_codes = array_map(function($resultat) {
		    return $resultat['Pays'].'/'.$resultat['Magazine'];
        }, $resultats);

        $noms_magazines = DmClient::get_service_results_ec(
            DmClient::$dm_server,
            'GET',
            '/coa/list/publications',
            [implode(',', array_unique($publication_codes))]
        );

		foreach($resultats as &$resultat) {
			$resultat['Magazine_complet'] = $noms_magazines[$resultat['Pays'].'/'.$resultat['Magazine']];
		}
		
		return $resultats;
	}
	
	function desactiver_modele() {
        $id_modele = $this->session->userdata('id_modele');

        DmClient::get_service_results_ec(DmClient::$dm_server, 'POST', "/edgecreator/model/v2/$id_modele/deactivate");
	}

    function prepublier_modele( $prepublier_ou_depublier) {
        $id_modele = $this->session->userdata('id_modele');

        $requete_prepublication=' UPDATE tranches_en_cours_modeles '
            .' SET PretePourPublication='.($prepublier_ou_depublier ? '1' : '0')
            .' WHERE ID='.$id_modele;
        DmClient::get_query_results_from_dm_server($requete_prepublication, 'db_edgecreator');
    }

    public function ajouter_photo_tranches_multiples($nomFichier, $hash)
    {
        $photos_jour = DmClient::get_service_results_ec(
            DmClient::$dm_server,
            'PUT',
            '/edgecreator/multiple_edge_photo', [
                'hash' => $hash,
                'filename' => $nomFichier
        ]);

        return count($photos_jour) > 10;
    }

    public function est_limite_photos_atteinte()
    {
        $photos_jour = DmClient::get_service_results_ec(
            DmClient::$dm_server,
            'GET',
            '/edgecreator/multiple_edge_photo/today'
        );

        return count($photos_jour) > 10;
    }

    public function get_photo_existante($hash)
    {
        $photos_existante = DmClient::get_service_results_ec(
            DmClient::$dm_server,
            'GET',
            "/edgecreator/multiple_edge_photo/hash/$hash"
        );

        return $photos_existante;
    }

    function copier_image_temp_vers_gen($nom_image) {
        $id_modele = $this->session->userdata('id_modele');

        $model = DmClient::get_service_results_ec(DmClient::$dm_server, 'GET', "/edgecreator/v2/model/$id_modele");

        $src_image =  self::getCheminImages().'/' . $model->pays . '/tmp/' . $nom_image . '.png';
        $dest_image = self::getCheminImages().'/' . $model->pays . '/gen/' . $model->magazine . '.' . $model->numero . '.png';
        @mkdir(self::getCheminImages().'/' . $model->pays . '/tmp');
        copy($src_image, $dest_image);
    }
	
	function marquer_modele_comme_pret_publication($createurs,$photographes) {
        $id_modele = $this->session->userdata('id_modele');

        DmClient::get_service_results_ec(DmClient::$dm_server, 'POST', "/edgecreator/model/v2/$id_modele/readytopublish/1", [
            'photographers' => explode(',', $photographes),
            'designers' => explode(',', $createurs)
        ]);
    }
	
	function get_couleurs_frequentes() {
        $id_modele = $this->session->userdata('id_modele');
		$couleurs= [];
		$requete= ' SELECT DISTINCT Option_valeur'
				 .' FROM tranches_en_cours_modeles_vue'
				 .' WHERE ID_Modele='.$id_modele.' AND Option_nom LIKE \'Couleur%\'';
        $resultats = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator');
		foreach($resultats as $resultat) {
			$couleurs[]=$resultat->Option_valeur;
		}
		return $couleurs;
	}
	
	function get_couleur_point_photo($frac_x,$frac_y) {
        $id_modele = $this->session->userdata('id_modele');
		$requete_nom_photo = ' SELECT NomPhotoPrincipale, Pays'
							.' FROM tranches_en_cours_modeles'
							.' WHERE ID='.$id_modele;
        $resultat_nom_photo = DmClient::get_query_results_from_dm_server($requete_nom_photo, 'db_edgecreator')[0];
		
		$chemin_photos = Fonction_executable::getCheminPhotos($resultat_nom_photo->Pays);
		$chemin_photo_tranche = $chemin_photos.'/'.$resultat_nom_photo->NomPhotoPrincipale;
		$image = imagecreatefromjpeg($chemin_photo_tranche);
		list($width, $height) = getimagesize($chemin_photo_tranche);
		
		$rgb = imagecolorat($image, $frac_x*$width, $frac_y*$height);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;
		return rgb2hex($r,$g,$b);
	}
}
?>