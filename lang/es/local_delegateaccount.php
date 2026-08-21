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
 * Spanish language strings for the local_delegateaccount plugin.
 *
 * @package    local_delegateaccount
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['settings'] = 'Ajustes de cuentas delegadas';
$string['settings_delegations'] = 'Controles de delegación';
$string['settings_delegations_desc'] = 'Define los límites aplicables al crear o revocar accesos a cuentas delegadas.';
$string['maxdelegationsperuser'] = 'Máximo de cuentas delegadas por usuario';
$string['maxdelegationsperuser_desc'] = 'Número máximo de cuentas actuales o programadas a las que puede acceder un usuario autorizado. Usa 0 para no limitarlo.';
$string['allowopenended'] = 'Permitir delegaciones sin fecha de fin';
$string['allowopenended_desc'] = 'Si se desactiva, toda delegación debe tener una fecha de fin.';
$string['maximumdurationdays'] = 'Duración máxima de la delegación';
$string['maximumdurationdays_desc'] = 'Duración máxima en días. Usa 0 para no limitarla.';
$string['protectprivilegedtargets'] = 'Proteger cuentas administradoras del sitio';
$string['protectprivilegedtargets_desc'] = 'Impide delegar cuentas de administradores del sitio a otro usuario.';
$string['maxbulkoperations'] = 'Máximo de registros por acción masiva';
$string['maxbulkoperations_desc'] = 'Número máximo de registros de delegación que una acción masiva puede crear o revocar. Usa 0 para no limitarlo.';
$string['usermenulimit'] = 'Cuentas delegadas mostradas en el menú de usuario';
$string['usermenulimit_desc'] = 'Número máximo de cuentas delegadas activas mostradas en el menú de usuario. Usa 0 para mostrarlas todas.';
$string['settings_notifications'] = 'Notificaciones';
$string['settings_notifications_desc'] = 'Elige cuándo se notifica a las personas afectadas y proporciona una plantilla para cada idioma instalado.';
$string['notificationpolicy'] = 'Política de notificación';
$string['notificationpolicy_desc'] = 'Controla si el formulario de delegación puede elegir enviar una notificación.';
$string['notificationpolicy_optional'] = 'Permitir que quien crea la delegación elija';
$string['notificationpolicy_always'] = 'Notificar siempre';
$string['notificationpolicy_never'] = 'No notificar nunca';
$string['notificationrecipients'] = 'Destinatarios de la notificación';
$string['notificationrecipients_desc'] = 'Elige qué usuarios afectados reciben una notificación de delegación.';
$string['notificationrecipients_authorised'] = 'Solo usuario autorizado';
$string['notificationrecipients_target'] = 'Solo cuenta delegada';
$string['notificationrecipients_both'] = 'Ambos usuarios';
$string['notifyonrevocation'] = 'Notificar al revocar una delegación';
$string['notifyonrevocation_desc'] = 'Envía la notificación configurada a los destinatarios seleccionados cuando se revoca el acceso.';
$string['notificationtemplate'] = 'Plantilla de notificación ({$a})';
$string['notificationtemplate_desc'] = 'Contenido HTML opcional que sustituye el mensaje integrado de Moodle Mustache para este idioma. Déjalo vacío para usar el mensaje integrado. Variables disponibles: {$a->authoriseduser}, {$a->delegateduser}, {$a->actor}, {$a->timestart}, {$a->timeend}, {$a->sitefullname}.';
$string['notificationgreeting'] = 'Hola {$a},';
$string['notificationaccessgranted'] = 'Se te ha concedido acceso a una cuenta delegada.';
$string['notificationaccountaccess'] = 'Ahora puedes acceder a {$a->delegateduser} en {$a->sitefullname}.';
$string['notificationaccessstarts'] = 'El acceso comienza:';
$string['notificationaccessends'] = 'El acceso finaliza:';
$string['notificationsupportmessage'] = 'Si no esperabas este acceso, ponte en contacto con la administración del sitio.';
$string['messageprovider:delegationnotification'] = 'Notificaciones de cuentas delegadas';
$string['error_openendednotallowed'] = 'No se permiten delegaciones sin fecha de fin.';
$string['error_maximumduration'] = 'Una delegación no puede durar más de {$a} días.';
$string['error_invaliduser'] = 'Uno o más usuarios seleccionados ya no existen.';
$string['error_ineligibleuser'] = 'Los usuarios eliminados o suspendidos no pueden participar en una delegación.';
$string['error_privilegedtarget'] = 'No se pueden delegar las cuentas administradoras del sitio.';
$string['error_maxdelegations'] = 'Un usuario autorizado no puede tener más de {$a} cuentas delegadas actuales o programadas.';
$string['error_maxbulkoperations'] = 'Una acción masiva no puede afectar a más de {$a} registros de delegación.';
$string['error_invalidtemplateplaceholder'] = 'La plantilla de notificación contiene una variable no admitida: {$a}.';
$string['delegationnotificationsubject'] = 'Acceso a cuenta delegada concedido';
$string['notificationsubject'] = 'Asunto de la notificación ({$a})';
$string['notificationsubject_desc'] = 'Asunto de texto sin formato usado para este idioma al enviar una notificación de cuenta delegada.';

