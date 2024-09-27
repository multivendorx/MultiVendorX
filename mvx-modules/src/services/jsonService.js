/**
 * Core static JSON service module.
 */

/**
 * Get Setting JSON data as object.
 * @return {Array} Array of Object.
 */
const getSettingsJsonData = () => {
    const settings = [];
    const context   = require.context( `../assets/json/settings`, false, /\.json$/ );
    context.keys().forEach(( key ) => {
        settings.push( context(key) );
    });
    return settings;
}

export { getSettingsJsonData };