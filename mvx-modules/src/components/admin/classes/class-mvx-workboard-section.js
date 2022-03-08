import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import PuffLoader from "react-spinners/PuffLoader";
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
import DataTable from 'react-data-table-component';

import HeaderSection from './class-mvx-page-header';


const override = css`
  display: block;
  margin: 0 auto;
  border-color: green;
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
      display_announcement: [],
      knowledge_data_fileds: [],
      edit_announcement_fileds: [],
      edit_knowledgebase_fileds: [],
      display_list_knowladgebase: [],
      columns_announcement: [
        {
            name: <div className="mvx-datatable-header-text">Title</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.title}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Date</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.date}}></div>,
            sortable: true,
        },
      ],
      columns_knowladgebase: [
        {
            name: <div className="mvx-datatable-header-text">Title</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.title}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Date</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.date}}></div>,
            sortable: true,
        },
      ],
    };

    this.query = null;
    // when click on checkbox

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);

    this.Child = this.Child.bind(this);


  }

  componentDidMount() {
    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`
    )
    .then(response => {
      this.setState({
        display_announcement: response.data,
      });
    })
  

    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`
    )
    .then(response => {
      this.setState({
        display_list_knowladgebase: response.data,
      });
    })
  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {
    let queryt = this.useQuery();
    if(!queryt.get("name")) {
      //window.location.href = window.location.href+'&name=activity_reminder';
    }
    var tab_name_display = '';
    var tab_description_display = '';
    appLocalizer.mvx_all_backend_tab_list['marketplace-workboard'].map((data, index) => {
        if(queryt.get("name") == data.tabname) {
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
                    {appLocalizer.mvx_all_backend_tab_list['marketplace-workboard'].map((data, index) => (
                        <li className={queryt.get("name") == data.tabname ? 'activegeneraltabs' : ''}><i class="mvx-font ico-store-icon"></i><Link to={`?page=mvx#&submenu=work-board&name=${data.tabname}`} >{data.tablabel}</Link></li>
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
  var get_current_name = this.useQuery();

  if (!get_current_name.get("AnnouncementID")) {
    this.state.edit_announcement_fileds = [];
  }

  if (!get_current_name.get("knowladgebaseID")) {
    this.state.edit_knowledgebase_fileds = [];
  }

  if (get_current_name.get("AnnouncementID")) {
    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/update_announcement_display`, { params: { announcement_id: get_current_name.get("AnnouncementID") } 
    })
    .then(response => {
      if (response.data && this.state.edit_announcement_fileds.length == 0) {
          this.setState({
            edit_announcement_fileds: response.data,
          });
      }
    })
  }

  if (get_current_name.get("knowladgebaseID")) {
    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/update_knowladgebase_display`, { params: { knowladgebase_id: get_current_name.get("knowladgebaseID") } 
    })
    .then(response => {
      if (response.data && this.state.edit_knowledgebase_fileds.length == 0) {
          this.setState({
            edit_knowledgebase_fileds: response.data,
          });
      }
    })
  }

  return (
    <div>
    {
      name == 'activity_reminder' ?

      'activity_reminder'

      :

      name == 'announcement' ?

      <div className="mvx-backend-datatable-wrapper">
        <div className="button-secondary"><Link to={`?page=mvx#&submenu=work-board&name=announcement&create=announcement`}>Add Announcement</Link></div>

        {get_current_name && get_current_name.get("create") == 'announcement' ?

          <DynamicForm
            key={`dynamic-form-announcement-add-new`}
            className="mvx-announcement-add-new"
            title="Add new Announcement"
            model= {appLocalizer.settings_fields['create_announcement']}
            method="post"
            modelname="create_announcement"
            url="mvx_module/v1/create_announcement"
            submit_title="Publish"
          />
         :

         get_current_name.get("AnnouncementID") ?

            this.state.edit_announcement_fileds && Object.keys(this.state.edit_announcement_fileds).length > 0 ? 
              <DynamicForm
                key={`dynamic-form-announcement-add-new`}
                className="mvx-announcement-add-new"
                title="Update Announcement"
                model= {this.state.edit_announcement_fileds['update_announcement_display']}
                method="post"
                announcement_id={get_current_name.get("AnnouncementID")}
                modelname="update_announcement"
                url="mvx_module/v1/update_announcement"
                submitbutton="false"
              />
            : <PuffLoader css={override} color={"#3f1473"} size={100} loading={true} />

          :

          <DataTable
            columns={this.state.columns_announcement}
            data={this.state.display_announcement}
            selectableRows
            pagination
          />
        }
      </div>

      :

      name == 'knowladgebase' ?

      <div className="mvx-backend-datatable-wrapper">
        <div className="button-secondary"><Link to={`?page=mvx#&submenu=work-board&name=knowladgebase&create=knowladgebase`}>Add Knowledgebase</Link></div>
        
        {get_current_name && get_current_name.get("create") == 'knowladgebase' ?

          <DynamicForm
            key={`dynamic-form-knowladgebase-add-new`}
            className="mvx-knowladgebase-add-new"
            title="Add new knowladgebase"
            model= {appLocalizer.settings_fields['create_knowladgebase']}
            method="post"
            modelname="create_knowladgebase"
            url="mvx_module/v1/create_knowladgebase"
            submit_title="Publish"
          />
         :

         get_current_name.get("knowladgebaseID") ?

            this.state.edit_knowledgebase_fileds && Object.keys(this.state.edit_knowledgebase_fileds).length > 0 ? 
              <DynamicForm
                key={`dynamic-form-knowladgebase-add-new`}
                className="mvx-knowladgebase-add-new"
                title="Update Announcement"
                model= {this.state.edit_knowledgebase_fileds['update_knowladgebase_display']}
                method="post"
                knowladgebase_id={get_current_name.get("knowladgebaseID")}
                modelname="update_knowladgebase"
                url="mvx_module/v1/update_knowladgebase"
                submitbutton="false"
              />
            : <PuffLoader css={override} color={"#cd0000"} size={100} loading={true} />

          :

          <DataTable
            columns={this.state.columns_knowladgebase}
            data={this.state.display_list_knowladgebase}
            selectableRows
            pagination
          />
        
        }

      </div>

      :

      name == 'store_review' ?

      'store_review'

      :

      name == 'report_abuse' ?

      'report_abuse'

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