$string['actions'] = 'Acciones';
$string['bulk_deleted_success'] = 'Las delegaciones seleccionadas se han eliminado correctamente.';
$string['confirm_bulk_delete'] = '¿Está seguro de que desea eliminar todas las delegaciones seleccionadas?';
$string['confirm_delete'] = '¿Está seguro de que desea eliminar esta delegación?';
$string['create_delegations'] = 'Crear nuevas delegaciones';
$string['delegateaccount:manage'] = 'Gestionar delegaciones de cuentas';
$string['delegateaccount:use'] = 'Acceder como una cuenta delegada';
$string['delegateduser'] = 'Cuenta delegada';
$string['delegatedusers'] = 'Cuentas delegadas';
$string['delegatedusers_help'] = 'Seleccione las cuentas de destino. Las personas seleccionadas anteriormente podrán acceder y actuar como estas cuentas.';
$string['delegations_created_success'] = 'Las delegaciones se han creado correctamente.';
$string['delete_selected'] = 'Eliminar las seleccionadas';
$string['deleted_success'] = 'La delegación se ha eliminado correctamente.';
$string['error_already_exists'] = 'Esta delegación ya existe.';
$string['error_alreadyloggedinas'] = 'Debe volver a su cuenta original antes de usar una cuenta delegada.';
$string['error_unauthorized'] = 'No tiene permiso para acceder a esta cuenta delegada.';
$string['manage_accounts'] = 'Gestionar cuentas delegadas';
$string['no_delegations'] = 'Aún no se ha creado ninguna delegación de cuentas.';
$string['no_delegations_created'] = 'No se creó ninguna delegación nueva; se ignoraron los duplicados.';
$string['pluginname'] = 'Cuenta delegada';
$string['privacy:metadata:local_delegateaccount'] = 'Almacena las delegaciones de cuentas configuradas por la administración del sitio.';
$string['privacy:metadata:local_delegateaccount:delegateduserid'] = 'La cuenta a la que puede acceder una persona.';
$string['privacy:metadata:local_delegateaccount:realuserid'] = 'La persona que puede acceder a una cuenta delegada.';
$string['privacy:metadata:local_delegateaccount:timecreated'] = 'La fecha y hora en que se creó la delegación.';
$string['privacy:metadata:local_delegateaccount:usercreated'] = 'La persona administradora que creó la delegación.';
$string['privacy:path:delegations'] = 'Delegaciones de cuentas';
$string['privacy:role:creator'] = 'Creador de la delegación';
$string['privacy:role:delegateduser'] = 'Cuenta delegada';
$string['privacy:role:realuser'] = 'Usuario autorizado';
$string['realuser'] = 'Usuario principal';
$string['realusers'] = 'Usuarios principales';
$string['realusers_help'] = 'Seleccione las personas que podrán acceder como otra cuenta. Puede buscar y seleccionar varios usuarios.';
$string['timecreated'] = 'Fecha de creación';
$string['delegateaccount:create'] = 'Crear delegaciones de cuentas';
$string['delegateaccount:revoke'] = 'Revocar delegaciones de cuentas';
$string['delegateaccount:update'] = 'Actualizar delegaciones de cuentas';
$string['delegateaccount:view'] = 'Ver delegaciones de cuentas';
$string['delegateaccount:viewactivity'] = 'Ver la actividad de cuentas delegadas';
$string['eventdelegationcreated'] = 'Delegación de cuenta creada';
$string['eventdelegationrevoked'] = 'Delegación de cuenta revocada';
$string['eventdelegationupdated'] = 'Delegación de cuenta actualizada';
$string['privacy:metadata:local_delegateaccount:notificationmode'] = 'La decisión de notificación seleccionada para la delegación.';
$string['privacy:metadata:local_delegateaccount:timeend'] = 'La fecha y hora en que expira la delegación.';
$string['privacy:metadata:local_delegateaccount:timemodified'] = 'La fecha y hora del último cambio de la delegación.';
$string['privacy:metadata:local_delegateaccount:timerevoked'] = 'La fecha y hora en que se revocó la delegación.';
$string['privacy:metadata:local_delegateaccount:timestart'] = 'La fecha y hora en que la delegación se activa.';
$string['privacy:metadata:local_delegateaccount:usermodified'] = 'La persona que modificó por última vez la delegación.';
$string['privacy:metadata:local_delegateaccount:userrevoked'] = 'La persona que revocó la delegación.';
$string['privacy:role:modifier'] = 'Modificador de la delegación';
$string['privacy:role:revoker'] = 'Revocador de la delegación';
