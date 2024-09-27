import { useRef, useState } from 'react';
import { useLocation } from 'react-router-dom';
import PageLoader from '../classes/class-mvx-page-loader.js';
import TabSection from './Tabs.jsx';
import DynamicForm from '../../../DynamicForm/DynamicForm.jsx';

const Settings = () => {
    const globalSettings = useRef(appLocalizer.databse_settings);

    const getCurrentTabContent = (tabName, apiurl) => {
        // if (tabName == ) {
        //     return (
                
        //     );
        // }
        return (
            <DynamicForm
                model={appLocalizer.settings_fields[tabName]}
                modelName={tabName}
                globalSettings={globalSettings}
                apiurl={apiurl}
                method="post"
            />
        );
    }

    const location = new URLSearchParams(useLocation().hash);
    return (
        <>
            {
                Object.keys(appLocalizer.mvx_all_backend_tab_list).length > 0 ?
                    <TabSection
                        model={appLocalizer.mvx_all_backend_tab_list['marketplace-general-settings']}
                        currentTab={location.get('name')}
                        getCurrentTab={getCurrentTabContent}
                    />
                    :
                    <PageLoader/>
            }
        </>
    );
}

export default Settings;