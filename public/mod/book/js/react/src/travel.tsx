import React from "react";
import {Button} from "@moodlehq/design-system";
import { mountReactApp } from "@moodle/lms/core/mount";

import * as mustacheTest from '@moodle/lms/mod_book/mustache_test';

function requireAmd(mod) {
    return new Promise((resolve, reject) => {
        // eslint-disable-next-line @typescript-eslint/no-require-imports
        require([mod], resolve, reject);
    });
}

async function getString(key, component, params = {}) {
    const str = await requireAmd("core/str");
    return str.get_string(key, component, params);
}

const loadModalForm = async (event) => {
    event?.preventDefault();
    const ModalForm = await requireAmd("core_form/modalform");
    const contextid = window.M.cfg.contextid ?? 1;
    const form = new ModalForm({
        formClass: "mod_book\\output\\simple2complex_form",
        args: { contextid: contextid },
        modalConfig: { title: "Simple to complex form" },
        returnFocus: event?.currentTarget ?? null,
    });
    form.show();
};

function App(props) {
    const [tick, setTick] = React.useState(0);

    React.useEffect(() => {
        setTimeout(() => setTick(t => t + 1), 500);
    }, []);

    const [label, setLabel] = React.useState("");

    React.useEffect(() => {
        async function load() {
            const text = await getString("toc", "mod_book");
            setLabel(text);
        }
        load().catch((error) =>
            window.console.error("Failed to load string", error)
        );
    }, []);

    return (
        <div>
            {label || "Hello from my new file"}
            <br />
            <Button label="Modal form?" onClick={loadModalForm} />
        </div>
    );
}

export function init(selector, props = {}) {
    const container = document.querySelector(selector);
    if (!container) {
        window.console.warn(`React container not found for selector: ${selector}`);
        return;
    }

    mountReactApp(container, App, props, {id: "App"});
}

init("#book-react-node", {});
