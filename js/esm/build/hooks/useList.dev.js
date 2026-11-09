var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * A list people work with, as the design system describes it: its state kept in the address (search, chip, sort, page and
 * rows per page), one page loaded at a time from a web service, the skeleton shown only when a load is slow, and a selection
 * that can grow to every row that matches.
 *
 * @module     local_delegateaccount/hooks/useList
 * @copyright  2026 Didactika.org
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useCallback, useEffect, useRef, useState } from "react";
import Pending from "@moodle/lms/core/pending";
const SKELETON_DELAY = 300;
const readState = /* @__PURE__ */ __name((defaults) => {
  const params = new URL(window.location.href).searchParams;
  const state = { ...defaults };
  Object.keys(defaults).forEach((key) => {
    const value = params.get(key);
    if (value !== null) {
      state[key] = typeof defaults[key] === "number" ? Number(value) || 0 : value;
    }
  });
  return state;
}, "readState");
const writeState = /* @__PURE__ */ __name((state, defaults) => {
  const url = new URL(window.location.href);
  Object.keys(defaults).forEach((key) => {
    if (state[key] === defaults[key] || state[key] === "") {
      url.searchParams.delete(key);
    } else {
      url.searchParams.set(key, String(state[key]));
    }
  });
  window.history.replaceState(window.history.state, "", url);
}, "writeState");
const FILTERS = ["search", "status", "datefrom", "dateto", "component", "action"];
function useList(defaults, load, initial) {
  const [state, setState] = useState(() => readState(defaults));
  const [page, setPage] = useState(initial ?? null);
  const [error, setError] = useState(null);
  const [busy, setBusy] = useState(false);
  const [skeleton, setSkeleton] = useState(false);
  const [selected, setSelected] = useState(/* @__PURE__ */ new Set());
  const [all, setAll] = useState(false);
  const [reload, setReload] = useState(0);
  const first = useRef(Boolean(initial));
  const loader = useRef(load);
  loader.current = load;
  useEffect(() => {
    writeState(state, defaults);
    if (first.current) {
      first.current = false;
      if (Object.keys(defaults).every((key) => state[key] === defaults[key])) {
        return void 0;
      }
    }
    let current = true;
    const pending = new Pending("local_delegateaccount/list:load");
    const timer = window.setTimeout(() => setSkeleton(true), SKELETON_DELAY);
    setBusy(true);
    loader.current(state).then((response) => {
      window.clearTimeout(timer);
      if (!current) {
        return response;
      }
      const lastpage = Math.max(0, Math.ceil(response.total / Number(state.perpage)) - 1);
      if (Number(state.page) > lastpage) {
        setState((before) => ({ ...before, page: lastpage }));
        return response;
      }
      setPage(response);
      setError(null);
      return response;
    }).catch((failure) => {
      if (current) {
        setError(failure);
      }
    }).finally(() => {
      window.clearTimeout(timer);
      if (current) {
        setBusy(false);
        setSkeleton(false);
      }
      pending.resolve();
    });
    return () => {
      current = false;
      window.clearTimeout(timer);
    };
  }, [state, reload]);
  const update = useCallback((changes) => {
    setState((before) => {
      const filtered = Object.keys(changes).some((key) => FILTERS.includes(key) && changes[key] !== before[key]);
      if (filtered || "page" in changes && changes.page !== before.page) {
        setSelected(/* @__PURE__ */ new Set());
        setAll(false);
      }
      return { ...before, ...filtered && !("page" in changes) ? { page: 0 } : {}, ...changes };
    });
  }, []);
  const toggle = useCallback((id, checked) => {
    setSelected((before) => {
      const next = new Set(before);
      if (checked) {
        next.add(id);
      } else {
        next.delete(id);
      }
      return next;
    });
    if (!checked) {
      setAll(false);
    }
  }, []);
  const togglePage = useCallback((ids, checked) => {
    setSelected((before) => {
      const next = new Set(before);
      ids.forEach((id) => checked ? next.add(id) : next.delete(id));
      return next;
    });
    setAll(false);
  }, []);
  const clear = useCallback(() => {
    setSelected(/* @__PURE__ */ new Set());
    setAll(false);
  }, []);
  return {
    state,
    update,
    page,
    error,
    busy,
    skeleton,
    selected,
    all,
    setAll,
    toggle,
    togglePage,
    clear,
    refresh: /* @__PURE__ */ __name(() => setReload((count) => count + 1), "refresh")
  };
}
__name(useList, "useList");
function useDebounced(value, delay) {
  const [late, setLate] = useState(value);
  useEffect(() => {
    const timer = window.setTimeout(() => setLate(value), delay);
    return () => window.clearTimeout(timer);
  }, [value, delay]);
  return late;
}
__name(useDebounced, "useDebounced");
export {
  useDebounced,
  useList
};
//# sourceMappingURL=useList.dev.js.map
