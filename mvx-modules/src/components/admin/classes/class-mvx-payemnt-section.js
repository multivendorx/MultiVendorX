import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import RingLoader from "react-spinners/RingLoader";
import { css } from "@emotion/react";
import PuffLoader from "react-spinners/PuffLoader";

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


const override = css`
  display: block;
  margin: 0 auto;
  border-color: red;
`;

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
      list_of_module_data: [],
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

          {appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].length > 0 ?

          <div className="mvx-sub-child-container">
            <div className="mv-offactive-white-box pa-15 mb-90 text-center">
              <div className="mvx-tab-name-display">{tab_name_display}</div>
              <p>{tab_description_display}</p>
            </div>

            <div className="general-tab-area">
              <ul className="mvx-general-tabs-list">
              {appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].length > 0 ? appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].map((data, index) => (
                  <li className={queryt.get("name") == data.modulename ? 'activegeneraltabs' : ''}><Link to={`?page=mvx#&submenu=payment&name=${data.modulename}`} >{data.icon ? <i class={`mvx-font ${data.icon}`}></i> : ''}{data.tablabel}</Link></li>
              )) : ''}
              </ul>
              <div className="tabcontentclass">
                <this.Child name={queryt.get("name")} />
              </div>
            </div>
          </div>
          : 'No Payment method found'}

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

  axios({
    url: `${appLocalizer.apiUrl}/mvx_module/v1/fetch_all_modules_data`
  })
  .then(response => {

    this.setState({
      list_of_module_data: response.data
    });

  });

  return (
    <div>
    {appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].length > 0 ? appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].map((data, index) => (
      <div>

      {
        name = !name ? 'paypal_masspay' : name,

        data.modulename == name ?

            <div>
            {Object.keys(this.state.list_of_module_data).length > 0 ?
              <DynamicForm
              key={`dynamic-form-${data.modulename}`}
              className={data.classname}
              title={data.tablabel}
              defaultValues={this.state.current}
              model= {this.state.list_of_module_data[data.modulename]}
              method="post"
              modulename={data.modulename}
              url={data.apiurl}
              submitbutton="false"
              />
              : <PuffLoader css={override} color={"#cd0000"} size={200} loading={true} /> }
            </div>

        : ''
      }
      </div>
    )) : ''}
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