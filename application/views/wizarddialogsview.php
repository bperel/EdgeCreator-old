<div id="wizard-accueil" class="first wizard" title="Bienvenue sur EdgeCreator !">
	<p>
		<img src="images/logo_petit.png" />
	</p>
</div>
<div id="wizard-accueil2" class="wizard" title="Bienvenue sur EdgeCreator !">
	<p>
		L'objectif d'EdgeCreator est de créer des images de tranches.
		<br />
		L'image d'une tranche que vous créerez apparaîtra ensuite 
		dans la section "Ma bibliothèque" de tous les utilisateurs de DucksManager 
		possédant le numéro correspondant.
		<br />
		<img style="height:300px" src="images/construction_tranche.png" />
	</p>
</div>

<div id="wizard-accueil3" class="wizard" title="Bienvenue sur EdgeCreator !">
	<p>
		Pour créer une tranche, vous aurez besoin :<br />
		<div style="float: left;width:50%">
			<img style="width: 100%" src="images/regle.png" />
			D'une règle
		</div>
		<div style="float: left;width:50%">
			<img style="width: 100%" src="images/appareil_photo.png" />
			D'un scanner ou un appareil photo (les capteurs photo des téléphones donnent parfois des photos floues)
		</div>
		
	</p>
</div>

<div id="login-form" class="wizard" title="Connexion à EdgeCreator">
	<p>
		Entrez vos identifiants DucksManager habituels ci-dessous et cliquez sur "Connexion".
	</p>
	<p class="erreurs"></p>
	<form>
		<fieldset>
			<label for="username">Pseudo: </label>
			<input type="text" name="username" id="username" class="text ui-widget-content ui-corner-all" />
			<label for="password">Mot de passe: </label>
			<input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all" />
			<br />
		</fieldset>
	</form>
</div>

<div id="wizard-1" class="first wizard" title="Accueil EdgeCreator">
	<p>
		Vous êtes à présent connecté(e) sur EdgeCreator.
	</p>
	<p>
		Commençons par le début... Que voulez-vous faire ?<br />
		<form>
			<div class="buttonset">
				<input type="radio" name="choix" value="to-wizard-envoyer-photo" id="to-wizard-envoyer-photo" />
				<label class="toutes_bordures" for="to-wizard-envoyer-photo">Envoyer des photos de tranche</label><br />
				
				<div></div>
				
				<input type="radio" name="choix" value="to-wizard-creer" id="to-wizard-creer" />
				<label class="toutes_bordures" for="to-wizard-creer">Créer une tranche de magazine</label><br />
				<input type="radio" name="choix" value="to-wizard-modifier" id="to-wizard-modifier"/>
				<label class="toutes_bordures" for="to-wizard-modifier">Modifier une tranche de magazine</label><br />
				<input type="radio" name="choix" value="to-wizard-conception" id="to-wizard-conception"/>
				<label class="bordure_gauche" for="to-wizard-conception">Poursuivre une conception de tranche</label>
    			<button id="selectionner_tranche_en_cours">Sélectionnez une tranche</button>
			</div>
			<ul id="tranches_en_cours" class="liste_numeros cache">
                <div name="tranches_non_affectees">Tranches non affectées :</div>
                <div name="tranches_affectees">Tranches en cours de conception par vous :</div>
				<li class="template">
					<input type="radio" id="numero_tranche_en_cours" name="choix_tranche_en_cours">
					<label for="numero_tranche_en_cours" class="toutes_bordures libelle_tranche_en_cours">Label</label>
                    <label class="prepublier">Pré-publier</label><label class="depublier cache">Dé-publier</label>
				</li>
			</ul>
            <input type="hidden" name="est_nouvelle_conception_tranche" />
		</form>
	</p>
