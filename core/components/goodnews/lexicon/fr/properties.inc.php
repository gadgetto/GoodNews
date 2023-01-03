<?php
/**
 * GoodNews
 *
 * Copyright 2012 by bitego <office@bitego.com>
 *
 * GoodNews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * GoodNews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this software; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 */

/**
 * GoodNews properties
 *
 * @package goodnews
 * @subpackage lexicon
 * @language en
 */

// GoodNewsSubscription snippet
$_lang['prop_goodnewssubscription.activation_desc']                 = 'Activation requise ou non pour valider l\'inscription. Si oui, les inscrits ne seront pas marqués actifs jusqu\'à ce qu\'ils aient activé leur compte.';
$_lang['prop_goodnewssubscription.activationttl_desc']              = 'Nombre de minutes jusqu\' à ce que l\'email d\'action expire.';
$_lang['prop_goodnewssubscription.activationemail_desc']            = 'Si activé, cela enverra les emails d\'activation à cette adresse au lieu de l\'adresse du nouvel inscrit.';
$_lang['prop_goodnewssubscription.activationemailsubject_desc']     = 'Sujet de l\'email d\'activation.';
$_lang['prop_goodnewssubscription.activationemailtpl_desc']         = 'Modèle de l\'email d\'activation.';
$_lang['prop_goodnewssubscription.activationemailtplalt_desc']      = 'Alternative texte brut au modèle de l\'email d\'activation.';
$_lang['prop_goodnewssubscription.activationemailtpltype_desc']     = 'Type de modèle pour l\'email d\'activation.';
$_lang['prop_goodnewssubscription.activationresourceid_desc']       = 'ID de la Ressource où se trouve le snippet d\'activation GoodNewsConfirmSubscription.';
$_lang['prop_goodnewssubscription.submittedresourceid_desc']        = 'Redirige après l\'envoi du formulaire vers la Ressource dont l\'ID est spécifiée ici.';
$_lang['prop_goodnewssubscription.sendsubscriptionemail_desc']      = 'Envoyer ou non un email à l\'inscrit après une activation réussie.';
$_lang['prop_goodnewssubscription.unsubscriberesourceid_desc']      = 'ID de la ressource pour annuler la souscription.';
$_lang['prop_goodnewssubscription.profileresourceid_desc']          = 'ID de la ressource pour éditer le profil de souscription.';
$_lang['prop_goodnewssubscription.subscriptionemailsubject_desc']   = 'Sujet de l\'email de réussite.';
$_lang['prop_goodnewssubscription.subscriptionemailtpl_desc']       = 'Modèle de l\'email de réussite.';
$_lang['prop_goodnewssubscription.subscriptionemailtplalt_desc']    = 'Alternative texte brut au modèle de l\'email de réussite.';
$_lang['prop_goodnewssubscription.subscriptionemailtpltype_desc']   = 'Type de modèle de l\'email de réussite.';
$_lang['prop_goodnewssubscription.resubscriptionemailsubject_desc'] = 'The subject of the renewal email.';
$_lang['prop_goodnewssubscription.resubscriptionemailtpl_desc']     = 'The renewal email template.';
$_lang['prop_goodnewssubscription.resubscriptionemailtplalt_desc']  = 'Plain-text alternative for the renewal email template.';
$_lang['prop_goodnewssubscription.resubscriptionemailtpltype_desc'] = 'The template-type for the renewal email.';
$_lang['prop_goodnewssubscription.errtpl_desc']                     = 'Modèle pour la sortie du message d\'erreur dans les champs.';
$_lang['prop_goodnewssubscription.useextended_desc']                = 'Définir les champs additionnels du formulaire pour étendre les champs du profil utilisateur MODx. Utile pour stocker des données supplémentaires.';
$_lang['prop_goodnewssubscription.excludeextended_desc']            = 'Liste de champs séparés par une virgule à exclure des champs additionnels.';
$_lang['prop_goodnewssubscription.emailfield_desc']                 = 'Nom du champs à utiliser pour la nouvelle adresse email de l\'inscrit. ';
$_lang['prop_goodnewssubscription.usernamefield_desc']              = 'Name of the field to use for the new Subscribers&apos;s username.';
$_lang['prop_goodnewssubscription.passwordfield_desc']              = 'Name of the field to use for the new Subscribers&apos;s password.';
$_lang['prop_goodnewssubscription.persistparams_desc']              = 'Objet de paramètres qui persistent au long du processus d\'inscription. Utile lors de l\'utilisation de redirection dans le snippet GoodNewsConfirmSubscription pour rediriger vers une autre page (p.ex. pour les paniers de shop).';
$_lang['prop_goodnewssubscription.prehooks_desc']                   = 'Liste de scripts séparés par une virgule à lancer avant la validation du formulaire. Si un script échoue, les suivants ne seront pas lancés. Un hook peut aussi être un nom de Snippet.';
$_lang['prop_goodnewssubscription.posthooks_desc']                  = 'Liste de scripts séparés par une virgule à lancer après l\'inscription de l\'utilisateur. Si un script échoue, les suivants ne seront pas lancés. Un hook peut aussi être un nom de Snippet.';
$_lang['prop_goodnewssubscription.redirectback_desc']               = '';
$_lang['prop_goodnewssubscription.redirectbackparams_desc']         = '';
$_lang['prop_goodnewssubscription.submitvar_desc']                  = 'Variable à vérifier : Si vide ou défini à False, le snippet traitera le formulaire avec toutes les variables POST.';
$_lang['prop_goodnewssubscription.successmsg_desc']                 = 'Si pas de redirection utilisant le paramètres submittedResourceId, cela affichera ce message à la place.';
$_lang['prop_goodnewssubscription.usergroups_desc']                 = 'Une liste de noms de groupe d\'utilisateur MODx ou ID séparés par une virgule auxquels ajouter automatiquement le nouvel utilisateur inscrit.';
$_lang['prop_goodnewssubscription.usergroupsfield_desc']            = 'Nom du champ pour spécifier à quel(s) groupe(s) d\'utilisateur MODx ajouter automatiquement un nouvel inscrit. Uniquement utilisé si pas laissé vide.';
$_lang['prop_goodnewssubscription.validate_desc']                   = 'Liste des champs à valider séparés par une virgule, chaque champ sous la forme nom:validateur (ex: fullname:required, email:required). Les validateurs peuvent aussi être chaînés : email:email:required.';
$_lang['prop_goodnewssubscription.grpfieldsettpl_desc']             = 'Modèle à utiliser pour le set de champs (fieldset) d\'un formulaire de groupe GoodNews.';
$_lang['prop_goodnewssubscription.grpnametpl_desc']                 = 'Modèle à utiliser pour les noms de groupe GoodNews (sans champ de formulaire).';
$_lang['prop_goodnewssubscription.grpfieldtpl_desc']                = 'Modèle à utiliser pour les champs case à cocher (checkbox) de formulaire de groupe GoodNews.';
$_lang['prop_goodnewssubscription.grpfieldhiddentpl_desc']          = 'Modèle à utiliser pour les champs cachés de formulaire de groupe GoodNews.';
$_lang['prop_goodnewssubscription.catfieldtpl_desc']                = 'Modèle à utiliser pour les cases à cocher de formulaire de catégorie GoodNews.';
$_lang['prop_goodnewssubscription.catfieldhiddentpl_desc']          = 'Modèle à utiliser pour les champs cachés de formulaire de catégorie GoodNews.';
$_lang['prop_goodnewssubscription.groupsonly_desc']                 = 'Si défini à OUI, les groupes GoodNews uniquement seront utilisés pour l\'inscription.';
$_lang['prop_goodnewssubscription.includegroups_desc']              = 'Liste d\'IDs de groupe GoodNews séparées par une virgule qui seront exclusivement utilisées.';
$_lang['prop_goodnewssubscription.defaultgroups_desc']              = 'Liste d\'IDs de groupe GoodNews séparées par une virgule qui seront obligatoirement utilisées. Les inscrits seront ajoutés automatiquement à ces groupes. Les champs de formulaire seront cachés.';
$_lang['prop_goodnewssubscription.defaultcategories_desc']          = 'Liste des IDs de catégories GoodNews séparées par une virgule, qui seront utilisées obligatoirement. Les inscrits seront ajoutés automatiquement à ces catégories. Les champs de formulaire seront cachés.';
$_lang['prop_goodnewssubscription.sort_desc']                       = 'Nom du champs utilisé pour trier les champs de catégories et groupes GoodNews.';
$_lang['prop_goodnewssubscription.dir_desc']                        = 'Direction du tri des champs des groupes et catégories GoodNews.';
$_lang['prop_goodnewssubscription.grpcatplaceholder_desc']          = 'Nom du placeholder qui contient tous les champs de formulaire des groupes et catégories GoodNews.';
$_lang['prop_goodnewssubscription.placeholderprefix_desc']          = 'Préfixe à utiliser pour tous les placeholders définis par ce snippet.';
$_lang['prop_goodnewssubscription.errorpage_desc']                  = 'Si défini, cela redirigera l\'utilisateur vers une page d\'erreur personnalisée.';

