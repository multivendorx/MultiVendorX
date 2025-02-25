import React from 'react';
import { useLocation } from 'react-router-dom';

import Settings from './components/Settings/Settings.jsx';
import TableComponent from './components/TableComponent/TableComponent.jsx';
import Modules from './components/Modules/Modules.jsx';
import { ModuleProvider } from './contexts/ModuleContext.jsx';
// for react tour
import { TourProvider } from '@reactour/tour';
import { disableBodyScroll, enableBodyScroll } from 'body-scroll-lock';
import Tour from './components/TourSteps/Settings/TourSteps.jsx';

const disableBody = (target) => disableBodyScroll(target);
const enableBody = (target) => enableBodyScroll(target);

const Route = () => {
    const currentTab = new URLSearchParams(useLocation().hash);
    return (
        <>
            { currentTab.get('tab') === 'settings' && <Settings id="settings"/> }
            { currentTab.get('tab') === 'custom_table' && <TableComponent/> }
            { currentTab.get('tab') === 'modules' && <Modules/> }
        </>
    );
}

const App = () => {

    const currentTabParams = new URLSearchParams(useLocation().hash);
    
    document.querySelectorAll('#toplevel_page_plugin-elements>ul>li>a').forEach((menuItem) => {
        const menuItemUrl = new URL(menuItem.href);
        const menuItemHashParams = new URLSearchParams(menuItemUrl.hash.substring(1));

        menuItem.parentNode.classList.remove('current');
        if ( menuItemHashParams.get('tab') === currentTabParams.get('tab')) {
            menuItem.parentNode.classList.add('current');
        }
    });
   
    return (
        <>
            <ModuleProvider modules = {appLocalizer.active_modules}>
                {/*this is for tour provider */}
                <TourProvider
                    steps={[]}
                    afterOpen={disableBody}
                    beforeClose={enableBody}
                    disableDotsNavigation={true}
                    showNavigation={false}
                    showCloseButton= {false}
                >
                    <Tour />
                </TourProvider>
                <Route/>
            </ModuleProvider>
        </>
    )
}

export default App;
