/* global appLocalizer */

import { useLocation } from "react-router-dom";
import DynamicForm from "../AdminLibrary/DynamicForm/DynamicForm";
import Tabs from '../AdminLibrary/Tabs/Tabs';
import Support from "../Support/Support";
import BannerSection from '../Banner/banner';

// import context
import { SettingProvider, useSetting } from "../../contexts/SettingContext";

// import services function
import { getTemplateData } from "../../services/templateService";

// import utility function
import { getAvialableSettings, getSettingById } from "../../utiles/settingUtil";
import { useState, useEffect } from "react";

const Settings = () => {

    // get all setting
    const settingsArray = getAvialableSettings(getTemplateData(), []);

    // get current browser location
    const location = new URLSearchParams( useLocation().hash );
    
    // Render the dinamic form.
    const getForm = (currentTab) => {
        // get the setting context
        const { setting, settingName, setSetting } = useSetting();
        const settingModal = getSettingById( settingsArray, currentTab );
        if ( settingName != currentTab ) {
            setSetting( currentTab, appLocalizer.settings_databases_value[currentTab] || {} );
        }

        useEffect(() => {
            appLocalizer.settings_databases_value[settingName] = setting;
        }, [setting]);

        // Reander spacial component...
        if ( currentTab === 'faq' ) {
            return (
                <Support
                    content={settingModal}
                />
            );
        }

        return (
            <>
                { settingName === currentTab ? <DynamicForm setting={ settingModal } proSetting={ appLocalizer.pro_settings_list } /> : <>Loading</> }
            </>
        );
    }

    return (
        <>
            <SettingProvider>
                <Tabs
                    tabData={ settingsArray }
                    currentTab={ location.get( 'subtab' ) }
                    getForm={ getForm }
                    BannerSection = { BannerSection }
                    prepareUrl={(subTab) => `?page=multivendorx#&tab=settings&subtab=${subTab}` }
                />
            </SettingProvider>
        </>
    );
}

export default Settings;