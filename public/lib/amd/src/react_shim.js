export const init = (selector, options = {}) => {
    if (window.ReactApp && typeof window.ReactApp.init === 'function') {
        window.ReactApp.init(selector, options);
        return;
    }

    window.console.warn('window.ReactApp.init not found');
};
