import {React, ReactDOM} from 'lib/react/build/react.js'

export function init(selector, props = {}) {
    const container = document.querySelector(selector)
    const root = ReactDOM.createRoot(container)
    root.render(<App {...props} />)
}

window.ReactApp.init = init
