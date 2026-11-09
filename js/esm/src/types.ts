/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The types the screens of the plugin share: what the web services return and what the server gives each component.
 *
 * @module     local_delegateaccount/types
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Looks a text up, putting the value in the place that the text marks. */
export type Text = (name: string, value?: string | number | Record<string, string | number>) => string;

/** Texts by name, as the server sends them. */
export type Strings = Record<string, string>;

/** A person, with what is needed to draw their picture or initials. */
export interface Person {
    id: number;
    fullname: string;
    email: string;
    hasimage: boolean;
    imageurl: string;
    initials: string;
    profileurl: string;
    url: string;
    linklabel: string;
}

/** How many rows of a list are in each status. */
export type Counts = Record<string, number>;

/** One row of the people of the management page. */
export interface PersonRow {
    user: Person;
    activecount: number;
    scheduledcount: number;
    url: string;
    cancreate: boolean;
    openlabel: string;
    addlabel: string;
}

/** One row of a list of delegations. */
export interface DelegationRow {
    id: number;
    realuser: Person;
    delegateduser: Person;
    status: string;
    statuslabel: string;
    statustone: string;
    timestart: number;
    start: string;
    timeend: number;
    end: string;
    lastaccess: string;
    hasaccess: boolean;
    overrides: number;
    overrideslabel: string;
    detailsurl: string;
    permissionsurl: string;
    activityurl: string;
    canupdate: boolean;
    canrevoke: boolean;
    canviewactivity: boolean;
    selectable: boolean;
    label: string;
}

/** One row of the activity of a delegation. */
export interface ActivityRow {
    id: number;
    time: string;
    eventname: string;
    eventurl: string;
    description: string;
    component: string;
    contextname: string;
    contexturl: string;
    relateduser: string;
    ip: string;
    origin: string;
}

/** One account a person may open. */
export interface AccountRow {
    user: Person;
    delegationid: number;
    start: string;
    end: string;
    hasend: boolean;
    loginurl: string;
    loginlabel: string;
}

/** One page of a list. */
export interface Page<Row> {
    total: number;
    counts?: Counts;
    haslog?: boolean;
    rows: Row[];
}

/** The filters that choose delegations. */
export interface Filters {
    realuserid: number;
    status: string;
    search: string;
}

/** The delegations an action reaches: the ones chosen, or every one that matches the filters. */
export interface Selection {
    delegationids: number[];
    all: boolean;
    filters: Filters;
}

/** One capability of the site. */
export interface Capability {
    name: string;
    label: string;
    component: string;
    componentlabel: string;
    risks: string[];
    canallow: boolean;
}

/** One override: a capability and its permission. */
export interface Override {
    capability: string;
    permission: number;
}

/** What the permission editor reads. */
export interface Permissions {
    capabilities: Capability[];
    overrides: Override[];
    site: Override[];
    canedit: boolean;
}

/** What every component gets: the texts of the page. */
export interface BaseProps {
    strings: Strings;
}

export interface PeopleListProps extends BaseProps {
    id: string;
    scope: string;
}

export interface DelegationListProps extends BaseProps {
    id: string;
    realuserid: number;
    status: string;
    canbulk: boolean;
    canupdate: boolean;
    canrevoke: boolean;
    canpermissions: boolean;
}

export interface ActivityListProps extends BaseProps {
    id: string;
    delegationid: number;
    mindate: string;
    maxdate: string;
}

export interface MyAccountsProps extends BaseProps {
    id: string;
    initial: Page<AccountRow>;
}

export interface PermissionEditorProps extends BaseProps {
    id: string;
    delegationid: number;
    site: boolean;
}

export interface CreateButtonProps extends BaseProps {
    realuserid: number;
}

export interface DelegationActionsProps extends BaseProps {
    delegationid: number;
    realuserid: number;
    canupdate: boolean;
    canrevoke: boolean;
    activityurl: string;
}

export interface UserMenuProps {
    html: string;
}
