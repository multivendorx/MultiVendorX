import { render } from '@wordpress/element';
import { BrowserRouter} from 'react-router-dom';
import App from './app.js';

/**
 * Import the stylesheet for the plugin.
 */
import './style/common.scss';

const MainApp=()=>{
    return <BrowserRouter>
       <App/>
    </BrowserRouter>
}

// Render the App component into the DOM

render(<MainApp/>,document.getElementById('mvx-admin-dashboard'));
