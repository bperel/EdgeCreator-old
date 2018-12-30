<div id="entete_page">
	<?php
	if ($privilege!=='Affichage') {?>
		<div id="zoom" class="invisible">
			Zoom : <span id="zoom_value">1.5</span>
			<div id="zoom_slider"></div>
		</div>
		<?php
	} ?>
	<div id="action_bar" class="cache"><span id="nom_complet_tranche_en_cours"></span><br />
		<img class="action tip" name="home"
			 title="Revenir à l'écran d'accueil de EdgeCreator" />
		<img class="action tip" name="info"
			 title="Informations sur la conception de tranche avec EdgeCreator" />
		<img class="action tip" name="dimensions"
			 title="Modifier les dimensions de la tranche" />
		<img class="action tip" name="photo"
			 title="Insérer/Sélectionner une photo de tranche" />
		<img class="action tip" name="clone"
			 title="Cloner depuis un autre modèle de tranche" />
		<img class="action tip" name="corbeille"
			 title="Supprimer cette conception de tranche" />
		<img class="action tip" name="valider"
			 title="Valider cette conception de tranche" />
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