</div>


	<div id="wizard-envoyer-photo" class="wizard" title="Assistant DucksManager - Envoi de photo">
		<p>
            <form>
                <input type="hidden" name="choix" value="to-wizard-decouper-photo" id="to-wizard-decouper-photo" />
            </form>
			Envoyez une photo contenant une ou plusieurs tranches à l'aide du formulaire ci-dessous.<br />
			Les tranches doivent apparaître verticales sur la photo.<br /><br />
			La photo doit être nette, bien éclairée, et les couleurs fidèles aux tranches originales.

			<iframe src="<?=base_url()?>index.php/helper/index/image_upload.php?photo_tranche=1&multiple"></iframe>
		</p>
	</div>


    <div id="wizard-decouper-photo" class="wizard extensible" title="Assistant DucksManager - Envoi de photo">
        <p>
            Sélectionnez avec la souris les zones de la photo correspondant à chaque tranche.<br />
            Déplacez et redimensionnez les zones et positionnez une zone par tranche.<br />
            <a href="javascript:void(0)" id="ajouter_zone_photo_multiple">Ajouter une zone</a>
            <br />
            <div id="zone_selection_tranches_multiples">
                <img id="image_tranche_multiples"/>
                <div class="rectangle_selection_tranche template">
                    <div class="edition_numero_tranche cache">
                        <span class="zone_intitule_numero">
                            <img class="edition" src="images/modifier.png" title="Modifier le magazine correspondant à cette tranche"/>
                            <span class="intitule_numero">
                                <span class="renseigne cache"></span>
                                <span class="non_renseigne">
                                    Cliquez ici pour modifier le numéro de la tranche sélectionnée
                                </span>
                            </span>
                        </span>
                        <br />
                        <img class="suppression" src="images/supprimer.png" title="Supprimer cette zone"/>
                    </div>
                </div>
            </div>
            <form>
                <input type="hidden" name="choix" value="to-wizard-confirmation-photo-multiple" id="to-wizard-confirmation-photo-multiple" />
            </form>
        </p>
    </div>

        <div id="wizard-selectionner-numero-photo-multiple" class="wizard first closeable" title="Assistant DucksManager - Choix de numéro">
            <p>
                Choisissez le numéro dont vous avez sélectionné la tranche.<br />
                <form>
                    <fieldset>
                        <label for="wizard_pays_photo_multiple">Pays: </label>
                        <select name="wizard_pays" id="wizard_pays_photo_multiple">
                            <option>Chargement...</option>
                        </select><br />
                        <label for="wizard_magazine_photo_multiple">Magazine: </label>
                        <select name="wizard_magazine" id="wizard_magazine_photo_multiple">
                            <option>Chargement...</option>
                        </select><br />
                        <label for="wizard_numero_photo_multiple">numéro: </label>
                        <select name="wizard_numero" id="wizard_numero_photo_multiple">
                            <option>Chargement...</option>
                        </select><br />
                        Les tranches sous fond vert sont déjà disponibles.
                        <br /><br />
                        Dimensions de la tranche :
                        <input type="text" name="Dimension_x" maxlength="3" size="2"> mm
                        x
                        <input type="text" name="Dimension_y" maxlength="3" size="2"> mm
                    </fieldset>
                    <input type="hidden" name="choix" value="do-in-wizard-affectation-numero-tranche" id="do-in-wizard-affectation-numero-tranche" />
                </form>
            </p>
        </div>

    <div id="wizard-confirmation-photo-multiple" class="wizard first deadend" title="Assistant DucksManager - Confirmation">
        <p>
            <span class="chargement">Veuillez patienter...</span>
            <span class="cache fin_chargement">
                Les modèles des tranches sélectionnées ontété initialisés.
                <a href="javascript:location.reload()">Retour à l'accueil</a>
            </span>
        </p>
    </div>


	<div id="wizard-creer" class="wizard" title="Assistant DucksManager - Création de tranche">
		<p>
			Possédez-vous déjà le numéro dont vous souhaitez créer la tranche 
			dans votre collection DucksManager ?
			<form>
				<div class="buttonset">
					<input type="radio" name="choix" value="to-wizard-creer-collection" id="to-wizard-creer-collection" /><label for="to-wizard-creer-collection">Oui</label>
					<input type="radio" name="choix" checked="checked" value="to-wizard-creer-hors-collection" id="to-wizard-creer-hors-collection" /><label for="to-wizard-creer-hors-collection">Non</label>
				</div>
			</form>
		</p>
	</div>
	


		<div id="wizard-creer-collection" class="wizard" title="Assistant DucksManager - Choix de numéro">
			<p>
				<span class="explication cache">Sélectionnez le numéro dont vous souhaitez créer la tranche.</span>
				<span class="chargement">Veuillez patienter...</span>
				<form>
					<ul id="tranches_non_pretes" class="liste_numeros cache">
                        <div name="tranches_non_affectees"></div>
						<li class="template">
							<input type="radio" id="numero_tranche_non_prete" name="choix_tranche">
							<label for="numero_tranche_non_prete" class="libelle_tranche_en_cours">Label</label>
						</li>
					</ul>
					<div class="buttonset cache">
						<input type="radio" checked="checked" name="choix" value="to-wizard-proposition-clonage" id="to-wizard-proposition-clonage" /><label for="to-wizard-proposition-clonage">J'ai trouvé mon numéro</label>
					</div>
				</form>
				<p class="pas_de_numero cache">Pas de numéro.</p>
			</p>
		</div>
			
		<div id="wizard-creer-hors-collection" class="wizard" title="Assistant DucksManager - Choix de numéro">
			<p>
				Choisissez le ou les numéro(s) que vous souhaitez modéliser.<br />
				<form>
					<fieldset>
						<div class="nowrap">
							<label for="wizard_pays">Pays: </label>
							<select name="wizard_pays" id="wizard_pays">
								<option>Chargement...</option>
							</select>
						</div>
						<div class="nowrap">
							<label for="wizard_magazine">Magazine: </label>
							<select name="wizard_magazine" id="wizard_magazine">
								<option>Chargement...</option>
							</select>
						</div>
						<div class="nowrap">
							<label for="wizard_numero">Numéro(s): </label>
							<select name="wizard_numero" id="wizard_numero" multiple="multiple">
								<option>Chargement...</option>
							</select>
						</div>
						<div class="clear">
							Les tranches sous fond vert sont déjà disponibles.
							Si vous souhaitez les modifier, repassez à l'écran précédent
							et choisissez "Modifier une tranche de magazine".
						</div>
					</fieldset>
					<div class="buttonset">
						<input type="radio" checked="checked" name="choix" value="to-wizard-proposition-clonage" id="to-wizard-proposition-clonage" /><label for="to-wizard-proposition-clonage">J'ai trouvé mes numéros</label>
						<input type="radio" name="choix" value="to-wizard-numero-inconnu" id="to-wizard-numero-inconnu" /><label for="to-wizard-numero-inconnu">Un numéro n'est pas dans la liste</label>
					</div>
				</form>
			</p>
		</div>
	
			<div id="wizard-dimensions" class="wizard first" title="Assistant DucksManager - Conception de la tranche">
				<p>
					<form name="form_options">
						<span id="nom_complet_numero"></span>
						Pour concevoir la tranche du magazine, nous devons connaitre ses dimensions.<br />
						Indiquez ci-dessous l'<b>épaisseur</b> et la <b>hauteur</b> de la tranche, en millimètres.
						
						Dimensions de la tranche : 
						<input type="text" id="Nouvelle_dimension_x" name="Dimension_x" maxlength="3" size="2"> mm 
						x 
						<input type="text" id="Nouvelle_dimension_y" name="Dimension_y" maxlength="3" size="2"> mm
						<div class="buttonset cache">
							<input type="radio" checked="checked" name="choix" value="to-wizard-images" id="to-wizard-images" />
						</div>
					</form>
				</p>
			</div>	
	<div id="wizard-modifier" class="wizard" title="Assistant DucksManager - Choix de numéro">
		<p>
			Choisissez le numéro dont vous souhaitez modifier la modélisation.<br />
			<form>
				<fieldset>
					<label for="wizard_pays_modifier">Pays: </label>
					<select name="wizard_pays" id="wizard_pays_modifier">
						<option>Chargement...</option>
					</select><br />
					<label for="wizard_magazine_modifier">Magazine: </label>
					<select name="wizard_magazine" id="wizard_magazine_modifier">
						<option>Chargement...</option>
					</select><br />
					<label for="wizard_numero_modifier">Numéro: </label>
					<select name="wizard_numero" id="wizard_numero_modifier">
						<option>Chargement...</option>
					</select><br />
					Les tranches sous fond vert sont modifiables. 
					Si vous souhaitez en créer une nouvelle, repassez à l'écran précédent
					et choisissez "créer une tranche de magazine".
				</fieldset>
				<div class="buttonset cache">
					<input type="radio" checked="checked" name="choix" value="to-wizard-clonage-silencieux" id="to-wizard-clonage-silencieux" />
                    <label for="to-wizard-clonage-silencieux">J'ai trouvé mon numéro</label>
				</div>
			</form>
		</p>
	</div>
	
		<div id="wizard-proposition-clonage" class="wizard" title="Assistant DucksManager - Création">
			<p>
				Certaines tranches ont déjà été conçues pour le magazine sélectionné.
				Si l'une des tranches si-dessous est identique à la vôtre, ou bien que seules quelques couleurs ou quelques textes sont différents, sélectionnez cette tranche. 
				Sinon, cliquez sur "créer une tranche originale".
				<form>
					<div class="chargement">
						Veuillez patienter...
					</div>
					<div class="tranches_affichees_magazine"></div>
					<br />
					<div class="buttonset">
						<input type="radio" checked="checked" name="choix" value="to-wizard-clonage" id="to-wizard-clonage" />
                        <label for="to-wizard-clonage">J'ai trouvé une tranche similaire</label>
						<input type="radio" name="choix" value="to-wizard-dimensions" id="to-wizard-dimensions1" />
                        <label for="to-wizard-dimensions1">Créer une tranche originale</label>
					</div>
				</form>
			</p>
		</div>
		
			<div id="wizard-clonage" class="wizard" title="Assistant DucksManager - Clonage">
				<p>
					La tranche du numéro <span class="nouveau_numero"></span> est créée à partir du numéro <span class="numero_similaire"></span>...<br />
					Ce processus peut durer plus d'une minute dans certains cas. Veuillez patienter tant que le clonage est en cours, ne fermez pas cette fenêtre.
					<div class="loading">Clonage en cours...</div>
					<div class="done cache">Clonage terminé. Vous pouvez passer à l'étape suivante.</div>
					<form>
						<input type="hidden" checked="checked" name="choix" value="to-wizard-conception" id="to-wizard-conception2" />
					</form>
				</p>
			</div>
		
			<div id="wizard-clonage-silencieux" class="wizard" title="Assistant DucksManager - Préparation de la tranche">
				<p>
					<div class="loading">Veuillez patienter...</div>
					<div class="done cache">La tranche est prête à être modifiée. Vous pouvez passer à l'étape suivante.</div>
					<form>
						<input type="hidden" checked="checked" name="choix" value="to-wizard-conception" id="to-wizard-conception3" />
					</form>
				</p>
			</div>
			
			
		<div id="wizard-conception" class="main first wizard deadend" title="Assistant DucksManager - Conception de la tranche">
			<p>
				<div class="chargement">Chargement...</div>
				<form class="cache" name="form_options">
					<span id="nom_complet_numero"></span>
					Dimensions de la tranche : 
					<input type="text" id="Dimension_x" name="Dimension_x" maxlength="3" size="2"> mm 
					x 
					<input type="text" id="Dimension_y" name="Dimension_y" maxlength="3" size="2"> mm
					<button id="modifier_dimensions" class="cache small">Modifier</button>
					<br />
					Chacune des manipulations permettant de créer la tranche sont appelées des <b>étapes</b>.
					<br />
					&lt; Les étapes de votre tranche sont présentées à gauche, dans leur ordre d'utilisation.<br />
					&lt; Cliquez sur une étape pour la modifier.<br />
					&lt; Passez la souris entre 2 étapes pour en insérer une nouvelle.<br />
					<p class="texte_presentation_tranche_finale">
						La tranche telle qu'elle sera affichée dans la bibliothèque DucksManager est présentée à gauche de la photo de la tranche. &gt;
					</p> 
				</form>
			</p>
		</div>
		
		<div class="wizard preview_etape initial">
			
		</div>
		
		<div id="options-etape--Agrafer" class="options_etape cache">
			<div class="premiere agrafe"></div>
			<div class="deuxieme agrafe"></div>
			<p>
				&gt; Déplacez et redimensionnez les agrafes.<br />
			</p>
		</div>
		
		<div id="options-etape--Degrade" class="options_etape cache">
			<div class="rectangle_degrade"></div>
			<p>
				&gt; Déplacez et redimensionnez la zone de dégradé.<br />
				
				&gt; Définissez la première couleur.<br />
				<label for="option-Couleur_debut">Couleur sélectionnée : </label>
				<input class="couleur" type="text" name="option-Couleur_debut" maxlength="7"/>
				<br />
				
				&gt; Définissez la deuxième couleur.<br />
				<label for="option-Couleur_fin">Couleur sélectionnée : </label>
				<input class="couleur" type="text" name="option-Couleur_fin" maxlength="7"/>
				<br />
				
				&gt; Indiquez le sens du dégradé.<br />
				<div style="font-size:16px">
					<div class="small buttonset">
						<input type="radio" name="option-Sens" value="Horizontal" id="Horizontal" /><label for="Horizontal">Gauche vers droite</label>
						<input type="radio" name="option-Sens" value="Vertical" id="Vertical" /><label for="Vertical">Haut vers bas</label>
					</div>
				</div>
			</p>
		</div>
		
		<div id="options-etape--DegradeTrancheAgrafee" class="options_etape cache">
			<div class="premiere agrafe"></div>
			<div class="deuxieme agrafe"></div>
			<div class="premier rectangle_degrade"></div>
			<div class="deuxieme rectangle_degrade"></div>
			<p>
				&gt; Définissez la couleur de fond de la tranche.<br />
				<label for="option-Couleur">Couleur sélectionnée : </label>
				<input class="couleur" type="text" name="option-Couleur" maxlength="7"/>
			</p>
		</div>
		
		<div id="options-etape--Remplir" class="options_etape cache">
			<div class="rectangle_position" class="cache"></div>
			<img class="point_remplissage cache" src="images/cross.png" />
			<p>
				&gt; Déplacez le curseur en forme de croix pour modifier le point de remplissage.<br />
				&gt; Sélectionnez une couleur pour modifier la couleur de remplissage.
			</p>
			<form id="options_etape">
				<label for="option-Couleur">Couleur sélectionnée : </label>
				<input class="couleur" type="text" name="option-Couleur" maxlength="6"/>
			</form>
		</div>
		
		<div id="options-etape--Arc_cercle" class="options_etape cache">
			<img class="arc_position cache">
			<p>
				&gt; Déplacez et redimensionnez l'arc de cercle.<br />
				&gt; Sélectionnez une couleur pour modifier la couleur de remplissage ou de contour.<br />
			</p>
			<form id="options_etape">
				<div class="buttonset">
					<input type="radio" name="option-drag-resize" value="deplacement" id="Arc_deplacement" /><label for="Arc_deplacement">Déplacement</label>
					<input type="radio" name="option-drag-resize" value="redimensionnement"  id="Arc_redimensionnement"/><label for="Arc_redimensionnement">Redimensionnement</label><br /><br />
				</div>
				<label for="option-Couleur">Couleur : </label>
				<input class="couleur" type="text" name="option-Couleur" maxlength="7"/>
				<br />
				<input type="checkbox" name="option-Rempli" id="option-Rempli" />&nbsp;<label for="option-Rempli">Remplir l'arc</label> 
					
			</form>
		</div>
		
		<div id="options-etape--Polygone" class="options_etape cache">
			<img class="polygone_position cache">
			<div class="point_polygone modele cache"></div>
			<p>
				&gt; Ajoutez et déplacer les points du polygone.<br />
				&gt; Indiquez la couleur de remplissage du polygone.<br />
			</p>
			<form id="options_etape">
				<div class="buttonset">
					<input type="radio" name="option-action" value="ajout" id="Point_ajout" /><label for="Point_ajout">Ajout de point</label>
					<input type="radio" name="option-action" value="deplacement" id="Point_deplacement" /><label for="Point_deplacement">Déplacement de point</label>
					<input type="radio" name="option-action" value="suppression" id="Point_suppression" /><label for="Point_suppression">Suppression de point</label>
				</div>
				<div id="descriptions_actions">
					<div id="description_ajout" class="cache">
						Cliquez sur le point après lequel le nouveau point sera placé.
					</div>
					<div id="description_deplacement" class="cache">
						Glissez-déposez le point à déplacer.
					</div>
					<div id="description_suppression" class="cache">
						Cliquez sur le point à supprimer.
					</div>
				</div>
				<label for="option-Couleur">Couleur du polygone : </label>
				<input class="couleur" type="text" name="option-Couleur" maxlength="7"/>
					
			</form>
		</div>
		
		<div id="options-etape--Rectangle" class="options_etape cache">
			<div class="rectangle_position" class="cache"></div>
			<p>
				&gt; Déplacez et redimensionnez le rectangle.<br />
				&gt; Sélectionnez une couleur pour modifier la couleur de remplissage ou de contour.<br />
			</p>
			<form id="options_etape">
				<label for="option-Couleur">Couleur : </label>
				<input class="couleur" type="text" name="option-Couleur" maxlength="7"/>
				<br />
				<input type="checkbox" name="option-Rempli" id="option-Rempli" />&nbsp;<label for="option-Rempli">Remplir le rectangle</label> 
					
			</form>
		</div>
		
		<div id="options-etape--Image" class="options_etape cache">
			<div class="image_position cache"></div>
			<p>
				&gt; Déplacez et redimensionnez l'image incrustée.<br />
			</p>
			<form id="options_etape">
				Image utilisée : 
				<input type="text" name="option-Source" />
				<button class="small" name="parcourir">Parcourir</button>
				<br />
				<img class="apercu_image hidden" />
			</form>
		</div>
		
		<div id="options-etape--TexteMyFonts" class="options_etape cache">
			<input type="hidden" name="original_preview_width" />
			<input type="hidden" name="original_preview_height" />
			<div class="image_position cache"></div>
			<div class="accordion">
				<h3><a href="#">Propriétés du texte</a></h3>
				<div class="proprietes_texte">
					<table style="border:0" cellspacing="0" cellpadding="1">
						<tr>
							<td>Police de caractères : </td>
							<td style="white-space:nowrap"><input name="option-URL" type="text" maxlength="90" size="19" />
								<button class="modifier_police small">
									<span>Modifier</span>
								</button>
							</td>
						</tr>
						<tr>
							<td>Texte : </td>
							<td><input name="option-Chaine" type="text" maxlength="90" size="30" /></td>
						</tr>
						<tr>
							<td>
								<label for="option-Couleur_texte">Couleur du texte : </label>
							</td>
							<td>
								<input class="couleur" type="text" name="option-Couleur_texte" maxlength="7"/>
							</td>
						</tr>
						<tr>
							<td>
								<label for="option-Couleur_fond">Couleur du fond : </label>
							</td>
							<td>
								<input class="couleur" type="text" name="option-Couleur_fond" maxlength="7"/>
							</td>
						</tr>
						<tr>
							<td colspan="2" style="text-align: center">
								<br />
								Texte généré : <br />
								<div class="apercu_myfonts">
									<img />
								</div>
							</td>
						</tr>
						<tr>
					</table>
				</div>
				<h3><a href="#">Finition du texte généré</a></h3>
				<div class="finition_texte_genere">
					Faites glisser le bord droit du texte généré de façon à ce qu'il soit entièrement visible.
					<br />
					<input type="checkbox" name="option-Demi_hauteur" id="option-Demi_hauteur" />&nbsp;<label for="option-Demi_hauteur">Cochez cette case pour éviter que le texte apparaisse sur 2 lignes.</label> 
					<br /><br />
					<div>
						<div class="extension_largeur cache">&nbsp;</div>
						<table style="border:0" cellspacing="0" cellpadding="0">
							<tr>
								<td colspan="2" style="text-align: center">
									<div class="apercu_myfonts">
										<img />
									</div>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<h3><a href="#">Rotation</a></h3>
				<div class="rotation">
					Faites tourner la zone de texte pour modifier la rotation du texte sur la tranche.
					<br />
					<table style="border:0" cellspacing="0" cellpadding="1">
						<tr style="height: 320px">
							<td>
								<a href="javascript:void(0)" name="fixer_rotation -90">Fixer à -90 &deg;</a><br />
								<a href="javascript:void(0)" name="fixer_rotation 0">Fixer à 0 &deg;</a><br />
								<a href="javascript:void(0)" name="fixer_rotation 90">Fixer à 90 &deg;</a><br />
								<a href="javascript:void(0)" name="fixer_rotation 180">Fixer à 180 &deg;</a><br />
							</td>
							<td><input name="option-Rotation" type="text" maxlength="90" size="35" readonly="readonly"
									   value="Faites tourner cette zone (Rotation=0.00&deg;)" /></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	
		<div id="wizard-numero-inconnu" class="wizard deadend" title="numéro non référencé">
			<p>
				Les tranches ne peuvent être reproduites que pour les numéros 
				référencés sur la base <a target="_blank" href="http://coa.inducks.org">Inducks</a>.
				Référencez votre numéro pour Inducks pour qu'il apparaisse dans les listes.
			</p>
		</div>