// GoodNewsConfirmSubscription snippet
$_lang['prop_goodnewsconfirmsubscription.sendsubscriptionemail_desc']      = 'Envoyer ou non un email à l\'inscrit après une activation réussie.';
$_lang['prop_goodnewsconfirmsubscription.unsubscriberesourceid_desc']      = 'ID de la ressource pour annuler la souscription.';
$_lang['prop_goodnewsconfirmsubscription.profileresourceid_desc']          = 'ID de la ressource pour éditer le profil de souscription.';
$_lang['prop_goodnewsconfirmsubscription.subscriptionemailsubject_desc']   = 'Sujet de l\'email de réussite.';
$_lang['prop_goodnewsconfirmsubscription.subscriptionemailtpl_desc']       = 'Modèle de l\'email de réussite.';
$_lang['prop_goodnewsconfirmsubscription.subscriptionemailtplalt_desc']    = 'Alternative texte brut au modèle de l\'email de réussite.';
$_lang['prop_goodnewsconfirmsubscription.subscriptionemailtpltype_desc']   = 'Type de modèle de l\'email de réussite.';
$_lang['prop_goodnewsconfirmsubscription.errorpage_desc']                  = 'Si défini, cela redirigera l\'utilisateur vers une page d\'erreur personnalisée s\'ils essaient d\'accéder à cette page après avoir activé leur compte.';

