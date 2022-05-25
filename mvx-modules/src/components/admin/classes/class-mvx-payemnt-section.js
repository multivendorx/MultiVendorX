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

import TabSection from './class-mvx-page-tab';

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
    let use_query = this.useQuery();
    return (
        <TabSection
          model={appLocalizer.mvx_all_backend_tab_list['marketplace-payments']}
          query_name={use_query.get("name")}
          funtion_name={this}
        />
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
    <div className="mvx-payments-tab-wrapper">
    {appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].length > 0 ? appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].map((data, index) => (
      <div className="mvx-payments-tab-child-wrapper">

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
      <Router>
        <this.QueryParamsDemo />
      </Router>
    );
  }
}
export default App;