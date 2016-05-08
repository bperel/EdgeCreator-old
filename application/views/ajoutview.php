<?=$fonction->Nom_fonction?> : <br />
<?php foreach($numeros_debut_globaux as $i=>$numero_debut_global) {
	$numero_fin_global=$numeros_fin_globaux[$i];
	?>Numéros <?=$numero_debut_global?> à <?=$numero_fin_global?><br /><?php
}?>
<table rules="all" style="margin-left:5px;border:1px solid black"><?php
	foreach($options as $option_nom=>$option_valeur) {
		?><tr><td><?=$option_nom?></td>
	   <td><?=$fonction->getValeurModifiable($option_nom,array($intervalle=>$option_valeur),false)?></td></tr><?php
	}
?>
</table><br />
Modifiez les paramètres de la nouvelle fonction puis, lorsque la prévisualisation vous satisfait,<br />Cliquez sur "Appliquer" pour l'intégrer dans le modèle de tranche.
