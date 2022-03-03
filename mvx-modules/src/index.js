import React from 'react';
import ReactDOM from 'react-dom';
// admin section
import App from './components/admin/load/mvx-module-list-section';

// shortcode section
import Vendorregistration from './components/shortcode/load/mvx-shortcode-vendor-registration-section';
import Vendorlist from './components/shortcode/load/mvx-shortcode-vendor-list-section';

document.addEventListener( 'DOMContentLoaded', function() {
    var element = document.getElementById( 'mvx-modules-admin-dashboard-display' );
    if ( typeof element !== 'undefined' && element !== null ) {
        ReactDOM.render( <App />, document.getElementById( 'mvx-modules-admin-dashboard-display' ) );
    }
} )