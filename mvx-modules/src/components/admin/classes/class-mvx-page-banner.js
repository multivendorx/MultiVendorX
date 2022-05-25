import React, { Component } from 'react';
import { render } from 'react-dom';

import {
  BrowserRouter as Router
} from "react-router-dom";


class App extends Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);
  }

  QueryParamsDemo() {
    return (
      <div className="mvx-sidebar">
        <a href="https://www.qries.com/" target="__blank">
          <img alt="Multivendor X" src={appLocalizer.multivendor_logo} />
        </a>
      </div>
    );
  }

  render() {
    return (
      <Router>
        <this.QueryParamsDemo />
      </Router>
    );
  }
}
export default App;