// GoodNewsUpdateProfile snippet
$_lang['prop_goodnewsupdateprofile.errtpl_desc']                    = 'Modèle pour l\'affichage de message d\'erreur dans les champs.';
$_lang['prop_goodnewsupdateprofile.useextended_desc']               = 'Définir ou non des champs additionnels dans le formulaire pour le champs étendu des Profils.';
$_lang['prop_goodnewsupdateprofile.excludeextended_desc']           = 'Liste des champs séparés par une virgule à exclure dans la définition des champs étendus.';
$_lang['prop_goodnewsupdateprofile.emailfield_desc']                = 'Le nom du champ du champ email dans le formulaire.';
$_lang['prop_goodnewsupdateprofile.prehooks_desc']                  = 'Liste des scripts à exécuter séparés par une virgule, avant que le formulaire passe la validation. Si un script échoue, les suivants ne seront pas exécutés. Un hook peut aussi être le nom d\'un Snippet.';
$_lang['prop_goodnewsupdateprofile.posthooks_desc']                 = 'Liste des scripts à exécuter séparés par une virgule, après la mise à jour du profil. Si un script échoue, les suivants ne seront pas exécutés. Un hook peut aussi être le nom d\'un Snippet.';
$_lang['prop_goodnewsupdateprofile.sendunauthorizedpage_desc']      = 'Si un utilisateur n\'est pas identifié par le SID donné depuis l\'email, le rediriger vers la page d\'accès refusé.';
$_lang['prop_goodnewsupdateprofile.reloadonsuccess_desc']           = 'Si vrai, la page sera redirigée vers elle-même avec un paramètre GET pour éviter les doubles Postbacks. Si Faux, cela définira simplement un placeholder de réuissite.';
$_lang['prop_goodnewsupdateprofile.submitvar_desc']                 = 'Variable à vérifier : Si vide ou défini à False, le snippet traitera le formulaire avec toutes les variables POST.';
$_lang['prop_goodnewsupdateprofile.successkey_desc']                = 'Nom de la clé qui sera envoyée comme paramètre d\'URL avec la valeur true si la mise à jour est réussie.';
$_lang['prop_goodnewsupdateprofile.successmsg_desc']                = 'Message affiché par le processus si la mise à jour est réussie.';
$_lang['prop_goodnewsupdateprofile.validate_desc']                  = 'Liste de champs à valider séparés par une virgule, chaque champ sous la forme nom:validateur (ex: fullname:required, email:required). Les validateurs peuvent aussi être chaînés : email:email:required.';
$_lang['prop_goodnewsupdateprofile.grpfieldsettpl_desc']            = 'Modèle à utiliser pour le set de champs (fieldset) d\'un formulaire de groupe GoodNews.';
$_lang['prop_goodnewsupdateprofile.grpnametpl_desc']                = 'Modèle à utiliser pour les noms de groupe GoodNews (sans champ de formulaire).';
$_lang['prop_goodnewsupdateprofile.grpfieldtpl_desc']               = 'Modèle à utiliser pour les champs case à cocher (checkbox) de formulaire de groupe GoodNews.';
$_lang['prop_goodnewsupdateprofile.grpfieldhiddentpl_desc']         = 'Modèle à utiliser pour les champs cachés de formulaire de groupe GoodNews.';
$_lang['prop_goodnewsupdateprofile.catfieldtpl_desc']               = 'Modèle à utiliser pour les cases à cocher de formulaire de catégorie GoodNews.';
$_lang['prop_goodnewsupdateprofile.catfieldhiddentpl_desc']         = 'Modèle à utiliser pour les champs cachés de formulaire de catégorie GoodNews.';
$_lang['prop_goodnewsupdateprofile.groupsonly_desc']                = 'Si défini à OUI, les groupes GoodNews uniquement seront utilisés pour l\'inscription.';
$_lang['prop_goodnewsupdateprofile.includegroups_desc']             = 'Liste d\'IDs de groupe GoodNews séparées par une virgule qui seront exclusivement utilisées.';
$_lang['prop_goodnewsupdateprofile.defaultgroups_desc']             = 'Liste d\'IDs de groupe GoodNews séparées par une virgule qui seront obligatoirement utilisées. Les inscrits seront ajoutés automatiquement à ces groupes. Les champs de formulaire seront cachés.';
$_lang['prop_goodnewsupdateprofile.defaultcategories_desc']         = 'Liste des IDs de catégories GoodNews séparées par une virgule, qui seront utilisées obligatoirement. Les inscrits seront ajoutés automatiquement à ces catégories. Les champs de formulaire seront cachés.';
$_lang['prop_goodnewsupdateprofile.sort_desc']                      = 'Nom du champs utilisé pour trier les champs de catégories et groupes GoodNews.';
$_lang['prop_goodnewsupdateprofile.dir_desc']                       = 'Direction du tri des champs des groupes et catégories GoodNews.';
$_lang['prop_goodnewsupdateprofile.grpcatplaceholder_desc']         = 'Nom du placeholder qui contient tous les champs de formulaire des groupes et catégories GoodNews.';
$_lang['prop_goodnewsupdateprofile.placeholderprefix_desc']         = 'Préfixe à utiliser pour tous les placeholders définis par ce snippet.';

