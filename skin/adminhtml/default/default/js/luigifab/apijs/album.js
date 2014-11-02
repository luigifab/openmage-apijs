/**
 * Copyright 2008-2014 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * File created D/15/12/2013, Updated L/27/10/2014, version 25
 * https://redmine.luigifab.info/projects/magento/wiki/apijs
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL).
 */

// traductions
function apijsInitTranslations() {

	apijs.i18n.data.en.deleteTitle = "Delete a file";
	apijs.i18n.data.es.deleteTitle = "Borrar un archivo";
	apijs.i18n.data.fr.deleteTitle = "Supprimer un fichier";
	apijs.i18n.data.en.deleteText = "Are you sure you want to delete this file?[br]Be careful, you can't cancel this operation.";
	apijs.i18n.data.es.deleteText = "Está usted seguro-a de que desea eliminar este archivo?[br]Tenga cuidado, pues no podrá cancelar esta operación.";
	apijs.i18n.data.fr.deleteText = "Êtes-vous certain de vouloir supprimer ce fichier ?[br]Attention, cette opération n'est pas annulable.";

	apijs.i18n.data.en.errorTitle = "Error";
	apijs.i18n.data.fr.errorTitle = "Erreur";
	apijs.i18n.data.en.error403 = "You are not authorized to perform this operation, please [a §]refresh the page[/a].";
	apijs.i18n.data.es.error403 = "No está autorizado-a para llevar a cabo esta operación, por favor [a §]actualice la página[/a].";
	apijs.i18n.data.fr.error403 = "Vous n'êtes pas autorisé(e) à effectuer cette opération, veuillez [a §]actualiser la page[/a].";
	apijs.i18n.data.en.error404 = "Sorry, the file no longer exists, please [a §]refresh the page[/a].";
	apijs.i18n.data.es.error404 = "Lo sentimos, pero el archivo ya no existe, por favor [a §]actualice la página[/a].";
	apijs.i18n.data.fr.error404 = "Désolé, le fichier n'existe plus, veuillez [a §]actualiser la page[/a].";

	apijs.i18n.data.en.sendTitle = "Send one file";
	apijs.i18n.data.es.sendTitle = "Enviar un archivo";
	apijs.i18n.data.fr.sendTitle = "Envoyer un fichier";
}

// en cas d'erreur
function apijsShowError(data) {

	if ((typeof data === 'number') || (data.indexOf('<!DOCTYPE') < 0)) {
		apijs.dialog.dialogInformation(apijs.i18n.translate('errorTitle'), (typeof data === 'number') ? apijs.i18n.translate('error' + data, "href='javascript:location.reload();'") : data, 'error');
	}
	else {
		apijs.dialog.styles.remove('lock');
		location.reload();
	}
}


// #### Envoie d'un fichier JPG/PNG ############################## public ### //
// = révision : 10
// » Affiche un formulaire d'upload avec le dialogue d'upload
// » Envoie un fichier en Ajax (admin/apijs_media/upload[get=product,form_key;post=myimage)
function apijsSendFile(url, maxsize) {

	if (typeof apijs.i18n.data.en.sendTitle !== 'string')
		apijsInitTranslations();

	// sendFile(title, action, inputname, maxsize, extensions, callback, args, icon)
	apijs.upload.sendFile(apijs.i18n.translate('sendTitle'), url, 'myimage', maxsize, 'jpg,jpeg,png', apijsUpdateForm);
}


// #### Modification des données d'un fichier #################### public ### //
// = révision : 12
// » Attention il est admis qu'un code différent de 200/403/404 ne peut pas arriver
// » Affiche rien du tout puisque le formulaire est déjà affiché (cache simplement le bouton lors de l'enregistrement)
// » Demande l'enregistrement en Ajax (admin/apijs_media/save[get=product,store,image,form_key;post=INPUTS])
// » Met à jour le formulaire à partir de la réponse Ajax
function apijsActionSave(button, url) {

	var elems, elem, xhr, data = '';
	button.style.visibility = 'hidden';

	// recherche des données
	elems = button.parentNode.parentNode.querySelectorAll('input');
	for (elem in elems) if (elems.hasOwnProperty(elem) && (elem !== 'length')) {
		if (elems[elem].hasAttribute('name') && apijs.inArray(elems[elem].getAttribute('type'), ['checkbox', 'radio']))
			data += elems[elem].getAttribute('name').replace('apijs-', '') + '=' + encodeURIComponent(elems[elem].checked) + '&';
		else if (elems[elem].hasAttribute('name'))
			data += elems[elem].getAttribute('name').replace('apijs-', '') + '=' + encodeURIComponent(elems[elem].value) + '&';
	}

	// traitement ajax
	xhr = new XMLHttpRequest();
	xhr.open('POST', url, true);
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

	xhr.onreadystatechange = function () {

		if ((xhr.readyState === 4) && (xhr.status === 200)) {

			// le traitement est un succès
			// désactive le bouton (success- = 8 caractères)
			if (xhr.responseText.indexOf('success-') === 0) {
				var button = document.getElementById('attachmentId' + xhr.responseText.slice(8)).querySelector('button.ici');
				button.style.visibility = '';
				button.setAttribute('disabled', 'disabled');
			}
			// le traitement est un échec
			// rechargement de la page ou affichage du contenu de la réponse
			else {
				apijsShowError(xhr.responseText);
			}
		}
		else if ((xhr.readyState === 4) && (xhr.status === 403)) {
			apijsShowError(403);
		}
		else if ((xhr.readyState === 4) && (xhr.status === 404)) {
			apijsShowError(404);
		}
	};

	xhr.send(data.slice(0, -1));
}

function apijsUpdateForm(data) {

	data = data.slice(data.indexOf('</a>') + 4);
	data = data.slice(data.indexOf('>') + 1);
	data = data.slice(0, data.lastIndexOf('<'));
	document.getElementById('apijsGallery').innerHTML = data;

	apijs.dialog.actionClose();
	apijs.slideshow.init();
}


// #### Suppression d'un fichier ################################# public ### //
// = révision : 25
// » Attention il est admis qu'un code différent de 200/403/404 ne peut pas arriver
// » Affiche une demande de confirmation de suppression avec le dialogue de confirmation
// » Demande la suppression en Ajax (admin/apijs_media/delete[get=product,image,form_key])
function apijsDeleteAttachment(action) {

	if (typeof apijs.i18n.data.en.deleteTitle !== 'string')
		apijsInitTranslations();

	// dialogConfirmation(title, text, callback, args, icon)
	apijs.dialog.dialogConfirmation(apijs.i18n.translate('deleteTitle'), apijs.i18n.translate('deleteText'), apijsActionDeleteAttachment, action);
}

function apijsActionDeleteAttachment(args) {

	// args = action
	var xhr = new XMLHttpRequest();
	xhr.open('GET', args, true);

	xhr.onreadystatechange = function () {

		if ((xhr.readyState === 4) && (xhr.status === 200)) {

			// le traitement est un succès
			// mise à jour simple (success- = 8 caractères)
			if (xhr.responseText.indexOf('success-') === 0)
				apijsUpdateForm(xhr.responseText.slice(8));
			// le traitement est un échec
			// rechargement de la page ou affichage du contenu de la réponse
			else
				apijsShowError(xhr.responseText);
		}
		else if ((xhr.readyState === 4) && (xhr.status === 403)) {
			apijsShowError(403);
		}
		else if ((xhr.readyState === 4) && (xhr.status === 404)) {
			apijsShowError(404);
		}
	};

	xhr.send();
}