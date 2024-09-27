import { useLocation } from 'react-router-dom';

// import components
import Dashboard from '../Dashboard/Dashboard';
import WorkBoard from '../WorkBoard/WorkBoard';
import Modules from '../Modules/Modules';
import Vendor from '../Vendor/Vendor';
import Payment from '../Payment/Payment';
import Commission from '../Commission/Commission';
import Settings from '../Settings/Settings';
import Analytics from '../Analytics/Analytics';
import StatusTools from '../StatusTools/StatusTools';

/**
 * App toplable router component.
 * Route sub tab of MultivendorX tab.
 * @returns 
 */
const AppRouter = () => {

    // MultivendorX sub tab active handle
    const currentUrl = window.location.href;
    document.querySelectorAll( '#toplevel_page_mvx>ul>li>a' ).forEach( ( element ) => {
        element.parentNode.classList.remove( 'current' );
        if ( element.href === currentUrl ) {
            element.parentNode.classList.add( 'current' );
        }
    });

    const location = new URLSearchParams(useLocation().hash);

    return (
        <>
            {/*  do routing here */}
            { location.get('submenu') === 'dashboard'    && <Dashboard /> }
            { location.get('submenu') === 'work-board'   && <WorkBoard /> }
            { location.get('submenu') === 'modules'      && <Modules /> }
            { location.get('submenu') === 'vendor'       && <Vendor /> }
            { location.get('submenu') === 'payment'      && <Payment /> }
            { location.get('submenu') === 'commission'   && <Commission /> }
            { location.get('submenu') === 'settings'     && <Settings/> }
            { location.get('submenu') === 'analytics'    && <Analytics /> }
            { location.get('submenu') === 'status-tools' && <StatusTools /> }
        </>
    );
}

export default AppRouter;