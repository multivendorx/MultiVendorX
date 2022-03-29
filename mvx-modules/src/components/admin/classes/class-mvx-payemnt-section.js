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

import HeaderSection from './class-mvx-page-header';


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
    if(!queryt.get("name")) {
      //window.location.href = window.location.href+'&name=paypal_masspay';
    }
    var tab_name_display = '';
    var tab_description_display = '';
    appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].map((data, index) => {
        if(queryt.get("name") == data.modulename) {
          tab_name_display = data.tablabel;
          tab_description_display = data.description;
        }
      }
    )

    return (
      <div>

        <HeaderSection />

      <div className="container">
      <div className="mvx-child-container">
        <div className="mvx-sub-container">

          <div className="general-tab-header-area">
          <div className="mvx-tab-name-display">{tab_name_display}</div>
          <p>{tab_description_display}</p>
          </div>
          <div className="general-tab-area">

            <ul className="mvx-general-tabs-list">
            {appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].map((data, index) => (
                <Link to={`?page=mvx#&submenu=payment&name=${data.modulename}`} ><li className={queryt.get("name") == data.modulename ? 'activegeneraltabs' : ''}>{data.icon ? <i class={`mvx-font ${data.icon}`}></i> : ''}{data.tablabel}</li></Link>
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
    {appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].map((data, index) => (
      <div>

      {
        name = !name ? 'paypal_masspay' : name,

        data.modulename == name ?
          
            <div>
              <DynamicForm
              key={`dynamic-form-${data.modulename}`}
              className={data.classname}
              title={data.tablabel}
              defaultValues={this.state.current}
              model= {appLocalizer.settings_fields[data.modulename]}
              method="post"
              modulename={data.modulename}
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