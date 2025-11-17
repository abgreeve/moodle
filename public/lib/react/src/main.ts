import React from "react";
import * as ReactDOM from "react-dom";
import * as ReactDOMClient from "react-dom/client";

import Button from "./components/Button";
import Input from "./components/Input";

import type { ReactComponentsRegistry } from "./types";

declare global {
  interface Window {
    React?: typeof React;
    ReactDOM?: typeof ReactDOM;
    ReactDOMClient?: typeof ReactDOMClient;
    ReactComponents?: ReactComponentsRegistry;
  }
}

window.React = React;
window.ReactDOM = ReactDOM;
window.ReactDOMClient = ReactDOMClient;

window.ReactComponents = window.ReactComponents || {};

window.ReactComponents["@core/button"] = Button;
window.ReactComponents["@core/input"] = Input;
