<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * French language strings for the local_delegateaccount plugin.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = 'Actions';
$string['active_delegations'] = 'Délégations actives';
$string['activity_action'] = 'Action';
$string['activity_component'] = 'Composant';
$string['activity_event'] = 'Événement';
$string['activity_target'] = 'Cible';
$string['activity_time'] = 'Date et heure';
$string['add_delegation'] = 'Ajouter un compte délégué';
$string['allowopenended'] = 'Autoriser les délégations sans date de fin';
$string['allowopenended_desc'] = 'Lorsque cette option est désactivée, chaque délégation doit avoir une date de fin.';
$string['authoriseduser'] = 'Personne autorisée';
$string['bulk_deleted_success'] = 'Les délégations sélectionnées ont été supprimées avec succès.';
$string['confirm_bulk_delete'] = 'Voulez-vous vraiment supprimer toutes les délégations sélectionnées ?';
$string['confirm_delete'] = 'Voulez-vous vraiment supprimer cette délégation ?';
$string['create_delegations'] = 'Créer de nouvelles délégations';
$string['delegateaccount:create'] = 'Créer des délégations de comptes';
$string['delegateaccount:manage'] = 'Gérer les délégations de comptes';
$string['delegateaccount:revoke'] = 'Révoquer des délégations de comptes';
$string['delegateaccount:update'] = 'Mettre à jour des délégations de comptes';
$string['delegateaccount:use'] = 'Se connecter en tant que compte délégué';
$string['delegateaccount:view'] = 'Consulter les délégations de comptes';
$string['delegateaccount:viewactivity'] = 'Consulter l\'activité des comptes délégués';
$string['delegated_accounts_for'] = 'Comptes délégués de {$a}';
$string['delegated_activity_description'] = 'Ce rapport présente les événements enregistrés par Moodle pendant que {$a->authoriseduser} utilisait le compte délégué {$a->delegateduser}. Le magasin de journaux du site reste la source de référence.';
$string['delegated_activity_for'] = 'Activité déléguée : {$a->authoriseduser} en tant que {$a->delegateduser}';
$string['delegateduser'] = 'Compte délégué';
$string['delegatedusers'] = 'Comptes délégués';
$string['delegatedusers_help'] = 'Sélectionnez les comptes de destination. Les personnes sélectionnées ci-dessus pourront se connecter et agir en tant que ces comptes.';
$string['delegation_count'] = 'Comptes actuels ou planifiés';
$string['delegation_created'] = 'Créée';
$string['delegation_created_by'] = 'Créée par';
$string['delegation_details'] = 'Détails de la délégation';
$string['delegation_end'] = 'Fin de l\'accès';
$string['delegation_filter_none'] = 'Aucun enregistrement de délégation';
$string['delegation_filter_user'] = 'Informations utilisateur';
$string['delegation_modified'] = 'Dernière modification';
$string['delegation_modified_by'] = 'Modifiée par';
$string['delegation_no_end'] = 'Aucune date de fin';
$string['delegation_revoked'] = 'Révoquée';
$string['delegation_revoked_by'] = 'Révoquée par';
$string['delegation_start'] = 'Début de l\'accès';
$string['delegation_status'] = 'Statut';
$string['delegation_status_active'] = 'Active';
$string['delegation_status_expired'] = 'Expirée';
$string['delegation_status_revoked'] = 'Révoquée';
$string['delegation_status_scheduled'] = 'Planifiée';
$string['delegation_unknown_user'] = 'Utilisateur supprimé';
$string['delegation_updated_success'] = 'La délégation a été mise à jour avec succès.';
$string['delegationnotificationmode'] = 'Notifier les personnes concernées';
$string['delegationnotificationmode_always'] = 'Envoyer une notification';
$string['delegationnotificationmode_desc'] = 'Choisissez si cette délégation envoie une notification aux personnes concernées.';
$string['delegationnotificationmode_help'] = 'Choisissez si cette opération envoie une notification aux personnes concernées. Cette option apparaît uniquement lorsque la politique du site autorise ce choix.';
$string['delegationnotificationmode_never'] = 'Ne pas envoyer de notification';
$string['delegationnotificationsubject'] = 'Accès au compte délégué accordé';
$string['delegations_created_success'] = 'Les délégations ont été créées avec succès.';
$string['delegations_user_not_authorised'] = 'Cet utilisateur n\'est plus autorisé à utiliser des comptes délégués. Son historique de délégations reste disponible à la consultation, mais aucune nouvelle délégation ne peut être créée.';
$string['delete_selected'] = 'Supprimer la sélection';
$string['deleted_success'] = 'La délégation a été supprimée avec succès.';
$string['edit_delegation'] = 'Modifier la délégation';
$string['error_already_exists'] = 'Cette délégation existe déjà.';
$string['error_alreadyloggedinas'] = 'Vous devez revenir à votre compte d\'origine avant d\'utiliser un compte délégué.';
$string['error_ineligibleuser'] = 'Les utilisateurs supprimés ou suspendus ne peuvent pas participer à une délégation.';
$string['error_invalidperiod'] = 'La date de fin doit être postérieure à la date de début.';
$string['error_invalidtemplateplaceholder'] = 'Le modèle de notification contient un espace réservé non pris en charge : {$a}.';
$string['error_invaliduser'] = 'Un ou plusieurs utilisateurs sélectionnés n’existent plus.';
$string['error_maxbulkoperations'] = 'Une action groupée ne peut pas affecter plus de {$a} enregistrements de délégation.';
$string['error_maxdelegations'] = 'Un utilisateur autorisé ne peut pas avoir plus de {$a} comptes délégués actuels ou programmés.';
$string['error_maximumduration'] = 'Une délégation ne peut pas durer plus de {$a} jours.';
$string['error_openendednotallowed'] = 'Les délégations sans date de fin ne sont pas autorisées.';
$string['error_privilegedtarget'] = 'Les comptes administrateur du site ne peuvent pas être délégués.';
$string['error_unauthorised_realuser'] = 'Chaque utilisateur autorisé doit actuellement avoir le droit d\'utiliser des comptes délégués.';
$string['error_unauthorized'] = 'Vous n\'êtes pas autorisé à accéder à ce compte délégué.';
$string['eventdelegationcreated'] = 'Délégation de compte créée';
$string['eventdelegationrevoked'] = 'Délégation de compte révoquée';
$string['eventdelegationupdated'] = 'Délégation de compte mise à jour';
$string['last_delegated_access'] = 'Dernier accès délégué';
$string['manage_accounts'] = 'Gérer les comptes délégués';
$string['manage_authorised_users'] = 'Utilisateurs autorisés';
$string['manage_authorised_users_description'] = 'Tous les utilisateurs actifs qui ont actuellement le droit d\'utiliser des comptes délégués, y compris ceux qui n\'ont encore aucune délégation.';
$string['manage_historical_users'] = 'Utilisateurs sans autorisation';
$string['manage_historical_users_description'] = 'Utilisateurs qui conservent des enregistrements de délégations mais n\'ont plus le droit d\'utiliser des comptes délégués. Leur historique et leurs rapports d\'activité restent disponibles à la consultation.';
$string['manage_user_delegations'] = 'Gérer les comptes délégués de cet utilisateur';
$string['maxbulkoperations'] = 'Nombre maximal d’enregistrements par action groupée';
$string['maxbulkoperations_desc'] = 'Nombre maximal d’enregistrements de délégation qu’une action groupée peut créer ou révoquer. Définissez 0 pour ne pas limiter.';
$string['maxdelegationsperuser'] = 'Nombre maximal de comptes délégués par utilisateur';
$string['maxdelegationsperuser_desc'] = 'Nombre maximal de comptes actuels ou programmés auxquels un utilisateur autorisé peut accéder. Définissez 0 pour ne pas limiter.';
$string['maximumdurationdays'] = 'Durée maximale de la délégation';
$string['maximumdurationdays_desc'] = 'Durée maximale en jours. Définissez 0 pour ne pas limiter.';
$string['messageprovider:delegationnotification'] = 'Notifications de comptes délégués';
$string['no_delegated_access'] = 'Aucun accès délégué enregistré';
$string['no_delegations'] = 'Aucune délégation de compte n\'a encore été créée.';
$string['no_delegations_created'] = 'Aucune nouvelle délégation n\'a été créée ; les doublons ont été ignorés.';
$string['notificationaccessends'] = 'Fin de l’accès :';
$string['notificationaccessgranted'] = 'Un accès à un compte délégué vous a été accordé.';
$string['notificationaccessstarts'] = 'Début de l’accès :';
$string['notificationaccountaccess'] = 'Vous pouvez désormais accéder à {$a->delegateduser} sur {$a->sitefullname}.';
$string['notificationgreeting'] = 'Bonjour {$a},';
$string['notificationpolicy'] = 'Politique de notification';
$string['notificationpolicy_always'] = 'Toujours avertir';
$string['notificationpolicy_desc'] = 'Détermine si le formulaire de délégation peut choisir d’envoyer une notification.';
$string['notificationpolicy_never'] = 'Ne jamais avertir';
$string['notificationpolicy_optional'] = 'Laisser la personne qui crée la délégation choisir';
$string['notificationrecipients'] = 'Destinataires de la notification';
$string['notificationrecipients_authorised'] = 'Utilisateur autorisé uniquement';
$string['notificationrecipients_both'] = 'Les deux utilisateurs';
$string['notificationrecipients_desc'] = 'Choisissez quels utilisateurs concernés reçoivent une notification de délégation.';
$string['notificationrecipients_target'] = 'Compte délégué uniquement';
$string['notificationsubject'] = 'Objet de la notification ({$a})';
$string['notificationsubject_desc'] = 'Objet en texte brut utilisé pour cette langue lorsqu’une notification de compte délégué est envoyée.';
$string['notificationsupportmessage'] = 'Si vous n’attendiez pas cet accès, contactez l’administration du site.';
$string['notificationtemplate'] = 'Modèle de notification ({$a})';
$string['notificationtemplate_desc'] = 'Contenu HTML facultatif qui remplace le message Moodle Mustache intégré pour cette langue. Laissez vide pour utiliser le message intégré. Espaces réservés disponibles : {$a->authoriseduser}, {$a->delegateduser}, {$a->actor}, {$a->timestart}, {$a->timeend}, {$a->sitefullname}.';
$string['notifyonrevocation'] = 'Avertir lors de la révocation d’une délégation';
$string['notifyonrevocation_desc'] = 'Envoie la notification configurée aux destinataires sélectionnés lorsque l’accès est révoqué.';
$string['pluginname'] = 'Compte délégué';
$string['privacy:metadata:local_delegateaccount'] = 'Stocke les délégations de comptes configurées par l\'administration du site.';
$string['privacy:metadata:local_delegateaccount:delegateduserid'] = 'Le compte auquel une personne peut accéder.';
$string['privacy:metadata:local_delegateaccount:notificationmode'] = 'Le choix de notification sélectionné pour la délégation.';
$string['privacy:metadata:local_delegateaccount:realuserid'] = 'La personne qui peut accéder à un compte délégué.';
$string['privacy:metadata:local_delegateaccount:timecreated'] = 'La date et l\'heure de création de la délégation.';
$string['privacy:metadata:local_delegateaccount:timeend'] = 'La date et l\'heure d\'expiration de la délégation.';
$string['privacy:metadata:local_delegateaccount:timemodified'] = 'La date et l\'heure de la dernière modification de la délégation.';
$string['privacy:metadata:local_delegateaccount:timerevoked'] = 'La date et l\'heure de révocation de la délégation.';
$string['privacy:metadata:local_delegateaccount:timestart'] = 'La date et l\'heure d\'activation de la délégation.';
$string['privacy:metadata:local_delegateaccount:usercreated'] = 'La personne administratrice qui a créé la délégation.';
$string['privacy:metadata:local_delegateaccount:usermodified'] = 'La personne ayant modifié la délégation en dernier.';
$string['privacy:metadata:local_delegateaccount:userrevoked'] = 'La personne ayant révoqué la délégation.';
$string['privacy:path:delegations'] = 'Délégations de comptes';
$string['privacy:role:creator'] = 'Créateur de la délégation';
$string['privacy:role:delegateduser'] = 'Compte délégué';
$string['privacy:role:modifier'] = 'Modificateur de la délégation';
$string['privacy:role:realuser'] = 'Utilisateur autorisé';
$string['privacy:role:revoker'] = 'Révocateur de la délégation';
$string['protectprivilegedtargets'] = 'Protéger les comptes administrateur du site';
$string['protectprivilegedtargets_desc'] = 'Empêche qu’un compte administrateur du site soit délégué à un autre utilisateur.';
$string['realuser'] = 'Utilisateur principal';
$string['realusers'] = 'Utilisateurs principaux';
$string['realusers_help'] = 'Sélectionnez les personnes qui pourront se connecter en tant qu\'un autre compte. Vous pouvez rechercher et sélectionner plusieurs utilisateurs.';
$string['revoke_delegation'] = 'Révoquer la délégation';
$string['scheduled_delegations'] = 'Délégations planifiées';
$string['search_authorised_users'] = 'Rechercher des utilisateurs autorisés';
$string['settings'] = 'Réglages des comptes délégués';
$string['settings_delegations'] = 'Contrôles de délégation';
$string['settings_delegations_desc'] = 'Définissez les limites appliquées lorsqu’un accès à un compte délégué est créé ou révoqué.';
$string['settings_notifications'] = 'Notifications';
$string['settings_notifications_desc'] = 'Choisissez quand les utilisateurs concernés sont avertis et fournissez un modèle pour chaque langue installée.';
$string['timecreated'] = 'Date de création';
$string['usermenulimit'] = 'Comptes délégués affichés dans le menu utilisateur';
$string['usermenulimit_desc'] = 'Nombre maximal de comptes délégués actifs affichés dans le menu utilisateur. Définissez 0 pour tous les afficher.';
$string['view_delegated_activity'] = 'Afficher l\'activité déléguée';
$string['view_delegation_details'] = 'Voir les détails de la délégation';
