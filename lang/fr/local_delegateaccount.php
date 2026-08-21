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

$string['pluginname'] = 'Compte délégué';
$string['delegateaccount:use'] = 'Se connecter en tant que compte délégué';
$string['delegateaccount:manage'] = 'Gérer les délégations de comptes';
$string['manage_accounts'] = 'Gérer les comptes délégués';
$string['create_delegations'] = 'Créer de nouvelles délégations';
$string['no_delegations'] = 'Aucune délégation de compte n\'a encore été créée.';
$string['no_delegations_created'] = 'Aucune nouvelle délégation n\'a été créée ; les doublons ont été ignorés.';
$string['realuser'] = 'Utilisateur principal';
$string['realusers'] = 'Utilisateurs principaux';
$string['delegateduser'] = 'Compte délégué';
$string['delegatedusers'] = 'Comptes délégués';
$string['timecreated'] = 'Date de création';
$string['actions'] = 'Actions';
$string['realusers_help'] = 'Sélectionnez les personnes qui pourront se connecter en tant qu\'un autre compte. Vous pouvez rechercher et sélectionner plusieurs utilisateurs.';
$string['delegatedusers_help'] = 'Sélectionnez les comptes de destination. Les personnes sélectionnées ci-dessus pourront se connecter et agir en tant que ces comptes.';
$string['delegations_created_success'] = 'Les délégations ont été créées avec succès.';
$string['deleted_success'] = 'La délégation a été supprimée avec succès.';
$string['bulk_deleted_success'] = 'Les délégations sélectionnées ont été supprimées avec succès.';
$string['error_already_exists'] = 'Cette délégation existe déjà.';
$string['error_alreadyloggedinas'] = 'Vous devez revenir à votre compte d\'origine avant d\'utiliser un compte délégué.';
$string['error_unauthorized'] = 'Vous n\'êtes pas autorisé à accéder à ce compte délégué.';
$string['confirm_delete'] = 'Voulez-vous vraiment supprimer cette délégation ?';
$string['confirm_bulk_delete'] = 'Voulez-vous vraiment supprimer toutes les délégations sélectionnées ?';
$string['delete_selected'] = 'Supprimer la sélection';
$string['privacy:metadata:local_delegateaccount'] = 'Stocke les délégations de comptes configurées par l\'administration du site.';
$string['privacy:metadata:local_delegateaccount:realuserid'] = 'La personne qui peut accéder à un compte délégué.';
$string['privacy:metadata:local_delegateaccount:delegateduserid'] = 'Le compte auquel une personne peut accéder.';
$string['privacy:metadata:local_delegateaccount:usercreated'] = 'La personne administratrice qui a créé la délégation.';
$string['privacy:metadata:local_delegateaccount:timecreated'] = 'La date et l\'heure de création de la délégation.';
$string['privacy:path:delegations'] = 'Délégations de comptes';
$string['privacy:role:realuser'] = 'Utilisateur autorisé';
$string['privacy:role:delegateduser'] = 'Compte délégué';
$string['privacy:role:creator'] = 'Créateur de la délégation';
