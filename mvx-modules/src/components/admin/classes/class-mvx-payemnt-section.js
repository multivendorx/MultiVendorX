import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
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
      current: {},
      list_of_module_data: [],
      list_of_all_tabs: []
    };
    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);
    this.useQuery = this.useQuery.bind(this);
    this.Child = this.Child.bind(this);
  }

  componentDidMount() {
    // tab list
    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/list_of_all_tabs`
    })
    .then(response => {
      this.setState({
        list_of_all_tabs: response.data,
      });
    })
  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {
    let use_query = this.useQuery();
    return (
      Object.keys(this.state.list_of_all_tabs).length > 0 ?
        <TabSection
          model={this.state.list_of_all_tabs['marketplace-payments']}
          query_name={use_query.get("name")}
          funtion_name={this}
        />
      : <PuffLoader css={override} color={"#cd0000"} size={200} loading={true} /> 
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
      appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].length > 0 ? appLocalizer.mvx_all_backend_tab_list['marketplace-payments'].map((data, index) => (
        name = !name ? 'paypal_masspay' : name,
        data.modulename == name ?
            Object.keys(this.state.list_of_module_data).length > 0 ?
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
              : 
            <PuffLoader css={override} color={"#cd0000"} size={200} loading={true} />
        : ''
    )) : ''
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