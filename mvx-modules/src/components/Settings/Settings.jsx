import { useLocation } from "react-router-dom";
import DynamicForm from "../DynamicForm/DynamicForm";
import Tabs from '../Tabs/Tabs';

// import context
import { useModules } from "../../contexts/ModuleContext";
import { SettingProvider, useSetting } from "../../contexts/SettingContext";

// import services function
import { getApiLink, sendApiResponse } from "../../services/apiService";
import { getSettingsJsonData } from "../../services/jsonService";

// import utility function
import { getAvialableSettings, getSettingById } from "../../utiles/settingUtil";


const Settings = () => {
    // get active modules
    const { modules } = useModules();

    // get all setting
    const settingsArray = getAvialableSettings( getSettingsJsonData(), modules );
    
    // get current browser location
    const location = new URLSearchParams( useLocation().hash );
    
    // Render the dinamic form.
    const getForm = (currentTab) => {
        // get the setting context
        const { settingName, setSetting } = useSetting();
        const setting = getSettingById( settingsArray, currentTab );
        
        if ( settingName !== currentTab ) {
            sendApiResponse(
                getApiLink( 'tabsettings' ),
                { tabname: currentTab }
                ).then( ( response ) => { 
                console.log(response);
                setSetting( currentTab, response );
            });
        }

        return (
            <>
                { settingName === currentTab ? <DynamicForm setting={ setting } /> : <>Loading</> }
            </>
        );
    }

    return (
        <>
            <SettingProvider>
                <Tabs
                    tabData={ settingsArray }
                    currentTab={ location.get( 'name' ) }
                    subMenu={ 'settings' }
                    getForm={ getForm }
                />
            </SettingProvider>
        </>
    );
}

export default Settings;
