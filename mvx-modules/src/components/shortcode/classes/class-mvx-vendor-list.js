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
      vendors_list: mvx_vendor_list_script_data.total_vendors,
    };
    this.handleloadmore = this.handleloadmore.bind(this);

    this.handleloadless = this.handleloadless.bind(this);

    this.handlevendordetails = this.handlevendordetails.bind(this);
    
  }

  handlevendordetails(vendor_id, uniquename) {
    //return e;

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/vendor_details`,
      data: {
        vendor_id: vendor_id,
        uniquename: uniquename
      }
    })
    .then(response => {
      console.log(response);
    })

  }

  handleloadmore(e) {
    var current_length  = this.state.vendors_list.length + parseInt(mvx_vendor_list_script_data.loadmore_show_option);
    this.setState({vendors_list: mvx_vendor_list_script_data.total_vendors.slice(0, current_length)})
  }

  handleloadless(e) {
    var current_length  = this.state.vendors_list.length - parseInt(mvx_vendor_list_script_data.loadmore_show_option);
    this.setState({vendors_list: mvx_vendor_list_script_data.total_vendors.slice(0, current_length)})
  }

  componentDidMount() {
    this.setState({vendors_list: mvx_vendor_list_script_data.total_vendors.slice(0, parseInt(mvx_vendor_list_script_data.initial_vendor_show_num))})
  }

  render() {
    var vendor = '';
    return (
      <div id="mvx-store-conatiner">
         <div className="mvx-store-locator-wrap">
          <div id="mvx-vendor-list-map" className="mvx-store-map-wrapper"></div>
          <form>
          </form>
         </div>

         <div className={`mvx-store-list-wrap list-${mvx_vendor_list_script_data.column_numbers}`}>
           {this.state.vendors_list.map((vendor_id, index) => (
            
            <div className="mvx-store-list mvx-store-list-vendor">
              
              <div className="mvx-vendorblocks">
              
              <div className="mvx-vendor-details">
                <div className="vendor-heading">
                  <div className="mvx-store-picture">
                  Image
                  
                  </div>
                  {vendor = this.handlevendordetails(vendor_id, 'image')}

                  {vendor_id}
                </div>
              </div>

              </div>

            </div>
            ))}
         </div>
        { this.state.vendors_list.length == mvx_vendor_list_script_data.total_vendors.length ? '' : <button className="btn-load-more" onClick={this.handleloadmore}>Load More</button> } 
        {this.state.vendors_list.length > mvx_vendor_list_script_data.initial_vendor_show_num ? <button className="btn-load-more" onClick={this.handleloadless}>Load Less</button> : ''}
      </div>
    );
  }
}
export default App;