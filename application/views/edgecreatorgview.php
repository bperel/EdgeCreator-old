<div id="barre"></div>
<div id="entete_page">
	<?php
	if ($privilege!='Affichage') {?>
		<div id="zoom" class="cache">
			Zoom : <span id="zoom_value">1.5</span>
			<div id="zoom_slider"></div>
		</div>
		<?php 
		if ($mode_expert==true) {?>
			<div style="position:fixed;left:210px">
				<div style="float:left">
					<select style="font-size:11px" id="liste_pays"></select>
					&nbsp;&nbsp;
					<select style="font-size:11px" id="liste_magazines"></select><br />
					<div id="filtre_numeros">
						Numéros&nbsp;
						<select id="filtre_debut"></select>&nbsp;à&nbsp;
						<select id="filtre_fin"></select>
						<button>OK</button>
					</div>
				</div>
			</div>
		<?php }
	} ?>
	<div id="action_bar" class="cache"><span id="nom_complet_tranche_en_cours"></span><br />
		<img class="action tip" name="home" 
			 title="Revenir à l'écran d'accueil de EdgeCreator" />
		<img class="action tip" name="photo"
			 title="Insérer/Sélectionner une photo de tranche" />
		<img class="action tip" name="clone"
			 title="Cloner depuis un autre modèle de tranche" />
		<img class="action tip" name="corbeille"
			 title="Supprimer cette conception de tranche" />
		<img class="action tip" name="valider"
			 title="Valider cette conception de tranche" />
	</div>
	
	<div id="status">
	</div>
	
	<div id="status_user">
		<?php
        if (isset($user)) {
            ?><div>Connecté(e) en tant que <span id="utilisateur"><?=$user?></span></div>
            <button class="small" id="deconnexion" onclick="logout()">Déconnexion</button><?php
        }
        else {
			?>Non connecté(e)<?php
		}
		?>
	</div>
	<div id="template-warning" class="ui-widget cache">
	    <div class="ui-state-warning ui-corner-all" style="padding: 0 .7em;">
            <span class="ui-icon ui-icon-alert"></span>
            <strong>Attention:</strong> <span class="message-label"></span>
	    </div>
	</div>
</div>
<?php
if ($mode_expert==true) {?>
	<div id="viewer">
		<div id="viewer_inner">
			<div id="tabs">
				<ul>
					<li><a href="#contenu_builder">Builder</a></li>
					<li><a href="#contenu_previews">Previews</a></li>
				</ul>
			
				<div id="contenu_builder">
					<div id="numero_preview">Cliquez sur le lien <img src="<?=base_url()?>images/view.png" /> d'un numéro pour le prévisualiser.</div>
					<?php switch($privilege) {
						case 'Admin' :
						?>
						<a style="display:none" class="save" href="javascript:void(0)">Enregistrer comme image PNG</a>
					<?php 
						break;
						case 'Edition' :
						?>
						<a style="display:none" class="save" href="javascript:void(0)">Proposer le modèle de tranche</a>
					<?php
						break;
					} ?>
					<div class="previews"></div>
				</div>
				<div id="contenu_previews">
					<span class="options" style="display:none">
						<input type="checkbox" checked="checked" id="option_details" />Détails<br />
						<input type="checkbox" checked="checked" id="option_pretes_seulement" />Prêtes seulement<br />
					</span>
	
					<?php switch($privilege) {
						case 'Admin' :
						?>
						<a style="display:none;" class="save" href="javascript:void(0)">Enregistrer comme images PNG</a>
					<?php 
						break;
						case 'Edition' :
						?>
						<a style="display:none" class="save" href="javascript:void(0)">Proposer les modèles de tranches</a>
					<?php
						break;
					} ?>
					<div id="numero_preview_debut" style="display:inline">
						Cliquez sur le lien <img src="<?=base_url()?>images/view.png" /> d'un numéro 
						pour le sélectionner comme premier numéro à prévisualiser.
					</div>
					- 
					<div id="numero_preview_fin" style="display:inline"></div>
					<div id="montrer_details">
					</div>
					<div class="previews"></div>
				</div>
			</div>
		</div>
	</div>
	<div id="corps">
		<br />
	</div>
	<?php if ($privilege !='Affichage') { ?>
		<div id="infos" class="cache">
			<div id="helpers"></div>
		</div>
		<div id="upload_fichier">
		</div>
	<?php } ?>
	<div id="chargement">
	</div>
	<div id="erreurs" ></div>
	<?php if ($privilege !='Affichage') { ?>
		<a id="toggle_helpers" href="javascript:void(0)" class="cache">Cacher l'assistant</a>
	<?php }
} ?>