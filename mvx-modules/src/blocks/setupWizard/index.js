import { render } from '@wordpress/element';
import { BrowserRouter } from 'react-router-dom';
import SetupWizard from './SetupWizard';
import WooCommerceInstaller from './WooCommerceInstaller';

// Render the App component into the DOM
render(<BrowserRouter>
{appLocalizer.woocommerce_installed ? <SetupWizard/> : <WooCommerceInstaller/>}
</BrowserRouter>, document.getElementById('mvx_setup_wizard'));
