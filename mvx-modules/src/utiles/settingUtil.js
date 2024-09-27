/**
 * Get settring objeccts as array of object sorted order based on priority.
 * @param {Array} settings 
 * @returns {Array}
 */
const getSettingsByPriority = ( settings ) => {
    if ( Array.isArray( settings ) ) {
        settings.sort((a, b) => a.priority - b.priority);
    }
    return settings;
}

/**
 * Get all setting that's id is present in provided ids array.
 * @param {*} settings 
 * @param {*} ids 
 * @returns filter setting.
 */
const filterSettingByIds = ( settings, ids ) => {
    if ( Array.isArray( settings ) && Array.isArray( ids ) ) {
        return settings.filter( ( setting ) => ids.includes( setting.id ) );
    }
    return settings;
}

/**
 * Get default settings from all settings.
 * @param {*} settings
 * @returns {*}
 */
const getDefaultSettings = ( settings ) => {
    if ( Array.isArray( settings ) ) {
        return settings.filter( ( setting ) => ! setting.pro_dependent && ! setting.module_dependent );
    }
    return settings;
}

/**
 * Get avialable settings include free settings and settings of provided ids.
 * @param {*} settings 
 * @param {*} ids 
 * @returns 
 */
const getAvialableSettings = ( settings, ids ) => {
    return getSettingsByPriority( [ ...getDefaultSettings( settings ) , ...filterSettingByIds( settings, ids ) ] );
}

/**
 * Get setting object from provided settings array matched the settingId.
 * If provided Id does not match it return empty array.
 * @param {*} settings 
 * @param {*} settingId 
 * @returns 
 */
const getSettingById = ( settings, settingId ) => {
    if ( Array.isArray( settings ) ) {
        return settings.find( ( { id } ) => id === settingId ) || [];
    }
    return [];
}

/**
 * Check if a setting is active or not.
 * @param {*} setting
 * @param {boolean} proActive
 * @param {Array} ids 
 * @return {boolean}
 */
const isActiveSetting = ( setting, proActive, ids ) => {
    // Default setting return true.
    if ( ! setting.module_dependent ) {
        return true;
    }
    // Module setting
    if ( ids.includes( setting.id ) ) {
        // Free module setting return true.
        if ( ! setting.pro_dependent ) {
            return true;
        }
        // Pro module setting and pro is active return true.
        if ( proActive ) {
            return true;
        }
    }
    return false;
}

export { getAvialableSettings, getSettingById, isActiveSetting };