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

import { DateRangePicker } from 'rsuite';

import DataTable from 'react-data-table-component';


import HeaderSection from './class-mvx-page-header';

import {
    LineChart,
    ResponsiveContainer,
    Legend, Tooltip,
    Line,
    XAxis,
    YAxis,
    CartesianGrid,
    BarChart,
    Bar
} from 'recharts';

import { CSVLink } from "react-csv";

class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      
    };

    this.query = null;
    // when click on checkbox

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);

    this.Child = this.Child.bind(this);

    this.handle_tools_triggers = this.handle_tools_triggers.bind(this);

    
  }

  handle_tools_triggers(e, type) {
    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/tools_funtion`,
      data: {
        type: type,
      }
    })
    .then( ( responce ) => {
      /*this.setState({
        list_of_store_review: responce.data,
      }); */ 
      
      if (responce.data.redirect_link) {
        window.location.href = responce.data.redirect_link;
      }

    } );
  }

  componentDidMount() {

  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {
    let queryt = this.useQuery();

    var tab_name_display = '';
    var tab_description_display = '';
    appLocalizer.mvx_all_backend_tab_list['status-tools'].map((data, index) => {
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
            

            <div className="mvx-upper-tab-header-area">
              <div className="mvx-tab-name-display">{tab_name_display}</div>
              <p>{tab_description_display}</p>
            </div>


            <div className="dashboard-tab-area">
              <ul className="mvx-dashboard-tabs-list">
                {appLocalizer.mvx_all_backend_tab_list['status-tools'].map((data, index) => (
                  <Link to={`?page=mvx#&submenu=status-tools&name=${data.modulename}`} ><li className={queryt.get("name") == data.modulename ? 'activedashboardtabs' : ''}>{data.icon ? <i class={`mvx-font ${data.icon}`}></i> : ''}{data.tablabel}</li></Link>                ))}
              </ul>
              <div className="dashboard-tabcontentclass">
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


      

      {
        name = !name ? appLocalizer.mvx_all_backend_tab_list['status-tools'][0]['modulename'] : name,

        name == appLocalizer.mvx_all_backend_tab_list['status-tools'][0]['modulename'] ?
          
            <div className="mvx-status-tools-content">

              <form>
                <header>
                  <h3>Rollback to Previous Version</h3>
                </header>

                <p>If you are facing issues after an update, you can reinstall a previous version with this tool</p>
                  
                <p className="description warning"><strong>Warning Previous versions may not be secure or stable. Proceed with caution and always create a backup<span class="warning"></span></strong></p>


              <table className="form-table">
                <tbody>
                  <tr>
                  <th scope="row"><label>Your Version</label></th>
                    <td>
                    
                    </td>
                  </tr>
                </tbody>
              </table>

              </form>

            </div>

            :

            name == appLocalizer.mvx_all_backend_tab_list['status-tools'][1]['modulename'] ?

            <div className="mvx-status-database-tools-content">
              
              <div className="mvx-vendor-transients">
                <div className="mvx-vendor-transients-header">
                  WCMp vendors transients
                </div>
                <div className="mvx-vendor-transients-description">
                  This tool will clear all WCMp vendors transients cache.
                </div>
                <div className="mvx-vendor-transients-button">
                  <button type="button" className="button-secondary" onClick={(e) => this.handle_tools_triggers(e, 'transients')}>Clear transients</button>
                </div>
              </div>

              <div className="mvx-vendor-transients">
                <div className="mvx-vendor-transients-header">
                  Reset visitors stats table
                </div>
                <div className="mvx-vendor-transients-description">
                  This tool will clear ALL the table data of WCMp visitors stats.
                </div>
                <div className="mvx-vendor-transients-button">
                  <button type="button" className="button-secondary" onClick={(e) => this.handle_tools_triggers(e, 'visitor')}>Reset Database</button>
                </div>
              </div>

              <div className="mvx-vendor-transients">
                <div className="mvx-vendor-transients-header">
                  Force WCMp order migrate
                </div>
                <div className="mvx-vendor-transients-description">
                  This will regenerate all vendors older orders to individual orders
                </div>
                <div className="mvx-vendor-transients-button">
                  <button type="button" className="button-secondary" onClick={(e) => this.handle_tools_triggers(e, 'migrate_order')}>Order Migrate</button>
                </div>
              </div>

              <div className="mvx-vendor-transients">
                <div className="mvx-vendor-transients-header">
                  Multivendor Migration
                </div>
                <div className="mvx-vendor-transients-description">
                  This will migrate older marketplace details
                </div>
                <div className="mvx-vendor-transients-button">
                  <button type="button" className="button-secondary" onClick={(e) => this.handle_tools_triggers(e, 'migrate')}>Multivendor migrate</button>
                </div>
              </div>

            </div>

            :

            name == appLocalizer.mvx_all_backend_tab_list['status-tools'][2]['modulename'] ?
            
            <div className="mvx-status-tools-content">

            </div>

            :

            name == appLocalizer.mvx_all_backend_tab_list['status-tools'][3]['modulename'] ?
              <div className="mvx-status-tools-content">

                

              </div>
            :
            ''
      }
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