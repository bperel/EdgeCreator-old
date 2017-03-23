<?php
include_once(BASEPATH.'../application/models/Modele_tranche.php');

class Modele_tranche_Wizard extends Modele_tranche {
	static $content_fields = ['Ordre', 'Nom_fonction', 'Option_nom', 'Option_valeur'];
	static $numero;

    function get_tranches_en_cours($id=null,$pays=null,$magazine=null,$numero=null) {
		$requete='SELECT ID, Pays, Magazine, Numero, username '
				.'FROM tranches_en_cours_modeles '
				.'WHERE username=\''.self::$username.'\' AND Active=1';
		if (!is_null($id)) {
			$requete.=' AND ID='.$id;
		}
		elseif (!is_null($pays)) {
			$requete.=' AND Pays=\''.$pays.'\' AND Magazine=\''.$magazine.'\' AND Numero=\''.$numero.'\'';
		}
        $resultats = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator');

        $liste_magazines= [];
		foreach($resultats as $resultat) {
			$resultat->Magazine_complet='';
            $publicationcode = implode('/', [$resultat->Pays, $resultat->Magazine]);
            if (!in_array($publicationcode, $liste_magazines))
				$liste_magazines[]=$publicationcode;
		}

        $noms_magazines = DmClient::get_service_results(DmClient::$dm_server, 'GET', '/coa/list/publications', [implode(',', array_unique($liste_magazines))]);

        foreach($resultats as $resultat) {
            $publicationcode = implode('/', [$resultat->Pays, $resultat->Magazine]);
            $resultat->Magazine_complet=$noms_magazines->{$publicationcode};
        }
		return $resultats;
	}
	
