import React from 'react';
import ReactDOM from 'react-dom';
// admin section
import App from './components/admin/load/mvx-module-list-section';
import Appsettings from './components/admin/load/mvx-general-settings-section';
import VendorPayemnt from './components/admin/load/mvx-vendor-paymnet-section';
import VendorAdvance from './components/admin/load/mvx-vendor-advance-section';
import VendorAnalytics from './components/admin/load/mvx-vendor-analytics-section';
import VendorManager from './components/admin/load/mvx-vendor-manager-section';
import VendorManage from './components/admin/load/mvx-vendor-manage-section';
import VendorCommission from './components/admin/load/mvx-vendor-commission-section';

// shortcode section
import Vendorregistration from './components/shortcode/load/mvx-shortcode-vendor-registration-section';
import Vendorlist from './components/shortcode/load/mvx-shortcode-vendor-list-section';

document.addEventListener( 'DOMContentLoaded', function() {
    var element = document.getElementById( 'mvx-modules-admin-dashboard-display' );
    if ( typeof element !== 'undefined' && element !== null ) {
        ReactDOM.render( <App />, document.getElementById( 'mvx-modules-admin-dashboard-display' ) );
    }

    var element1 = document.getElementById( 'mvx-modules-admin-dashboard-general-settings' );
    if ( typeof element1 !== 'undefined' && element1 !== null ) {
        ReactDOM.render( <Appsettings />, document.getElementById( 'mvx-modules-admin-dashboard-general-settings' ) );
    }

    var element2 = document.getElementById( 'mvx-vendor-registration-shortcode' );
    if ( typeof element2 !== 'undefined' && element2 !== null ) {
        ReactDOM.render( <Vendorregistration />, document.getElementById( 'mvx-vendor-registration-shortcode' ) );
    }

    var element3 = document.getElementById( 'mvx-vendor-list-shortcode' );
    if ( typeof element3 !== 'undefined' && element3 !== null ) {
        ReactDOM.render( <Vendorlist />, document.getElementById( 'mvx-vendor-list-shortcode' ) );
    }

    var element4 = document.getElementById( 'mvx-modules-admin-payment-display' );
    if ( typeof element4 !== 'undefined' && element4 !== null ) {
        ReactDOM.render( <VendorPayemnt />, document.getElementById( 'mvx-modules-admin-payment-display' ) );
    }

    var element5 = document.getElementById( 'mvx-modules-admin-advance-display' );
    if ( typeof element5 !== 'undefined' && element5 !== null ) {
        ReactDOM.render( <VendorAdvance />, document.getElementById( 'mvx-modules-admin-advance-display' ) );
    }
    
    var element6 = document.getElementById( 'mvx-modules-admin-analytics-display' );
    if ( typeof element6 !== 'undefined' && element6 !== null ) {
        ReactDOM.render( <VendorAnalytics />, document.getElementById( 'mvx-modules-admin-analytics-display' ) );
    }

    var element7 = document.getElementById( 'mvx-modules-admin-manager-display' );
    if ( typeof element7 !== 'undefined' && element7 !== null ) {
        ReactDOM.render( <VendorManager />, document.getElementById( 'mvx-modules-admin-manager-display' ) );
    }

    var element8 = document.getElementById( 'mvx-vendor-section' );
    if ( typeof element8 !== 'undefined' && element8 !== null ) {
        ReactDOM.render( <VendorManage />, document.getElementById( 'mvx-vendor-section' ) );
    }

    var element9 = document.getElementById( 'mvx-modules-admin-commission-display' );
    if ( typeof element9 !== 'undefined' && element9 !== null ) {
        ReactDOM.render( <VendorCommission />, document.getElementById( 'mvx-modules-admin-commission-display' ) );
    }

} )