// GoodNewsUnSubscription snippet
$_lang['prop_goodnewsunsubscription.errtpl_desc']                   = 'Modèle pour l\'affichage de message d\'erreur dans les champs.';
$_lang['prop_goodnewsunsubscription.prehooks_desc']                 = 'Scripts à exécuter avant que le formulaire passe la validation. Peut être une liste de hooks séparés par une virgule et si le premier échoue, les suivants ne seront pas exécutés. Un hook peut aussi être le nom d\'un Snippet.';
$_lang['prop_goodnewsunsubscription.posthooks_desc']                = 'Scripts à exécuter après que l\'ut s\'est inscrit. Peut être une liste de hooks séparés par une virgule et si le premier échoue, les suivants ne seront pas exécutés. Un hook peut aussi être le nom d\'un Snippet.';
$_lang['prop_goodnewsunsubscription.sendunauthorizedpage_desc']     = 'Si un utilisateur n\'est pas identifié par le SID donné depuis l\'email, le rediriger vers la page d\'accès refusé.';
$_lang['prop_goodnewsunsubscription.submitvar_desc']                = 'Variable à vérifier : Si vide ou défini à False, le snippet traitera le formulaire avec toutes les variables POST.';
$_lang['prop_goodnewsunsubscription.successkey_desc']               = 'Nom de la clé qui sera envoyée comme paramètre d\'URL avec la valeur True si la désinscription a réussi.';
$_lang['prop_goodnewsunsubscription.removeuserdata_desc']           = 'Si défini, toutes les données-utilisateur seront effacées de la base de données MODx si un utilisateur se désinscrit. Dans le cas contraire, seules les données relatives à GoodNews seront effacées et l\'utilisateur sera désactivé.';
$_lang['prop_goodnewsunsubscription.placeholderprefix_desc']        = 'Préfixe à utiliser pour tous les Placeholders définis par ce snippet.';