<!--  Dialogues issus du menu et utilitaires -->

<div id="wizard-ajout-etape" class="first wizard modal" title="Ajouter une étape">
	<p>
		<form>
			<div class="accordion">
				<h3><a href="#">Créer une étape à partir de zéro</a></h3>
				<div>
					Que souhaitez-vous faire ? 
					<div id="liste_fonctions"></div>
					<input type="hidden" name="etape" />
					<input type="hidden" name="pos" />
				</div>
				<h3><a href="#">Créer une étape à partir d'une étape similaire</a></h3>
				<div>
					<div class="aucune_etape">Aucune étape n'aété créée pour le moment.</div>
					<div class="etape_existante">
						<a id="selectionner_etape_base" style="text-decoration:underline" href="#">
							Sélectionner l'étape de base
						</a>
						<span id="section_etape_a_cloner" class="cache">
							Etape <input id="etape_a_cloner" name="etape_a_cloner" readonly="readonly" /> sélectionnée pour le clonage
						</span>
					</div>
				</div>
			</div>
		</form>
	</p>
</div>

<div id="wizard-images" class="wizard deadend photo_principale" title="Assistant DucksManager - Photos de la tranche">
	<p>
		<form name="form_options">
			<span class="photo_principale">
				Afin d'assurer la meilleure conception de tranche possible, une photo de la tranche est fortement conseillée.<br />
				La photo doit contenir uniquement la tranche souhaitée, en position verticale.
				Cette photo sera mise à côté de votre tranche en cours de conception.
			</span> 
			<span class="autres_photos">
				<!-- Si certaines parties de la tranche (des logos par exemple) ne sont pas assez visibles depuis cette photo, 
				cela peut être une bonne idée de les photographier à part.<br /> -->
				Les photos doivent être nettes, bien éclairées, et les couleurs fidèles à la tranche originale.
			</span>
			<span class="photos_texte">
				Sélectionnez une image contenant le texte, et <u>uniquement</u> le texte. <br />
				Le texte doit être horizontal et net sur la photo. Pour des résultats optimaux,
				modifiez manuellement l'image pour en retirer tout élément pouvant nuire à 
				la détection de la police de caractères.
			</span>
			<br />
			<!-- <span class="photo_principale">
				Vous pourrez revenir à cet écran à tout moment lors de la conception de la tranche.<br />
			</span> -->
			<div class="accordion">
				<h3 id="upload">
					<a href="#">
						<span class="photo_principale">Envoyer une photo</span>
						<span class="autres_photos photos_texte">Envoyer une image d'élément</span>
					</a>
				</h3>
				<div name="upload" class="envoyer_photo">
					<span class="photo_principale">
						<iframe src="<?=base_url()?>index.php/helper/index/image_upload.php?photo_tranche=1"></iframe>
					</span>
					<span class="autres_photos photos_texte">
						<iframe src="<?=base_url()?>index.php/helper/index/image_upload.php?photo_tranche=0"></iframe>
					</span>
				</div>
				
				<h3 id="section_photo" class="autres_photos photos_texte">
					<a href="#">
						<span class="autres_photos photos_texte">A partir de la photo de tranche</span>
					</a>
				</h3>
				<div name="section_photo" class="selectionner_photo_tranche autres_photos photos_texte">
					<ul class="gallery cache">
						<li class="template">
							<img />
						</li>
					</ul>
				</div>
				
				<h3 id="gallery">
					<a href="#">
						<span class="photo_principale">Sélectionner une photo existante</span>
						<span class="autres_photos photos_texte">Sélectionner une image existante</span>
					</a>
				</h3>
				<div name="gallery" class="selectionner_photo">
					<p class="chargement_images" >Chargement des images</p>
					<p class="pas_d_image autres_photos photos_texte cache" >Aucune image répertoriée pour ce pays</p>
					<p class="pas_d_image photo_principale cache" >Aucune image répertoriée pour ce magazine</p>
					<ul class="gallery cache">
						<li class="template">
                            <div class="thumbnailAndFilenameContainer">
							    <img />
                                <div class="filename"></div>
                            </div>
							<input type="radio" name="numeroPhotoPrincipale" class="cache" />
						</li>
					</ul>
				</div>
			</div>
			<button id="to-wizard-resize" class="cache" value="to-wizard-resize">
				<span class="photo_principale">Rogner la photo sélectionnée</span>
				<span class="autres_photos photos_texte">Rogner l'image sélectionnée</span>
			</button>
			<br />
			<span class="photo_principale">Sélectionnez une photo pour poursuivre.</span>
			<span class="autres_photos photos_texte">Sélectionnez une image pour poursuivre.</span>
			
			<input type="hidden" name="selected" />
            <div class="photo_principale">
                <input type="checkbox" id="pasDePhoto" name="pasDePhoto"/>
                <label for="pasDePhoto">Pas de photo</label>
            </div>
			<input type="hidden" id="numeroPhotoPrincipale" name="numeroPhotoPrincipale" value=""/>
			<div class="buttonset cache">
				<input type="radio" checked="checked" name="choix" value="to-wizard-conception" id="to-wizard-conception" />
			</div>
		</form>
	</p>
