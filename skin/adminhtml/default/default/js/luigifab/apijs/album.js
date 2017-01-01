/**
 * Copyright 2008-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * Created D/15/12/2013, updated D/27/11/2016
 * https://redmine.luigifab.info/projects/magento/wiki/apijs
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL).
 */

// traductions
function apijsInitTranslations() {

	var de = apijs.i18n.data.de, en = apijs.i18n.data.en, es = apijs.i18n.data.es, fr = apijs.i18n.data.fr,
	    it = apijs.i18n.data.it, pt = apijs.i18n.data.pt, ru = apijs.i18n.data.ru;

	de.deleteTitle = "Eine Datei löschen";
	en.deleteTitle = "Delete a file";
	es.deleteTitle = "Borrar un archivo";
	fr.deleteTitle = "Supprimer un fichier";
	it.deleteTitle = "Eliminare un file";
	pt.deleteTitle = "Suprimir um ficheiro";
	ru.deleteTitle = "Удалить файл";

	de.deleteText = "Sind Sie sicher, dass Sie diese Datei löschen möchten?[br]Achtung, diese Aktion ist unrückgängig.";
	en.deleteText = "Are you sure you want to delete this file?[br]Be careful, you can't cancel this operation.";
	es.deleteText = "¿Está usted seguro-a de que desea eliminar este archivo?[br]Atención, pues no podrá cancelar esta operación.";
	fr.deleteText = "Êtes-vous certain(e) de vouloir supprimer ce fichier ?[br]Attention, cette opération n'est pas annulable.";
	it.deleteText = "Sicuri di voler eliminare il file?[br]Attenzione, questa operazione non puo' essere annullata.";
	pt.deleteText = "Tem certeza de que quer suprimir este ficheiro?[br]Atenção, não pode cancelar esta operação.";
	ru.deleteText = "Вы уверены, что хотите удалить этот файл?[br]Осторожно, вы не сможете отменить эту операцию.";

	de.errorTitle = "Fehler";
	en.errorTitle = "Error";
	es.errorTitle = "Error";
	fr.errorTitle = "Erreur";
	it.errorTitle = "Errore";
	pt.errorTitle = "Erro";
	ru.errorTitle = "Ошибка";

	de.error403 = "Sie verfügen nicht über die notwendigen Rechte um diese Operation durchzuführen, bitte [a §]aktualisieren Sie die Seite[/a].";
	en.error403 = "You are not authorized to perform this operation, please [a §]refresh the page[/a].";
	es.error403 = "No está autorizado-a para llevar a cabo esta operación, por favor [a §]actualice la página[/a].";
	fr.error403 = "Vous n'êtes pas autorisé(e) à effectuer cette opération, veuillez [a §]actualiser la page[/a].";
	it.error403 = "Non siete autorizzati a eseguire questa operazione, vi preghiamo di [a §]ricaricare la pagina[/a].";
	pt.error403 = "Não é autorizado(a) para efetuar esta operação, por favor [a §]atualize a página[/a].";
	ru.error403 = "Вы не авторизованы для выполнения этой операции, пожалуйста [a §]обновите страницу[/a].";

	de.error404 = "Es tut uns leid, diese Datei existiert nicht mehr, bitte [a §]aktualisieren Sie die Seite[/a].";
	en.error404 = "Sorry, the file no longer exists, please [a §]refresh the page[/a].";
	es.error404 = "Lo sentimos, pero el archivo ya no existe, por favor [a §]actualice la página[/a].";
	fr.error404 = "Désolé, le fichier n'existe plus, veuillez [a §]actualiser la page[/a].";
	it.error404 = "Spiacenti, il file non esiste più, vi preghiamo di [a §]ricaricare la pagina[/a].";
	pt.error404 = "Lamento, o ficheiro já não existe, por favor [a §]atualize a página[/a].";
	ru.error404 = "Извините, но файл не существует, пожалуйста [a §]обновите страницу[/a].";

	de.sendTitle = "Eine Datei senden";
	en.sendTitle = "Send a file";
	es.sendTitle = "Enviar un archivo";
	fr.sendTitle = "Envoyer un fichier";
	it.sendTitle = "Inviare un file";
	pt.sendTitle = "Enviar um ficheiro";
	ru.sendTitle = "Отправить один файл";
}

// en cas d'erreur
function apijsShowError(data) {

	if ((typeof data === 'number') || (data.indexOf('<!DOCTYPE') < 0)) {
		apijs.dialog.dialogInformation(
			apijs.i18n.translate('errorTitle'),
			(typeof data === 'number') ? apijs.i18n.translate('error' + data, "href='javascript:location.reload();'") : data,
			'error');
	}
	else {
		apijs.dialog.styles.remove('lock'); // obligatoire sinon demande de confirmation de quitter la page
		location.reload();
	}
}


// #### Envoi d'un fichier JPG/JPEG/PNG ##################################### //
// = révision : 12
// » Affiche un formulaire d'upload avec le dialogue d'upload
// » Envoi un fichier en Ajax (../admin/apijs_media/upload[get=product,form_key;post=myimage)
function apijsSendFile(url, maxsize) {

	if (typeof apijs.i18n.data.en.sendTitle !== 'string')
		apijsInitTranslations();

	//apijs.config.upload.tokenValue = token;
	apijs.upload.sendFile(apijs.i18n.translate('sendTitle'), url, 'myimage', maxsize, 'jpg,jpeg,png', apijsUpdateForm);
}


