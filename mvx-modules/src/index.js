import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter } from "react-router-dom"
import App from "./App.js";

document.addEventListener( 'DOMContentLoaded', function () {
	const element = document.getElementById('mvx-admin-dashboard');
	if ( typeof element !== 'undefined' && element !== null ) {
		ReactDOM.render(
			<BrowserRouter><App /></BrowserRouter>,
			document.getElementById( 'mvx-admin-dashboard' )
		);
	}
});
