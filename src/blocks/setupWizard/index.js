import { render } from '@wordpress/element';
import { BrowserRouter } from 'react-router-dom';
import SetupWizard from './SetupWizard';

// Render the App component into the DOM
render(<BrowserRouter>
<SetupWizard/>
</BrowserRouter>, document.getElementById('multivendorx_setup_wizard'));
