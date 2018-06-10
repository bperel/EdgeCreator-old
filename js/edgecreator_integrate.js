var numeros_dispos = [];
$.ajax({
	url: urls['numerosdispos'] + ['index', pays, magazine].join('/'),
	type: 'post',
	dataType: 'json',
	success: function (data) {
		if (data.erreur !== undefined && data.erreur === 'Nombre d\'arguments insuffisant') {
			$('#nom_magazine').html('Utilisez un nom de magazine valide');
			return;
		}
		numeros_dispos = data.numeros_dispos;

		$.ajax({
			url: urls['parametrageg'] + ['index', pays, magazine, 'null'].join('/'),
			type: 'post',
			dataType: 'json',
			success: function (etapes) {
				// Toujours intégrer l'étape -1
				integrer_etape(Object.values(etapes)[0].Ordre);
			}
		});
	}
});

function integrer_etape(num_etape) {
	$('#log').append('Int&eacute;gration de l\'&eacute;tape ' + num_etape + '...');
	$.ajax({
		url: urls['parametrageg'] + ['index', pays, magazine, num_etape].join('/'),
		type: 'post',
		dataType: 'json',
		success: function (options) {
			integrer_option(num_etape, Object.keys(options)[0]);
		},
		error: function (data) {
			$('#log').append('ECHEC : <br />' + data);
		}
	});
}

function integrer_option(num_etape_courante, nom_option_courante) {
	$('#log').append('&nbsp;Int&eacute;gration de l\'option ' + nom_option_courante + '...');
	$.ajax({
		url: urls['modifierg'] + ['index', pays, magazine, num_etape_courante, numero, nom_option_courante, valeurs_options[num_etape_courante][nom_option_courante], 'Dimensions', false].join('/'),
		type: 'post',
		dataType: 'json',
		success: function () {
			$('#log').append('OK<br />');
			var option_trouvee = false;
			for (var nom_option in options) {
				if (option_trouvee) {
					integrer_option(num_etape_courante, nom_option);
					return;
				}
				if (nom_option === nom_option_courante)
					option_trouvee = true;
			}
			var etape_trouvee = false;
			for (var etape = 0; etape < etapes.length; etape++) {
				var num_etape = etapes[etape].Ordre;
				if (etape_trouvee && est_dans_intervalle(numero, etapes[etape].Numero_debut + '~' + etapes[etape].Numero_fin)) {
					integrer_etape(num_etape);
					return;
				}
				if (num_etape === num_etape_courante)
					etape_trouvee = true;
			}

			var parametrage = {};
			var src = urls['viewer'] + '/' + [numero, '1.5', 'all', JSON.stringify(parametrage), 'save', 'false'].join('/') + '/' + username;
			var image = $('<img>', {'id': 'image'});
			$('#section_image').update(image);
			image.attr({'src': src});
		},
		error: function (data) {
			$('#log').insert('ECHEC : <br />&nbsp;' + data);
		}
	});
}