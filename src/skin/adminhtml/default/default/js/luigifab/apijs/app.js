/**
 * Created D/15/12/2013
 * Updated V/24/07/2020
 *
 * Copyright 2008-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/apijs
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

var apijsOpenMage = new (function () {

	"use strict";
	this.hide = false;

	this.start = function () {

		var d = apijs.i18n.data;
		// https://docs.google.com/spreadsheets/d/1UUpKZ-YAAlcfvGHYwt6aUM9io390j0-fIL0vMRh1pW0/edit?usp=sharing
		// auto start
		d.cs[252] = "Chyba";
		d.de[250] = "Eine Datei löschen";
		d.de[251] = "Sind Sie sicher, dass Sie diese Datei löschen möchten?[br]Achtung, diese Aktion ist unrückgängig.";
		d.de[252] = "Fehler";
		d.de[253] = "Sie verfügen nicht über die notwendigen Rechte um diese Operation durchzuführen, bitte [a §]aktualisieren Sie die Seite[/a].";
		d.de[254] = "Es tut uns leid, diese Datei existiert nicht mehr, bitte [a §]aktualisieren Sie die Seite[/a].";
		d.en[250] = "Remove a file";
		d.en[251] = "Are you sure you want to remove this file?[br]Be careful, you can't cancel this operation.";
		d.en[252] = "Error";
		d.en[253] = "You are not authorized to perform this operation, please [a §]refresh the page[/a].";
		d.en[254] = "Sorry, the file no longer exists, please [a §]refresh the page[/a].";
		d.en[255] = "Clear cache";
		d.en[256] = "Are you sure you want to clear the cache?[br]Be careful, you can't cancel this operation.";
		d.es[250] = "Borrar un archivo";
		d.es[251] = "¿Está usted seguro-a de que desea eliminar este archivo?[br]Atención, pues no podrá cancelar esta operación.";
		d.es[253] = "No está autorizado-a para llevar a cabo esta operación, por favor [a §]actualice la página[/a].";
		d.es[254] = "Disculpe, pero el archivo ya no existe, por favor [a §]actualice la página[/a].";
		d.fr[250] = "Supprimer un fichier";
		d.fr[251] = "Êtes-vous certain(e) de vouloir supprimer ce fichier ?[br]Attention, cette opération n'est pas annulable.";
		d.fr[252] = "Erreur";
		d.fr[253] = "Vous n'êtes pas autorisé(e) à effectuer cette opération, veuillez [a §]actualiser la page[/a].";
		d.fr[254] = "Désolé, le fichier n'existe plus, veuillez [a §]actualiser la page[/a].";
		d.fr[255] = "Vider le cache";
		d.fr[256] = "Êtes-vous certain(e) de vouloir vider le cache ?[br]Attention, cette opération n'est pas annulable.";
		d.it[250] = "Eliminare un file";
		d.it[251] = "Sicuri di voler eliminare il file?[br]Attenzione, questa operazione non può essere annullata.";
		d.it[252] = "Errore";
		d.it[253] = "Non siete autorizzati a eseguire questa operazione, vi preghiamo di [a §]ricaricare la pagina[/a].";
		d.it[254] = "Spiacenti, il file non esiste più, vi preghiamo di [a §]ricaricare la pagina[/a].";
		d.ja[250] = "ファイルを削除";
		d.ja[252] = "エラー";
		d.nl[252] = "Fout";
		d.pl[252] = "Błąd";
		d.pt[250] = "Suprimir um ficheiro";
		d.pt[251] = "Tem certeza de que quer suprimir este ficheiro?[br]Atenção, não pode cancelar esta operação.";
		d.pt[252] = "Erro";
		d.pt[253] = "Não é autorizado(a) para efetuar esta operação, por favor [a §]atualize a página[/a].";
		d.pt[254] = "Lamento, o ficheiro já não existe, por favor [a §]atualize a página[/a].";
		d.ru[250] = "Удалить файл";
		d.ru[251] = "Вы уверены, что хотите удалить этот файл?[br]Осторожно, вы не сможете отменить эту операцию.";
		d.ru[252] = "Ошибка";
		d.ru[253] = "Вы не авторизованы для выполнения этой операции, пожалуйста [a §]обновите страницу[/a].";
		d.ru[254] = "Извините, но файл не существует, пожалуйста [a §]обновите страницу[/a].";
		d.tr[252] = "Hata";
		d.zh[252] = "错误信息";
	// auto end
	};

	this.error = function (data) {

		if ((typeof data == 'string') || (data.indexOf('<!DOCTYPE') < 0)) {
			if (apijs.dialog.has('upload'))
				apijs.upload.onError(false, data);
			else
				apijs.dialog.dialogInformation(apijs.i18n.translate(252), data, 'error');
		}
		else {
			apijs.dialog.remove('lock'); // obligatoire sinon demande de confirmation de quitter la page
			self.location.reload();
		}
	};

	this.sendFiles = function (title, action, onemax, allmax) {
		apijs.upload.sendFiles(title, action, 'myimage', onemax, allmax, 'jpg,jpeg,gif,png,svg', apijsOpenMage.updateForm);
	};

	this.actionSave = function (action) {

		document.getElementById('apijsGallery').setAttribute('style', 'opacity:0.4; cursor:progress;');

		// traitement ajax
		var xhr = new XMLHttpRequest();
		xhr.open('POST', action + '?isAjax=true', true);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		xhr.onreadystatechange = function () {

			if (xhr.readyState === 4) {
				if ([0, 200].has(xhr.status)) {
					if (xhr.responseText.indexOf('success-') === 0)
						apijsOpenMage.updateForm(xhr.responseText);
					else
						apijsOpenMage.error(xhr.responseText);
				}
				else {
					apijsOpenMage.error(xhr.status);
				}
				document.getElementById('apijsGallery').removeAttribute('style');
			}
		};

		xhr.send(apijs.serialize(document.getElementById('product_edit_form'), 'apijs'));
	};

	this.updateForm = function (data) {

		var elem;

		// success-{json[result, bbcode]}
		if (data.indexOf('{') > -1) {
			data = JSON.parse(data.slice(data.indexOf('{')));
			if (data.bbcode)
				apijsOpenMage.error(data.bbcode);
		}

		// produit ou widget cms
		if (elem = document.getElementById('apijsGallery')) {

			elem.parentNode.innerHTML = data.html;
			elem.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function (elem) {
				elem.checked = elem.hasAttribute('checked');
				if (elem.hasAttribute('onchange'))
					apijsOpenMage.checkVal(elem);
			});

			apijs.slideshow.init();
			if (!apijs.dialog.has('error')) // ferme sauf en cas d'erreur
				apijs.dialog.actionClose();
		}
		else {
			MediabrowserInstance.selectFolder(MediabrowserInstance.currentNode);
		}
	};

	this.removeAttachment = function (action) {
		apijs.dialog.dialogConfirmation(apijs.i18n.translate(250), apijs.i18n.translate(251), apijsOpenMage.actionRemoveAttachment, action);
	};

	this.actionRemoveAttachment = function (args) {

		// args = action
		var xhr = new XMLHttpRequest();
		xhr.open('GET', args, true);

		xhr.onreadystatechange = function () {

			if (xhr.readyState === 4) {
				if ([0, 200].has(xhr.status)) {
					if (xhr.responseText.indexOf('success-') === 0)
						apijsOpenMage.updateForm(xhr.responseText);
					else
						apijsOpenMage.error(xhr.responseText);
				}
				else {
					apijsOpenMage.error(xhr.status);
				}
			}
		};

		xhr.send();
	};

	this.removeMedia = function (elem) {

		elem = elem.parentNode.parentNode;
		elem.click();
		if (!elem.classList.contains('selected'))
			elem.click();

		apijs.dialog.dialogConfirmation(apijs.i18n.translate(250), apijs.i18n.translate(251), apijsOpenMage.actionRemoveMedia, elem.id);
	};

	this.actionRemoveMedia = function (args) {

		// args = id
		// copie de MediabrowserInstance.deleteFiles(); ou presque
		new Ajax.Request(MediabrowserInstance.deleteFilesUrl, {
			parameters: { files: Object.toJSON([args]) },
			onSuccess: function (transport) {
				try {
					MediabrowserInstance.onAjaxSuccess(transport);
					MediabrowserInstance.selectFolder(MediabrowserInstance.currentNode);
				}
				catch (e) {
					alert(e.message);
				}
			}.bind(MediabrowserInstance)
		});
	};

	this.clearCache = function (action) {

		try {
			apijs.dialog.dialogConfirmation(apijs.i18n.translate(255), apijs.i18n.translate(256), apijsOpenMage.actionClearCache, action);
		}
		catch (e) {
			console.error(e);
			if (confirm(Translator.translate('Are you sure?')))
				self.location.href = action;
		}
	};

	this.actionClearCache = function (args) {
		apijs.dialog.remove('waiting', 'lock'); // obligatoire sinon demande de confirmation de quitter la page
		self.location.href = args;
	};

	this.checkVal = function (root) {

		var elem = root;
		while ((elem.nodeName !== 'BODY') && !elem.querySelector('.val'))
			elem = elem.parentNode;

		if (elem = elem.querySelector('.val')) {
			if (root.checked)
				elem.setAttribute('disabled', 'disabled');
			else
				elem.removeAttribute('disabled');
		}
	};

	this.overloadMediabrowser = function () {

		var objs = [];
		if (typeof Mediabrowser == 'function')
			objs.push(Mediabrowser);
		if (typeof MediabrowserInstance == 'object')
			objs.push(MediabrowserInstance);

		objs.forEach(function (obj) {

			if (typeof obj.origHideElement != 'function') {

				obj.origHideElement = obj.hideElement;
				obj.hideElement = function (name) {
					this.origHideElement(name);
					if (name === 'loading-mask') {
						document.getElementById('contents').classList.remove('no-display');
						document.getElementById('contents-loader').classList.add('no-display');
						if (!apijs.dialog.has('error')) // ferme sauf en cas d'erreur
							apijs.dialog.actionClose();
					}
				};

				obj.origShowElement = obj.showElement;
				obj.showElement = function (name) {
					this.origShowElement(name);
					if (name === 'loading-mask') {
						document.getElementById('contents').classList.add('no-display');
						document.getElementById('contents-loader').classList.remove('no-display');
					}
				};

				obj.origDrawBreadcrumbs = obj.drawBreadcrumbs;
				obj.drawBreadcrumbs = function (node) {
					this.origDrawBreadcrumbs(node);
					if (!document.getElementById('breadcrumbs')) {
						node = this.tree.getNodeById('root');
						$('content_header').insert({ after: '<ul class="breadcrumbs" id="breadcrumbs"><li><a href="#" onclick="MediabrowserInstance.selectFolderById(\'' + node.id + '\');">' + node.text + '</a></li></ul>' });
					}
				};
			}
		});

		MediabrowserInstance.deleteFilesUrl  = MediabrowserInstance.deleteFilesUrl.replace(/[a-z_]+\/deleteFiles\//, 'apijs_wysiwyg/deleteFiles/');
		MediabrowserInstance.deleteFolderUrl = MediabrowserInstance.deleteFolderUrl.replace(/[a-z_]+\/deleteFolder\//, 'apijs_wysiwyg/deleteFolder/');
	};

	this.toggleVisibility = function () {

		this.hide = !this.hide;

		document.getElementById('apijsGallery').querySelectorAll('input.exclude').forEach(function (elem) {

			var line = elem.parentNode.parentNode.parentNode;
			if (elem.checked)
				line.classList[this.hide ? 'add' : 'remove']('no-display');
			else
				line.classList.remove('no-display');

		}, this); // pour que ci-dessus this = this
	};

})();

if (typeof self.addEventListener == 'function')
	self.addEventListener('apijsload', apijsOpenMage.start.bind(apijsOpenMage));