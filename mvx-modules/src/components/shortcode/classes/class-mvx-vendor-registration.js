import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import RingLoader from "react-spinners/RingLoader";
import { css } from "@emotion/react";

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
      current: {}, 
    };
  }

  render() {
    return (
      <div>
          <DynamicForm
              key="vendor-registration-dynamic-from"
              className="vendor-registration-dynamic-from-class"
              title="Vendor Registration"
              defaultValues={this.state.current}
              model= {appLocalizer.vendor_registration_data}
              method="post"
              modelname=""
              url="mvx_module/v1/save_front_registration"
              />
      </div>
    );
  }
}
export default App;