// GoodNewsGetNewsletters snippet
$_lang['prop_goodnewsgetnewsletters.parent_desc']                   = 'ID du conteneur de mailing depuis lequel recevoir des documents newsletter. Si vide, l\'ID du conteneur actuel est utilisé.';
$_lang['prop_goodnewsgetnewsletters.tpl_desc']                      = 'Nom du modèle pour une rangée de ressource Newsletter. NOTE : Si non fourni, les propriétés sont affichées pour chaque ressource.';
$_lang['prop_goodnewsgetnewsletters.sortby_desc']                   = 'Nom du champ selon lequel trier, ou un objet JSON, ou les noms de champ et leur ordre de tri pour chaque champ, par exemple : {"publishedon":"ASC","createdon":"DESC"}. Par défaut publishedon.';
$_lang['prop_goodnewsgetnewsletters.sortdir_desc']                  = 'Ordre selon lequel faire le tri. Par défaut Décroissant.';
$_lang['prop_goodnewsgetnewsletters.includecontent_desc']           = 'Indique si le contenu de chaque newsletter doit être affiché dans les résultats. Par défaut : False.';
$_lang['prop_goodnewsgetnewsletters.limit_desc']                    = 'Limite le nombre des ressources newsletter affichées. Par défaut : 0 = illimité.';
$_lang['prop_goodnewsgetnewsletters.offset_desc']                   = 'Décalage des ressources affichées par le critère de saut.';
$_lang['prop_goodnewsgetnewsletters.totalvar_desc']                 = 'Nom du placeholder qui contient le nombre des ressources newsletter reçues.';
$_lang['prop_goodnewsgetnewsletters.outputseparator_desc']          = 'Séparateur pour la sortie des rangées de chunks de newsletter';
$_lang['prop_goodnewsgetnewsletters.toplaceholder_desc']            = 'Si défini, assigne le résultat à ce placeholder ou lieu de l\'afficher directement.';
$_lang['prop_goodnewsgetnewsletters.debug_desc']                    = 'Si vrai, envoie la requête SQL dans le log MODx . Par défaut : False.';

