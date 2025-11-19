import {React, ReactDOM} from '@core/react'

export default function App(props) {
    window.console.log(props);
    return (
        <div>
            Hello from my new file
        </div>
    )
}

export function init(selector, props = {}) {
    const container = document.querySelector(selector)
    if (!container) {
        window.console.warn(`React container not found for selector: ${selector}`)
        return
    }

    window.ReactApp = window.ReactApp || {}
    window.ReactApp.roots = window.ReactApp.roots || {}

    let root = window.ReactApp.roots[selector]
    if (!root) {
        root = ReactDOM.createRoot(container)
        window.ReactApp.roots[selector] = root
    }

    root.render(<App {...props} />)
}

if (!window.ReactApp) {
    window.ReactApp = {}
}

window.ReactApp.init = init
