import React, { Component } from 'react';
import { render } from 'react-dom';

import {
  BrowserRouter as Router
} from "react-router-dom";


class App extends Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  QueryParamsDemo() {
    return (
      <div className="mvx-module-section-nav">
        <div className="mvx-module-nav-left-section">
          <div className="mvx-module-section-nav-child-data">
            <img src={appLocalizer.mvx_logo} alt="WC Marketplace" className="mvx-section-img-fluid"/>
          </div>
          <div className="mvx-module-section-nav-child-data">
            {appLocalizer.marketplace_text}
          </div>
        </div>
        <div className="mvx-module-nav-right-section">
          <div className="mvx-header-search-section"> 
            <label><i className='mvx-font icon-search'></i></label>
            <input type="text" placeholder="Search Modules" name="search"/>
          </div>
          <a href={appLocalizer.knowledgebase} title={appLocalizer.knowledgebase_title} target="_blank" className="mvx-module-section-nav-child-data"><i class='mvx-font icon-knowledge-topbar'></i></a>
        </div>
      </div>
    );
  }

  render() {
    return (
      <div>
          <Router>
            <this.QueryParamsDemo />
          </Router>
      </div>
    );
  }
}
export default App;