// GoodNewsContentCollection snippet
$_lang['prop_goodnewscontentcollection.collectionid_desc']          = 'Nom interne de la collection de contenu (collection1, collection2 ou collection3).';
$_lang['prop_goodnewscontentcollection.tpl_desc']                   = 'Nom du Chunk servant de modèle pour une rangée de ressources. NOTE: Si infourni, les propriétés sont affichées pour chaque ressource.';
$_lang['prop_goodnewscontentcollection.tplwrapper_desc']            = 'Nom du Chunk servant de modèle de wrapper pour la sortie du Snippet.';
$_lang['prop_goodnewscontentcollection.sortby_desc']                = 'Nom du champ selon lequel trier, ou un objet JSON, ou les noms de champ et leur ordre de tri pour chaque champ, par exemple : {"publishedon":"ASC","createdon":"DESC"}. Par défaut publishedon.';
$_lang['prop_goodnewscontentcollection.sortdir_desc']               = 'Ordre de tri. Décroissant (DESC) par défaut.';
$_lang['prop_goodnewscontentcollection.includecontent_desc']        = 'Indique si le contenu de chaque newsletter doit être affiché dans les résultats. Par défaut : False.';
$_lang['prop_goodnewscontentcollection.outputseparator_desc']       = 'Séparateur pour l\'affichage des rangées de chunks.';
$_lang['prop_goodnewscontentcollection.toplaceholder_desc']         = 'Si défini, assigne le résultat à ce placeholder ou lieu de l\'afficher directement.';
$_lang['prop_goodnewscontentcollection.debug_desc']                 = 'Si vrai, envoie la requête SQL dans le log MODx . Par défaut : False.';

// GoodNewsRequestLinks snippet
$_lang['prop_goodnewsrequestlinks.unsubscriberesourceid_desc']      = 'ID de la ressource pour annuler la souscription.';
$_lang['prop_goodnewsrequestlinks.profileresourceid_desc']          = 'ID de la ressource pour éditer le profil de souscription.';
$_lang['prop_goodnewsrequestlinks.submittedresourceid_desc']        = 'Rediriger vers la ressource dont l\ID est spécifiée ici, après que l\'inscrit a envoyé le formulaire.';
$_lang['prop_goodnewsrequestlinks.requestlinksemailsubject_desc']   = 'Sujet de l\'email envoyé à la personne qui demande les liens pour éditer son profil d\'inscription.';
$_lang['prop_goodnewsrequestlinks.requestlinksemailtpl_desc']       = 'Modèle de l\'email de demande de liens.';
$_lang['prop_goodnewsrequestlinks.requestlinksemailtplalt_desc']    = 'Alternative texte brut au modèle de l\'email de demande de liens.';
$_lang['prop_goodnewsrequestlinks.requestlinksemailtpltype_desc']   = 'Type de modèle pour l\'email de demande de liens.';
$_lang['prop_goodnewsrequestlinks.errtpl_desc']                     = 'Modèle pour l\'affichage de message d\'erreur dans les champs.';
$_lang['prop_goodnewsrequestlinks.emailfield_desc']                 = 'Nom du champ à utiliser pour l\'adresse email.';
$_lang['prop_goodnewsrequestlinks.sendunauthorizedpage_desc']       = 'Si un utilisateur n\'est pas identifié par l\'adresse email soumise, le rediriger vers la page d\'accès non-autoris.';
$_lang['prop_goodnewsrequestlinks.submitvar_desc']                  = 'Variable à vérifier : Si vide ou défini à False, le snippet traitera le formulaire avec toutes les variables POST.';
$_lang['prop_goodnewsrequestlinks.successmsg_desc']                 = 'Si aucune redirection utilisant le paramètre submittedResourceId, ce message sera affiché à la place.';
$_lang['prop_goodnewsrequestlinks.validate_desc']                   = 'Liste de champs à valider séparés par une virgule, chaque champ sous la forme nom:validateur (ex: fullname:required, email:required). Les validateurs peuvent aussi être chaînés : email:email:required.';
$_lang['prop_goodnewsrequestlinks.placeholderprefix_desc']          = 'Préfixe à utiliser pour tous les Placeholders définis par ce snippet.';

// List options
$_lang['opt_goodnews.chunk']    = 'Chunk';
$_lang['opt_goodnews.file']     = 'Fichier';
$_lang['opt_goodnews.inline']   = 'Inline';
$_lang['opt_goodnews.embedded'] = 'Intégré';
$_lang['opt_goodnews.asc']      = 'Croissant';
$_lang['opt_goodnews.desc']     = 'Décroissant';
