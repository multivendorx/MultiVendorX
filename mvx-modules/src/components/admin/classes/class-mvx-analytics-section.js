import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import RingLoader from "react-spinners/RingLoader";
import { css } from "@emotion/react";

import { ReactSortable } from "react-sortablejs";

import {
  BrowserRouter as Router,
  Link,
  useLocation,
  withRouter,
  useParams,
  NavLink
} from "react-router-dom";

import DynamicForm from "../../../DynamicForm";

class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      error: null,
      isLoaded: false,
      items: [],
      checkedState: [],
      module_ids: [],
      open_model: false,
      open_model_dynamic: [],
      isLoading: true,
      loading: false,
      module_tabs: [],
      tabIndex: 0,
      query: null,
      firstname: true,
      lastname: '',
      email: '',
      abcarray: [],
      first_toggle: '',
      second_toggle: '',      
      current: {},
    };

    this.query = null;
    // when click on checkbox

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);

    this.Child = this.Child.bind(this);


  }

  componentDidMount() {


  }

  useQuery() {
    return new URLSearchParams(useLocation().search);
  }

  QueryParamsDemo() {
    let queryt = this.useQuery();
    if(!queryt.get("name")) {
      window.location.href = window.location.href+'&name=admin_overview';
    }
    return (
      <div className="container">
        <div className="general-tab-header-area">
        <h1>{queryt.get("name")}</h1>
        <p>links data are there</p>
        </div>
        <div className="general-tab-area">
          <ul className="mvx-general-tabs-list">
          {appLocalizer.mvx_all_backend_tab_list['marketplace-analytics'].map((data, index) => (
              <li className={queryt.get("name") == data.tabname ? 'activegeneraltabs' : ''}><i class="mvx-font ico-store-icon"></i><Link to={`?page=marketplace-analytics-settings&name=${data.tabname}`} >{data.tablabel}</Link></li>
          ))}
          </ul>

          <div className="tabcontentclass">
            <this.Child name={queryt.get("name")} />
          </div>

        </div>
      </div>
    );
  }

Child({ name }) {
  return (
    <div>
    {appLocalizer.mvx_all_backend_tab_list['marketplace-analytics'].map((data, index) => (
      <div>
        
      {
        name = !name ? 'admin_overview' : name,

        data.tabname == name ?
          
            <div>
             
            </div>
            
        : ''
      }
      </div>
    ))}
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