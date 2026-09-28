/**
 * Dynamic AMD module for user selector targeting only permitted delegations.
 *
 * @module     local_delegateaccount/form_user_selector
 * @author     Miguel Rivas Morantes <miguelrivasmorantes@gmail.com>
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @copyright  2026 Didactika.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {render as renderTemplate} from 'core/templates';

/**
 * Load the list of users from the local_delegateaccount web service.
 *
 * @param {String} selector The selector of the auto complete element.
 * @param {String} query The query string.
 * @param {Function} callback A callback function receiving an array of results.
 * @param {Function} failure A function to call in case of failure, receiving the error message.
 * @return {Promise} Resolves when the operation concludes.
 */
export async function transport(selector, query, callback, failure) {
    const select = document.querySelector(selector);
    let realUserId = 0;
    if (select && select.dataset.realuserid) {
        realUserId = parseInt(select.dataset.realuserid, 10);
    }

    const request = {
        methodname: 'local_delegateaccount_get_delegated_account_options',
        args: {
            query: query,
            realuserid: realUserId
        }
    };

    try {
        const results = await Ajax.call([request])[0];

        let labels = [];
        results.forEach(user => {
            labels.push(renderTemplate('core_user/form_user_selector_suggestion', {
                id: user.id,
                fullname: user.name,
                extrafields: []
            }));
        });
        labels = await Promise.all(labels);

        results.forEach((user, index) => {
            user.label = labels[index];
        });

        callback(results);
    } catch (e) {
        failure(e);
    }
}

/**
 * Process the results for auto complete elements.
 *
 * @param {String} selector The selector of the auto complete element.
 * @param {Array} results An array or results returned by {@see transport()}.
 * @return {Array} New array of the selector options.
 */
export function processResults(selector, results) {
    if (!Array.isArray(results)) {
        return results;
    } else {
        return results.map(result => ({value: result.id, label: result.label}));
    }
}
