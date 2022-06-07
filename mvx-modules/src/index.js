import React from 'react';
import ReactDOM from 'react-dom';
// admin section
import App from './components/admin/load/mvx-backend-pages';

document.addEventListener( 'DOMContentLoaded', function() {
    var element = document.getElementById( 'mvx-admin-dashboard' );
    if ( typeof element !== 'undefined' && element !== null ) {
        ReactDOM.render( <App />, document.getElementById( 'mvx-admin-dashboard' ) );
    }
} )