// #### Modification des données d'un fichier ############################### //
// = révision : 18
// » Attention il est admis qu'un code différent de 200/403/404 n'est pas possible
// » Affiche rien du tout puisque le formulaire est déjà affiché (cache simplement le bouton lors de l'enregistrement)
// » Demande l'enregistrement en Ajax (../admin/apijs_media/save[get=product,store,image,form_key;post=INPUTS])
function apijsActionSave(button, url) {

	var elems, elem, xhr, data = '';
	apijsManageForm(false);

	// recherche des données
	elems = button.parentNode.parentNode.querySelectorAll('input');
	for (elem in elems) if (elems.hasOwnProperty(elem) && !isNaN(elem)) {
		if (elems[elem].hasAttribute('name') && ['checkbox', 'radio'].has(elems[elem].getAttribute('type')))
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
			apijsManageForm(true);
			if (xhr.responseText.indexOf('success-') !== 0)
				apijsShowError(xhr.responseText);
		}
		else if ((xhr.readyState === 4) && ([403, 404].indexOf(xhr.status) !== -1)) {
			apijsManageForm(true);
			apijsShowError(xhr.status);
		}
	};

	xhr.send(data.slice(0, -1)); // slice supprime le dernier &
}

function apijsManageForm(enable) {

	var elems, elem;

	if (!enable) {
		document.getElementById('apijsGallery').setAttribute('class', 'grid saving');
		elems = document.getElementById('apijsGallery').querySelectorAll('input,button');
		for (elem in elems) if (elems.hasOwnProperty(elem) && !isNaN(elem)) {
			elems[elem].setAttribute('tabindex', '-1');
			if (elems[elem].nodeName === 'BUTTON')
				elems[elem].setAttribute('disabled', 'disabled');
			else if (elems[elem].hasAttribute('name') && ['checkbox', 'radio'].has(elems[elem].getAttribute('type')))
				elems[elem].setAttribute('disabled', 'disabled');
			else if (elems[elem].hasAttribute('name'))
				elems[elem].setAttribute('readonly', 'readonly');
		}

		document.querySelector('p.content-buttons.form-buttons').setAttribute('class', 'content-buttons form-buttons saving');
		elems = document.querySelectorAll('p.content-buttons.form-buttons button');
		for (elem in elems) if (elems.hasOwnProperty(elem) && !isNaN(elem)) {
			elems[elem].setAttribute('tabindex', '-1');
			elems[elem].setAttribute('disabled', 'disabled');
		}
	}
	else {
		elems = document.getElementById('apijsGallery').querySelectorAll('input,button:not(.save)');
		for (elem in elems) if (elems.hasOwnProperty(elem) && !isNaN(elem)) {
			elems[elem].removeAttribute('tabindex');
			elems[elem].removeAttribute('readonly');
			elems[elem].removeAttribute('disabled');
		}
		document.getElementById('apijsGallery').setAttribute('class', 'grid');

		elems = document.querySelectorAll('p.content-buttons.form-buttons button');
		for (elem in elems) if (elems.hasOwnProperty(elem) && !isNaN(elem)) {
			elems[elem].removeAttribute('tabindex');
			elems[elem].removeAttribute('disabled');
		}
		document.querySelector('p.content-buttons.form-buttons').setAttribute('class', 'content-buttons form-buttons');
	}
}

function apijsEnableButtonSave(elem, event) {
	if ((event !== undefined) && (event.keyCode !== 9)) { // tab
		if (document.getElementById('apijsGallery').getAttribute('class').indexOf('saving') < 0)
			elem.parentNode.parentNode.querySelector('button.ici').removeAttribute('disabled');
	}
}

function apijsUpdateForm(data) {

	// produit
	if (document.getElementById('apijsGallery')) {

		data = data.slice(data.indexOf('</a>') + 4);
		data = data.slice(data.indexOf('>') + 1);
		data = data.slice(0, data.lastIndexOf('<'));
		document.getElementById('apijsGallery').innerHTML = data;

		apijs.dialog.actionClose();
		apijs.slideshow.init();
	}
	// widget cms
	else {
		MediabrowserInstance.selectFolder(MediabrowserInstance.currentNode);
		apijs.dialog.actionClose();
	}
}


// #### Suppression d'un fichier ############################################ //
// = révision : 27
// » Attention il est admis qu'un code différent de 200/403/404 n'est pas possible
// » Affiche une demande de confirmation de suppression avec le dialogue de confirmation
// » Demande la suppression en Ajax (../admin/apijs_media/delete[get=product,image,form_key])
function apijsDeleteAttachment(action) {

	if (typeof apijs.i18n.data.en.deleteTitle !== 'string')
		apijsInitTranslations();

	apijs.dialog.dialogConfirmation(apijs.i18n.translate('deleteTitle'), apijs.i18n.translate('deleteText'),
		apijsActionDeleteAttachment, action);
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