	function get_ordres($pays,$magazine,$numero=null,$toutes_colonnes=false) {
		$resultats_ordres= [];
		$requete=' SELECT DISTINCT '.($toutes_colonnes?'*':'Ordre, Numero')
				.' FROM tranches_en_cours_modeles_vue'
			    .' WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\'';
		if (!is_null($numero)) {
			$requete.=' AND Numero=\''.$numero.'\'';
		}
		$requete.=' AND username = \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\''
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

	function get_etapes_simple($pays,$magazine,$numero,$num_etape=null) {
		$requete='SELECT '.implode(', ', self::$content_fields).' '
				.'FROM tranches_en_cours_modeles_vue '
			    .'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero = \''.$numero.'\' '
				.'AND username = \''.self::$username.'\' ';
		if (!is_null($num_etape))
			$requete.='AND Ordre='.$num_etape.' ';
		$requete.=' GROUP BY Ordre'
				 .' ORDER BY Ordre ';
        $resultats = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator');
		return $resultats;
	}

	function get_fonction($pays,$magazine,$ordre,$numero=null) {
	    if (is_null($numero)) {
	        return [];
        }
		$requete='SELECT '.implode(', ', self::$content_fields).' '
				.'FROM tranches_en_cours_modeles_vue '
				.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Ordre='.$ordre.' '
				.'AND username = \''.self::$username.'\' '
				.'AND Numero=\''.$numero.'\'';

        $premier_resultat = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')[0];
		return count($premier_resultat) == 0 ? null : new Fonction($premier_resultat);
	}

	function get_options(
        $pays,
        $magazine,
        $ordre,
        $nom_fonction,
        $numero = null,
        $inclure_infos_options = false,
        $nouvelle_etape = false,
        $nom_option = null
    ) {
		$numero=false;
		$requete='SELECT '.implode(', ', self::$content_fields).' '
				.'FROM tranches_en_cours_modeles_vue '
				.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Numero = \''.$nom_fonction.'\' AND Ordre='.$ordre.' AND Option_nom IS NOT NULL '
				.'AND username = \''.($this->user_possede_modele() ? self::$username : 'brunoperel').'\' ';
		if (!is_null($nom_option))
			$requete.='AND Option_nom = \''.$nom_option.'\' ';
		$requete.='ORDER BY Option_nom ASC';

        $resultats = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator');
		$resultats_options=new stdClass();
		foreach($resultats as $resultat) {
			$nom_fonction=$resultat->Nom_fonction;
			$option_nom=$resultat->Option_nom;
			$valeur=$resultat->Option_valeur;
			$resultats_options->$option_nom=$valeur;
		}
		$f=new $nom_fonction($resultats_options,false,$numero,!$inclure_infos_options); // Ajout des champs avec valeurs par défaut
		if ($inclure_infos_options) {
			$prop_champs=new ReflectionProperty(get_class($f), 'champs');
			$champs=$prop_champs->getValue();
			$prop_valeurs_defaut=new ReflectionProperty(get_class($f), 'valeurs_defaut');
			$valeurs_defaut=$prop_valeurs_defaut->getValue();
			$prop_descriptions=new ReflectionProperty(get_class($f), 'descriptions');
			$descriptions=$prop_descriptions->getValue();
			foreach(array_keys((array)$f->options) as $nouvelle_etape) {
				$intervalles_option= [];
				$intervalles_option['valeur']=$f->options->$nouvelle_etape;
				$intervalles_option['type']=$champs[$nouvelle_etape];
				$intervalles_option['description']=isset($descriptions[$nouvelle_etape]) ? $descriptions[$nouvelle_etape] : '';
				if (array_key_exists($nouvelle_etape, $valeurs_defaut))
					$intervalles_option['valeur_defaut']=$valeurs_defaut[$nouvelle_etape];
				$f->options->$nouvelle_etape=$intervalles_option;
			}
		}
		return $f->options;
	}

	function has_no_option($pays, $magazine) {
		$requete='SELECT Option_nom '
				.'FROM tranches_en_cours_modeles_vue '
				.'WHERE Pays = \''.$pays.'\' AND Magazine = \''.$magazine.'\' AND Option_nom IS NOT NULL '
				.'AND username = \''.self::$username.'\'';
        return count(DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')) === 0;
	}

	function decaler_etapes_a_partir_de($pays,$magazine,$numero,$etape_debut, $inclure_cette_etape) {
        $inclure_cette_etape = $inclure_cette_etape ? 'inclusive' : 'exclusive';

        $resultat = DmClient::get_service_results(
            DmClient::$dm_server,
            'POST',
            "/edgecreator/step/shift/$pays/$magazine/$numero/$etape_debut/$inclure_cette_etape",
            [],
            'edgecreator'
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
			$requete.=' AND username=\''.$username.'\' AND Active=1';
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
		$requete='INSERT INTO tranches_en_cours_modeles (Pays, Magazine, Numero, username, Active) '
				.'VALUES (\''.$pays.'\',\''.$magazine.'\',\''.$numero.'\',\''.self::$username.'\', 1)';
        DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator');
		echo $requete."\n";
	}
	
	function get_photo_principale($pays,$magazine,$numero) {
		$requete='SELECT NomPhotoPrincipale FROM tranches_en_cours_modeles '
				.'WHERE Pays=\''.$pays.'\' AND Magazine=\''.$magazine.'\' AND Numero=\''.$numero.'\'';
        $resultat = DmClient::get_query_results_from_dm_server($requete, 'db_edgecreator')[0];
		return $resultat->NomPhotoPrincipale;
	}

	function insert_etape($pays, $magazine, $numero, $pos_relative, $etape, $nom_fonction) {
		$inclure_avant = $pos_relative==='avant' || $etape === -1;
		$id_modele=$this->get_id_modele($pays,$magazine,$numero,self::$username);
		$infos=new stdClass();

		if ($etape > -1) {
		    $infos->decalages=$this->decaler_etapes_a_partir_de($pays,$magazine,$numero,$etape, $inclure_avant);
        }
		
		$nouvelle_fonction=new $nom_fonction(false, null, true);
		$numero_etape=$inclure_avant ? $etape : $etape+1;
		foreach($nouvelle_fonction->options as $nom=>$valeur) {
			$this->insert_to_modele($id_modele, $numero_etape, $nom_fonction, $nom, $valeur);
		}
		$infos->numero_etape=$numero_etape;
		return $infos;
	}

	function update_etape($pays,$magazine,$numero,$etape,$parametrage) {
        DmClient::get_service_results(
            DmClient::$dm_server,
            'POST',
            "/edgecreator/v2/step/$pays/$magazine/$numero/$etape",
            ['options' => $parametrage],
            'edgecreator'
        );
    }
	
	function update_photo_principale($pays,$magazine,$numero,$nom_photo_principale) {
		$id_modele=$this->get_id_modele($pays,$magazine,$numero,self::$username);
		
		$requete_maj='UPDATE tranches_en_cours_modeles '
					.'SET NomPhotoPrincipale=\''.$nom_photo_principale.'\' '
					.'WHERE ID='.$id_modele;
        DmClient::get_query_results_from_dm_server($requete_maj, 'db_edgecreator');
		echo $requete_maj."\n";
	}

	function cloner_etape_numero($pays,$magazine,$numero,$pos,$etape_courante) {
		$inclure_avant = $pos==='avant' || $pos==='_';
		$infos=new stdClass();
		
		$infos->decalages=$this->decaler_etapes_a_partir_de($pays,$magazine,$numero,$etape_courante, $inclure_avant);
		
		$nouvelle_etape=$inclure_avant ? $etape_courante : $etape_courante+1;

        $resultat = DmClient::get_service_results(DmClient::$dm_server, 'POST',
            "/edgecreator/step/clone/$pays/$magazine/$numero/$etape_courante/to/$nouvelle_etape",
            [],
            'edgecreator'
        );
		
		$infos->numero_etape=$nouvelle_etape;
		$infos->nom_fonction=$resultat->functionName;
		return $infos;
	}

	function supprimer_etape($pays,$magazine,$numero,$etape) {
		$requete_suppr='DELETE FROM tranches_en_cours_valeurs '
					  .'WHERE ID_Modele=(SELECT m.ID FROM tranches_en_cours_modeles m '
					  				   .'WHERE m.Pays = \''.$pays.'\' AND m.Magazine = \''.$magazine.'\' AND m.Numero = \''.$numero.'\' AND m.Active=1) '
					  	.'AND Ordre = \''.$etape.'\'';
        DmClient::get_query_results_from_dm_server($requete_suppr, 'db_edgecreator');
		echo $requete_suppr."\n";
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
		
		$requete_ajout_modele='INSERT INTO tranches_en_cours_modeles (Pays, Magazine, Numero, username, Active) '
							 .'VALUES (\''.$pays.'\',\''.$magazine.'\',\''.$nouveau_numero.'\','
							 .'\''.self::$username.'\', 1)';
        DmClient::get_query_results_from_dm_server($requete_ajout_modele, 'db_edgecreator');
		$id_modele=$this->get_id_modele_tranche_en_cours_max();
		
		foreach($options[$numero] as $option) {
			$requete_ajout_valeur=' INSERT INTO tranches_en_cours_valeurs (ID_Modele, Ordre, Nom_fonction, Option_nom, Option_valeur)'
								 .' VALUES ('.$id_modele .',\''.$option->Ordre.'\',\''.$option->Nom_fonction.'\','
								 .' '.$option->Option_nom.','.$option->Option_valeur.')';
            DmClient::get_query_results_from_dm_server($requete_ajout_valeur, 'db_edgecreator');
		}
		
		// Suppression des étapes incomplètes = étapes dont le nombre d'options est différent de celui défini
		
		foreach(self::$noms_fonctions as $nom_fonction) {
			$champs_obligatoires = array_diff(array_keys($nom_fonction::$champs), array_keys($nom_fonction::$valeurs_defaut));
			
			$requete_nettoyage = ' SELECT Ordre, Option_nom'
								.' FROM tranches_en_cours_modeles_vue'
								.' WHERE ID_Modele='.$id_modele.' AND Nom_fonction=\''.$nom_fonction.'\''
								.' ORDER BY Ordre';
            $resultats = DmClient::get_query_results_from_dm_server($requete_nettoyage, 'db_edgecreator');
			$etapes_et_options= [];
			foreach($resultats as $resultat) {
				if (!array_key_exists($resultat->Ordre, $etapes_et_options)) {
					$etapes_et_options[$resultat->Ordre]= [];
				}
				$etapes_et_options[$resultat->Ordre][]=$resultat->Option_nom;
				echo "Etape ".$resultat->Ordre.', option '.$resultat->Option_nom."\n";
			}
			
			foreach($etapes_et_options as $etape=>$options) {
				$champs_obligatoires_manquants = array_diff($champs_obligatoires, $options);
				if (count($champs_obligatoires_manquants) > 0) {
					echo utf8_encode("\nEtape $etape : l'étape sera supprimée car les champs suivants ne sont pas renseignés : "
									 .implode(', ', $champs_obligatoires_manquants)."\n");
					$requete_suppression_etape=' DELETE FROM tranches_en_cours_valeurs'
											  .' WHERE ID_Modele='.$id_modele.' AND Ordre='.$etape;
                    DmClient::get_query_results_from_dm_server($requete_suppression_etape, 'db_edgecreator');
				}
			}
		}		
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
		
		$country_codes= [];
		$publication_codes= [];
		foreach($resultats as $resultat) {
            $country_codes[]=$resultat['Pays'];
			$publication_codes[]=$resultat['Pays'].'/'.$resultat['Magazine'];
		}

        $noms_magazines = DmClient::get_service_results(DmClient::$dm_server, 'GET', '/coa/list/publications', [implode(',', array_unique($publication_codes))]);

		foreach($resultats as &$resultat) {
			$resultat['Magazine_complet'] = $noms_magazines[$resultat['Pays'].'/'.$resultat['Magazine']];
		}
		
		return $resultats;
	}
	
	function desactiver_modele($pays,$magazine,$numero) {
		$id_modele=$this->get_id_modele($pays,$magazine,$numero,self::$username);
		
		$requete_maj=' UPDATE tranches_en_cours_modeles '
					.' SET Active=0'
					.' WHERE ID='.$id_modele;
        DmClient::get_query_results_from_dm_server($requete_maj, 'db_edgecreator');
		echo $requete_maj."\n";
	}

    function prepublier_modele($id_modele, $prepublier_ou_depublier) {
        $requete_prepublication=' UPDATE tranches_en_cours_modeles '
            .' SET PretePourPublication='.($prepublier_ou_depublier ? '1' : '0')
            .' WHERE ID='.$id_modele;
        DmClient::get_query_results_from_dm_server($requete_prepublication, 'db_edgecreator');
    }

    function copier_image_temp_vers_gen($pays, $magazine, $numero, $nom_image) {
        $src_image = '../edges/' . $pays . '/tmp/' . $nom_image . '.png';
        $dest_image = '../edges/' . $pays . '/gen/' . $magazine . '.' . $numero . '.png';
        copy($src_image, $dest_image);
    }
	
	function marquer_modele_comme_pret_publication($pays,$magazine,$numero,$createurs,$photographes) {
        $id_modele = $this->get_id_modele($pays,$magazine,$numero,self::$username);

        $requete_maj=' UPDATE tranches_en_cours_modeles '
                    .' SET PretePourPublication=1, createurs=\''.$createurs.'\', photographes=\''.$photographes.'\''
                    .' WHERE ID='.$id_modele;
        DmClient::get_query_results_from_dm_server($requete_maj, 'db_edgecreator');
        echo '<br />'.$requete_maj."\n";
    }
	
	function get_couleurs_frequentes($id_modele) {
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
	
	function get_couleur_point_photo($pays,$magazine,$numero,$frac_x,$frac_y) {
		$id_modele=$this->get_id_modele($pays,$magazine,$numero);
		$requete_nom_photo = ' SELECT NomPhotoPrincipale'
							.' FROM tranches_en_cours_modeles'
							.' WHERE ID='.$id_modele;
        $resultat_nom_photo = DmClient::get_query_results_from_dm_server($requete_nom_photo, 'db_edgecreator')[0];
		
		$chemin_photos = Fonction_executable::getCheminPhotos($pays);
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