</div>

<div id="wizard-resize" class="wizard first closeable" title="Retouche d'image">
	<p>
		Rognez l'image.
        <b>Pour de meilleurs résultats sur votre modèle, nous vous conseillons d'éditer l'image rognée sur votre ordinateur<br />
        afin par exemple de rendre transparent son arrière plan.</b>
	</p>
	<img /><br />
	<div class="error crop_inconsistent cache">Une partie de votre sélection est située en dehors de l'image.</div>
	<form>
		<input type="hidden" name="destination" />
		<div class="buttonset">
			<input type="hidden" checked="checked" name="choix" value="do-in-wizard-enregistrer" id="do-in-wizard-enregistrer" />
			<input type="hidden" checked="checked" name="onClose" value="to-wizard-images" id="to-wizard-images" />
		</div>
	</form>
</div>

<div id="wizard-myfonts" class="wizard first closeable" title="Recherche d'une police de caractères">
	<form>
		<div class="explication">
			Suivez l'assistant ci-dessous : le site MyFonts permet de retrouver la police de caractères d'un texte.
			<br />
			Une fois que vous parvenez à la page de proposition de polices de caractères 
			(vous verrez l'image <img class="exemple_resultats" src="images/whatthefont_results.png" />en haut de la page),
			<br />
			faites un clic droit sur la police ressemblant le plus à celle de votre image, puis un clic gauche sur "Copier l'adresse du lien" 
			(<a class="exemple_cache toggle_exemple" href="#">Voir un exemple</a>
			 <a class="exemple_affiche toggle_exemple cache" href="#">Cacher l'exemple</a>).
			<br />
			<img src="images/whatthefont_selection_exemple.png" class="exemple_affiche cache" /><br />
			Collez ce lien (Ctrl+V ou Cmd+V sur Mac) dans le champ ci-après :
			<input type="text" name="url_police" size="100"/>
		</div>
		<iframe></iframe>
	</form>
</div>

<div id="wizard-confirmation-supprimer" class="wizard" title="Supprimer l'étape ?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Vous allez supprimer cette étape. Continuer ?</p>
</div>

<div id="wizard-confirmation-rechargement" class="wizard" title="Sauvegarder les changements ?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Vous avez modifié l'étape ouverte sans valider ses modifications. 
	Souhaitez-vous valider ces modifications ?</p>
</div>

<div id="wizard-confirmation-annulation" class="wizard" title="Sauvegarder les changements ?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Vous avez modifié l'étape que vous souhaitez fermer. 
	Souhaitez-vous sauvegarder ces modifications ?</p>
</div>

<div id="wizard-confirmation-suppression" class="wizard" title="Supprimer cette étape ?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Cette étape va être supprimée. Continuer ?</p>
	<span id="num_etape_a_supprimer" class="cache"></span>
</div>

<div id="wizard-confirmation-suppression-point" class="wizard" title="Supprimer ce point ?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	Ce point du polygone va être supprimé. Continuer ?</p>
	<span id="nom_point_a_supprimer" class="cache"></span>
</div>

<div id="wizard-confirmation-resize" class="wizard" title="Nom de l'image ?">
	<p>
	Une nouvelle image va être créee. Indiquez le nom que vous souhaitez lui donner.
	(Exemples : <i>Tete Donald</i> ; <i>Motif arriere plan</i>, etc.</p>
	<form>
		<input type="text" name="nom_image" />
	</form>
</div>

<div id="wizard-confirmation-desactivation-modele" class="wizard" title="Suppression d'un modèle EdgeCreator">
	<p>
	Le modèle EdgeCreator en cours de conception va être désactivé. Confirmer ?
	</p>
</div>

<div id="wizard-confirmation-validation-modele" class="wizard" title="Validation d'un modèle EdgeCreator">
	<p>
	Votre tranche apparaîtra aux côtés des tranches suivantes dans la bibliothèque DucksManager : <br />
	
	<div class="tranches_affichees_magazine"></div>
    <br />
    <input type="checkbox" id="cacher_libelles_magazines" name="cacher_libelles_magazines">&nbsp;
    <label for="cacher_libelles_magazines">Cacher les libellés des numéros</label>
    <br /><br />
	
	Le modèle EdgeCreator en cours de conception va être verrouillé en attendant sa validation. <br />
	<br /><br />
	Confirmer ?
	<form>
		<input type="hidden" checked="checked" name="choix" value="to-wizard-confirmation-validation-modele-contributeurs" id="to-wizard-confirmation-validation-modele-contributeurs" />
    </form>
	</p>
</div>

<div id="wizard-confirmation-validation-modele-contributeurs" class="wizard" title="Validation d'un modèle EdgeCreator - Contributeurs">
	<p>
		Veuillez sélectionner les photographes (utilisateurs qui ont photographié la tranche)
	    et les designers (utilisateurs qui ont recréé la tranche via EdgeCreator) :
	    <form id="form_save_png">
	    	<span id="photographes">Photographes</span>
	    	<span id="designers" style="margin-left:30px">Designers</span>
			<input type="hidden" checked="checked" name="choix" value="to-wizard-confirmation-validation-modele-ok" id="to-wizard-confirmation-validation-modele-ok" />
	    </form>
	    
	</p>
</div>

<div id="wizard-confirmation-validation-modele-ok" class="wizard" title="modèle envoyé">
	<p>
	Le modèle d'image aété envoyé pour validation.
	</p>
</div>

<div id="wizard-erreur-image-myfonts" class="wizard" title="Paramètres de texte invalides">
	<p>
		Les paramètres du texte à générer sont invalides. <br />
		Vérifiez notamment que la police de caractères spécifiée est valide.
	</p>
</div>


<div id="wizard-erreur-generation-image" class="wizard" title="Erreur de génération d'image">
	<p>
		La génération de l'image pour l'étape <span name="etape"></span> a échoué. <br />
		La génération des images des étapes suivantes aété annulée. <br /><br />
		Merci de reporter ce problème au webmaster en indiquant le message d'erreur suivant : <br /><br />
		<iframe></iframe>
	</p>
</div>

<div id="wizard-session-expiree" class="wizard" title="Session expirée">
	<p>
	Votre session a expiré. 
	<br />
	Retour à la page d'accueil d'EdgeCreator.
	</p>
</div>

<div id="conteneur_selecteur_couleur" class="cache">
	<div id="selecteur_couleur">
		<button class="small" id="fermer_selecteur_couleur" name="fermer_selecteur_couleur">Fermer</button>
		<ul>
	    	<li><a href="#picker_container">Sélection de couleur</a></li>
	    	<li><a href="#couleurs_frequentes">Couleurs fréquemment utilisées</a></li>
	    	<li><a href="#depuis_photo">Depuis la photo de tranche</a></li>
	  	</ul>
		<div id="picker_container">
			Sélectionnez une couleur
			<div id="picker"></div>
		</div>
		<div id="couleurs_frequentes">
			<input type="text" readonly="readonly" class="couleur_frequente template" />
		</div>
		<div id="depuis_photo">
			<div name="description_selection_couleur">
				Déplacez le curseur de la souris vers votre photo de tranche et cliquez
				à l'endroit où se situe la couleur désirée.
			</div>
			<div name="pas_de_photo_tranche" id="pas_de_photo_tranche"></div>
		</div>
	</div>
</div>

<div id="libelles-messages" class="cache">
	<div id="message-aucune-image-de-tranche">
		<div class="titre">
			Aucune photo générale de tranche.
		</div>
		<div class="libelle">
			Le modèle EdgeCreator en cours de conception ne contient aucune photo générale 
			de la tranche. <br />
			Spécifier une photo générale de tranche est fortement recommandé
			car cela permet de faciliter sa conception. <br />
			Cliquez sur l'icône <img src="images/photo.png" /> de la barre de menu
			pour sélectionner une photo de tranche.
		</div>
	</div>
</div>

<span class="intitule_magazine template">
    <img name="wizard_pays" src="" />&nbsp;
    <b><span name="wizard_magazine_complet"></span></b>&nbsp;n&deg;
    <span name="wizard_numero"></span>&nbsp;
    <span name="Dimension_x"></span> x <span name="Dimension_y"></span> mm
</span>