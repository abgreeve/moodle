import React from "react";
import {createRoot} from "react-dom/client";
import {Button} from "@moodlehq/design-system";

import * as mustacheTest from '@moodle/mod_book/mustache_test';

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
  window.console.log("Do I hit this?");
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
  window.console.log(props);
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

// Wrap with profiler.

export function init(selector, props = {}) {
  const container = document.querySelector(selector);
  if (!container) {
    window.console.warn(`React container not found for selector: ${selector}`);
    return;
  }

  const root = createRoot(container);
  root.render(<App {...props} />);
}

init("#book-react-node", {});
