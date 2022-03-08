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


import HeaderSection from './class-mvx-page-header';


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

  componentDidMount() {}

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {
    let queryt = this.useQuery();
    return (
      <div>

        <HeaderSection />

        <div className="container">
            <div className="mvx-child-container">
              <div className="mvx-sub-container">
                <div className="general-tab-header-area">
                  <div className="mvx-tab-name-display">{queryt.get("name")}</div>
                  <p>links data are there</p>
                </div>
                <div className="general-tab-area">
                  <ul className="mvx-general-tabs-list">
                  {appLocalizer.mvx_all_backend_tab_list['marketplace-advance-settings'].map((data, index) => (
                      <li className={queryt.get("name") == data.tabname ? 'activegeneraltabs' : ''}><i class="mvx-font ico-store-icon"></i><Link to={`?page=mvx#&submenu=advance&name=${data.tabname}`} >{data.tablabel}</Link></li>
                  ))}
                  </ul>
                  <div className="tabcontentclass">
                    <this.Child name={queryt.get("name")} />
                  </div>
                </div>
              </div>

              <div className="mvx-adv-image-display">
                <a href="https://www.qries.com/" target="__blank">
                  <img alt="Multivendor X" src={appLocalizer.multivendor_logo}/>
                </a>
              </div>
            </div>
      </div>

      </div>
    );
  }

Child({ name }) {
  return (
    <div>
    {appLocalizer.mvx_all_backend_tab_list['marketplace-advance-settings'].map((data, index) => (
      <div>
        
      {
        name = !name ? 'buddypress' : name,

        data.tabname == name ?
          
            <div>
              <DynamicForm
              key={`dynamic-form-${data.tabname}`}
              className={data.classname}
              title={data.tablabel}
              defaultValues={this.state.current}
              model= {appLocalizer.settings_fields[data.modelname]}
              method="post"
              modelname={data.modelname}
              url={data.apiurl}
              submitbutton="false"
              />
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