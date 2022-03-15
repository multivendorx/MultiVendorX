import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import PuffLoader from "react-spinners/PuffLoader";
import { css } from "@emotion/react";

import { ReactSortable } from "react-sortablejs";


//import DynamicDataTable from "@langleyfoxall/react-dynamic-data-table";

import styled from 'styled-components'
import { useTable, usePagination, useRowSelect } from 'react-table'

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
      bulkselectlist: [],
      display_announcement: [],
      knowledge_data_fileds: [],
      edit_announcement_fileds: [],
      edit_knowledgebase_fileds: [],
      display_list_knowladgebase: [],
      list_of_pending_vendor_product: [],
      list_of_pending_vendor: [],
      list_of_pending_vendor_coupon: [],
      list_of_pending_transaction: [],
      list_of_pending_question: [],
      list_of_store_review: [],
      columns_announcement_new: [],



      datassssssssssss: [
     {
       col1: 'Hello',
       col2: 'World',
     },
     {
       col1: 'react-table',
       col2: 'rocks',
     },
     {
       col1: 'whatever',
       col2: 'you want',
     },
   ],

      columnssssssssssssssssss: [
     {
       Header: 'Column 1',
       accessor: 'col1', // accessor is the "key" in the data
     },
     {
       Header: 'Column 2',
       accessor: 'col2',
     },
   ],




      columns_announcement: [
        {
            name: <div className="mvx-datatable-header-text">Title</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.title}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Vendors</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.vendor}}></div>,
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




      pending_product: [
        {
            name: <div className="mvx-datatable-header-text">Vendor Name</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.vendor}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Product Name</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.product}}></div>,
            sortable: true,
        },
      ],

      pending_vendor: [
        {
            name: <div className="mvx-datatable-header-text">Edit</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.vendor}}></div>,
            sortable: true,
        }
      ],

      pending_coupon: [
        {
            name: <div className="mvx-datatable-header-text">Vendor Name</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.vendor}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Coupon Name</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.coupon}}></div>,
            sortable: true,
        },
      ],

      pending_tranaction: [
        {
            name: <div className="mvx-datatable-header-text">Vendor Name</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.vendor}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Commission</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.commission}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Amount</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.amount}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Account Detail</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.account_details}}></div>,
            sortable: true,
        },
      ],
      
      pending_questions: [
        {
            name: <div className="mvx-datatable-header-text">Question by</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.question_by}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Product Name</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.product_name}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Question details</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.question_details}}></div>,
            sortable: true,
        },
      ],

      store_review: [
        {
            name: <div className="mvx-datatable-header-text">Customer</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.author}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Vendor</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.user_id}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Content</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.content}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Time</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.time}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">review</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.review}}></div>,
            sortable: true,
        },
      ],



    };

    this.query = null;
    // when click on checkbox

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);

    this.Child = this.Child.bind(this);

    this.handle_post_check_publish = this.handle_post_check_publish.bind(this);

    this.handle_post_bulk_status = this.handle_post_bulk_status.bind(this);
    
    this.onSelectedRowsChange = this.onSelectedRowsChange.bind(this);

    this.handle_work_board_chenage = this.handle_work_board_chenage.bind(this);

  }

  handle_work_board_chenage(e, type) {
    if (type == 'announcement' && e) {
      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/update_custom_post_status`,
        data: {
          ids: this.state.bulkselectlist,
          value: e.value
        }
      })
      .then( ( responce ) => {
      } );
    }
  }


  onSelectedRowsChange(e) {
    this.setState({
      bulkselectlist: e.selectedRows,
    });
  }

  handle_post_check_publish(e) {
    console.log('saffffffffffff');
  }


  handle_post_bulk_status(e, type) {

    if (type == 'announcement') {
      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/search_announcement`,
        data: {
          ids: this.state.bulkselectlist,
          value: e.target.value
        }
      })
      .then( ( responce ) => {
        this.setState({
          display_announcement: responce.data,
        });

      } );
    }
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


    // pending details


    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_question`
    )
    .then(response => {
      this.setState({
        list_of_pending_question: response.data,
      });
    })

    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_transaction`
    )
    .then(response => {
      this.setState({
        list_of_pending_transaction: response.data,
      });
    })

    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_vendor_coupon`
    )
    .then(response => {
      this.setState({
        list_of_pending_vendor_coupon: response.data,
      });
    })

    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_vendor`
    )
    .then(response => {
      this.setState({
        list_of_pending_vendor: response.data,
      });
    })

    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_vendor_product`
    )
    .then(response => {
      this.setState({
        list_of_pending_vendor_product: response.data,
      });
    })

    // fetch review
    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/list_of_store_review`
    )
    .then(response => {
      this.setState({
        list_of_store_review: response.data,
      });
    })



    
    



  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {

    // delete post start
    var this_data = this;
    var $ = jQuery;
      $('.dismiss_button').unbind().click(function (e) {

       e.preventDefault();

       if ( confirm("Are you sure to delete?") ) {
         $.ajax({
          url: `${appLocalizer.apiUrl}/mvx_module/v1/delete_post_details`,
          data: {
              ids: $(this).attr('data-id'),
          },
          type: 'POST',
          success: function( response ) {
            this_data.setState({
              display_announcement: response,
            });  
          }
        });
       }

    });
    // delete post end


    // update announcement table

      /*if (new URLSearchParams(window.location.hash).get("name") == 'announcement') {
        this.state.display_announcement = [];

        var hhvhvh = '';

        axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`
        )
        .then(response => {
          if (hhvhvh == '') {
            hhvhvh = 'abc';
            this.setState({
              display_announcement: response.data,
            });
          }
        })

      }*/

      /*if (new URLSearchParams(window.location.hash).get("name") == 'announcement') {

        
      }*/

    // update announcement table end


    let query_name = this.useQuery();
    if(!query_name.get("name")) {
      //window.location.href = window.location.href+'&name=activity_reminder';
    }
    var tab_name_display = '';
    var tab_description_display = '';
    appLocalizer.mvx_all_backend_tab_list['marketplace-workboard'].map((data, index) => {
        if(query_name.get("name") == data.modulename) {
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
                    {appLocalizer.mvx_all_backend_tab_list['marketplace-workboard'].map((data, index) => (
                        <Link to={`?page=mvx#&submenu=work-board&name=${data.modulename}`} ><li className={query_name.get("name") == data.modulename ? 'activedashboardtabs' : ''}>{data.icon ? <i class={`mvx-font ${data.icon}`}></i> : ''}{data.tablabel}</li></Link>
                    ))}
                  </ul>
                    <this.Child name={query_name.get("name")} />
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








  // Display table column and row slection
  if (this.state.columns_announcement_new.length == 0 && new URLSearchParams(window.location.hash).get("name") == 'announcement') {
      appLocalizer.columns_announcement.map((data_ann, index_ann) => {
        var data_selector = '';
        var set_for_dynamic_column = '';
        data_selector = data_ann['selector_choice'];
        data_ann.selector = row => <div dangerouslySetInnerHTML={{__html: row[data_selector]}}></div>;
        this.state.columns_announcement_new[index_ann] = data_ann
        set_for_dynamic_column = this.state.columns_announcement_new;
            this.setState({
              columns_announcement_new: set_for_dynamic_column,
            });
        }
      )
    }
    // Display table column and row slection end

  return (
    <div>
    {
      name == appLocalizer.mvx_all_backend_tab_list['marketplace-workboard'][0]['modulename'] ?

      <div className="mvx-module-grid">



        <div className="mvx-todo-status-check">
            <div className="mvx-text-with-line-wrapper">
                <div className="mvx-report-text">Vendor Verification</div>
                <div className="mvx-report-text-fade-line"></div>


                <div className="mvx-select-all-bulk-wrap">
                  <div className="mvx-select-all-checkbox">
                    <input type="checkbox" className="mvx-select-all" />
                    <span className="mvx-select-all-text">Select All</span>
                  </div>

                  <Select placeholder="Bulk Action" options={this.state.details_vendor} isClearable={true} className="mvx-module-vendor-section-nav-child-data" onChange={(e) => this.handle_work_board_chenage(e)} />
                </div>

            </div>
            <div className="mvx-workboard-card-wrapper">
                <div className="mvx-workboard-card-wrapper-child">
                    <div className="mvx-workboard-card-wrapper-heading">Address Verification</div>
                    <div className="mvx-workboard-top-part">
                        <div className="mvx-workboard-img-part">
                            <img alt="Multivendor X" src={appLocalizer.mvx_logo}/>
                            <div className="mvx-workboard-vendor-name">vendor test 01</div>
                        </div>
                        <div className="mvx-workboard-select-icon"><input type="checkbox" className="mvx-workboard-checkbox"/></div>
                    </div>
                    <div className="mvx-workboard-address-area">
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>

                    </div>
                    <div className="mvx-module-current-status wp-clearfix">
                        <div className="mvx-left-icons-wrap">
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>

                        </div>

                    </div>
                </div>





                <div className="mvx-workboard-card-wrapper-child">
                    <div className="mvx-workboard-card-wrapper-heading">Address Verification</div>
                    <div className="mvx-workboard-top-part">
                        <div className="mvx-workboard-img-part">
                            <img alt="Multivendor X" src={appLocalizer.mvx_logo}/>
                            <div className="mvx-workboard-vendor-name">vendor test 01</div>
                        </div>
                        <div className="mvx-workboard-select-icon"><input type="checkbox" className="mvx-workboard-checkbox"/></div>
                    </div>
                    <div className="mvx-workboard-address-area">
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>

                    </div>
                    <div className="mvx-module-current-status wp-clearfix">
                        <div className="mvx-left-icons-wrap">
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                        <div className="mvx-right-icons-wrap">
                          <div className="mvx-right-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                    </div>
                </div>



                <div className="mvx-workboard-card-wrapper-child">
                    <div className="mvx-workboard-card-wrapper-heading">Address Verification</div>
                    <div className="mvx-workboard-top-part">
                        <div className="mvx-workboard-img-part">
                            <img alt="Multivendor X" src={appLocalizer.mvx_logo}/>
                            <div className="mvx-workboard-vendor-name">vendor test 01</div>
                        </div>
                        <div className="mvx-workboard-select-icon"><input type="checkbox" className="mvx-workboard-checkbox"/></div>
                    </div>
                    <div className="mvx-workboard-address-area">
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>

                    </div>
                    <div className="mvx-module-current-status wp-clearfix">
                        <div className="mvx-left-icons-wrap">
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                        <div className="mvx-right-icons-wrap">
                          <div className="mvx-right-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                    </div>
                </div>



                <div className="mvx-workboard-card-wrapper-child">
                    <div className="mvx-workboard-card-wrapper-heading">Address Verification</div>
                    <div className="mvx-workboard-top-part">
                        <div className="mvx-workboard-img-part">
                            <img alt="Multivendor X" src={appLocalizer.mvx_logo}/>
                            <div className="mvx-workboard-vendor-name">vendor test 01</div>
                        </div>
                        <div className="mvx-workboard-select-icon"><input type="checkbox" className="mvx-workboard-checkbox"/></div>
                    </div>
                    <div className="mvx-workboard-address-area">
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>

                    </div>
                    <div className="mvx-module-current-status wp-clearfix">
                        <div className="mvx-left-icons-wrap">
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                        <div className="mvx-right-icons-wrap">
                          <div className="mvx-right-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                    </div>
                </div>




                
              </div>
        </div>



















        <div className="mvx-todo-status-check">
            <div className="mvx-text-with-line-wrapper">
                <div className="mvx-report-text">Vendor Verification</div>
                <div className="mvx-report-text-fade-line"></div>


                <div className="mvx-select-all-bulk-wrap">
                  <div className="mvx-select-all-checkbox">
                    <input type="checkbox" className="mvx-select-all" />
                    <span className="mvx-select-all-text">Select All</span>
                  </div>

                  <Select placeholder="Bulk Action" options={this.state.details_vendor} isClearable={true} className="mvx-module-vendor-section-nav-child-data" onChange={(e) => this.handle_work_board_chenage(e)} />
                </div>

            </div>
            <div className="mvx-workboard-card-wrapper">
                <div className="mvx-workboard-card-wrapper-child">
                    <div className="mvx-workboard-card-wrapper-heading">Address Verification</div>
                    <div className="mvx-workboard-top-part">
                        <div className="mvx-workboard-img-part">
                            <img alt="Multivendor X" src={appLocalizer.mvx_logo}/>
                            <div className="mvx-workboard-vendor-name">vendor test 01</div>
                        </div>
                        <div className="mvx-workboard-select-icon"><input type="checkbox" className="mvx-workboard-checkbox"/></div>
                    </div>
                    <div className="mvx-workboard-address-area">
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>

                    </div>
                    <div className="mvx-module-current-status wp-clearfix">
                        <div className="mvx-left-icons-wrap">
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>

                        </div>

                    </div>
                </div>





                <div className="mvx-workboard-card-wrapper-child">
                    <div className="mvx-workboard-card-wrapper-heading">Address Verification</div>
                    <div className="mvx-workboard-top-part">
                        <div className="mvx-workboard-img-part">
                            <img alt="Multivendor X" src={appLocalizer.mvx_logo}/>
                            <div className="mvx-workboard-vendor-name">vendor test 01</div>
                        </div>
                        <div className="mvx-workboard-select-icon"><input type="checkbox" className="mvx-workboard-checkbox"/></div>
                    </div>
                    <div className="mvx-workboard-address-area">
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>

                    </div>
                    <div className="mvx-module-current-status wp-clearfix">
                        <div className="mvx-left-icons-wrap">
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                        <div className="mvx-right-icons-wrap">
                          <div className="mvx-right-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                    </div>
                </div>



                <div className="mvx-workboard-card-wrapper-child">
                    <div className="mvx-workboard-card-wrapper-heading">Address Verification</div>
                    <div className="mvx-workboard-top-part">
                        <div className="mvx-workboard-img-part">
                            <img alt="Multivendor X" src={appLocalizer.mvx_logo}/>
                            <div className="mvx-workboard-vendor-name">vendor test 01</div>
                        </div>
                        <div className="mvx-workboard-select-icon"><input type="checkbox" className="mvx-workboard-checkbox"/></div>
                    </div>
                    <div className="mvx-workboard-address-area">
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>

                    </div>
                    <div className="mvx-module-current-status wp-clearfix">
                        <div className="mvx-left-icons-wrap">
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                        <div className="mvx-right-icons-wrap">
                          <div className="mvx-right-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                    </div>
                </div>



                <div className="mvx-workboard-card-wrapper-child">
                    <div className="mvx-workboard-card-wrapper-heading">Address Verification</div>
                    <div className="mvx-workboard-top-part">
                        <div className="mvx-workboard-img-part">
                            <img alt="Multivendor X" src={appLocalizer.mvx_logo}/>
                            <div className="mvx-workboard-vendor-name">vendor test 01</div>
                        </div>
                        <div className="mvx-workboard-select-icon"><input type="checkbox" className="mvx-workboard-checkbox"/></div>
                    </div>
                    <div className="mvx-workboard-address-area">
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>
                        <p className="mvx-todo-list-details-data-value">
                        <div className="mvx-commission-label-class">Email Address:</div>
                        <div className="mvx-commission-value-class"><a href="">bvjhb@gmail.com</a></div>
                        </p>

                    </div>
                    <div className="mvx-module-current-status wp-clearfix">
                        <div className="mvx-left-icons-wrap">
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                            <div className="mvx-left-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                        <div className="mvx-right-icons-wrap">
                          <div className="mvx-right-icon"><i className="mvx-font ico-store-icon"></i></div>
                        </div>

                    </div>
                </div>




                
              </div>
        </div>



    </div>

      :

      name == appLocalizer.mvx_all_backend_tab_list['marketplace-workboard'][1]['modulename'] ?
      <div className="mvx-module-grid">

        <div className="mvx-table-text-and-add-wrap">
          <Link to={`?page=mvx#&submenu=work-board&name=announcement&create=announcement`}><span class="dashicons dashicons-plus"></span>Add Announcement</Link>
        </div>



        {get_current_name && get_current_name.get("create") == 'announcement' ?

          <DynamicForm
            key={`dynamic-form-announcement-add-new`}
            className="mvx-announcement-add-new"
            title="Add new Announcement"
            model= {appLocalizer.settings_fields['create_announcement']}
            method="post"
            modulename="create_announcement"
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
                modulename="update_announcement"
                url="mvx_module/v1/update_announcement"
                submitbutton="false"
              />
            : <PuffLoader css={override} color={"#3f1473"} size={100} loading={true} />

          :


          <div>
            <div className="mvx-search-and-multistatus-wrap">
              <div className="mvx-multistatus-check">
                <div className="mvx-multistatus-check-all">All (10)</div>
                <div className="mvx-multistatus-check-approve" onClick={this.handle_post_check_publish}>| Published (10)</div>
                <div className="mvx-multistatus-check-pending status-active">| Pending (10)</div>
              </div>


              <div className="mvx-module-section-list-data"> 
                <label><span class="dashicons dashicons-search"></span></label>
                <input type="text" placeholder="Search Announcement" onChange={(e) => this.handle_post_bulk_status(e, 'announcement')}/>
              </div>


            </div>


            <div className="mvx-wrap-bulk-all-date">
              <div className="mvx-wrap-bulk-action">
                <Select placeholder="Bulk actions" options={appLocalizer.post_bulk_status} isClearable={true} className="mvx-module-section-list-data" onChange={(e) => this.handle_work_board_chenage(e, 'announcement')}/>
              </div>

              <div className="mvx-wrap-date-action">
                <Select placeholder="All Dates" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_work_board_chenage} />
              </div>
            </div>


            <div className="mvx-backend-datatable-wrapper">
              {this.state.columns_announcement_new && this.state.columns_announcement_new.length > 0 ?
                <DataTable
                  columns={this.state.columns_announcement_new}
                  data={this.state.display_announcement}
                  selectableRows
                  onSelectedRowsChange={this.onSelectedRowsChange}
                  pagination
                />
              : '' }
            </div>





          </div>
        }
      </div>

      :

      name == appLocalizer.mvx_all_backend_tab_list['marketplace-workboard'][2]['modulename'] ?

      <div className="mvx-module-grid">


        <div className="mvx-table-text-and-add-wrap">
          <Link to={`?page=mvx#&submenu=work-board&name=knowladgebase&create=knowladgebase`}><span class="dashicons dashicons-plus"></span>Add Knowladgebase</Link>
        </div>

        {get_current_name && get_current_name.get("create") == 'knowladgebase' ?

          <DynamicForm
            key={`dynamic-form-knowladgebase-add-new`}
            className="mvx-knowladgebase-add-new"
            title="Add new knowladgebase"
            model= {appLocalizer.settings_fields['create_knowladgebase']}
            method="post"
            modulename="create_knowladgebase"
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
                modulename="update_knowladgebase"
                url="mvx_module/v1/update_knowladgebase"
                submitbutton="false"
              />
            : <PuffLoader css={override} color={"#cd0000"} size={100} loading={true} />

          :

          <div>
            <div className="mvx-search-and-multistatus-wrap">
              <div className="mvx-multistatus-check">
                <div className="mvx-multistatus-check-all">All (10)</div>
                <div className="mvx-multistatus-check-approve" onClick={this.handle_post_check_publish}>| Published (10)</div>
                <div className="mvx-multistatus-check-pending status-active">| Pending (10)</div>
              </div>


              <div className="mvx-module-section-list-data"> 
                <label><span class="dashicons dashicons-search"></span></label>
                <input type="text" placeholder="Search Knowledgebase" name="search"/>
              </div>
            </div>


            <div className="mvx-wrap-bulk-all-date">
              <div className="mvx-wrap-bulk-action">
                <Select placeholder="Bulk actions" options={appLocalizer.post_bulk_status} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_post_bulk_status} />
              </div>

              <div className="mvx-wrap-date-action">
                <Select placeholder="All Dates" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_work_board_chenage} />
              </div>
            </div>

            <div className="mvx-backend-datatable-wrapper">

              <DataTable
                columns={this.state.columns_knowladgebase}
                data={this.state.display_list_knowladgebase}
                selectableRows
                pagination
              />
            </div>
          </div>
        
        }

      </div>

      :

      name == appLocalizer.mvx_all_backend_tab_list['marketplace-workboard'][3]['modulename'] ?

        <div className="mvx-module-grid">
          <div className="mvx-search-and-multistatus-wrap">
            <div className="mvx-multistatus-check">
              <div className="mvx-multistatus-check-all">All (10)</div>
              <div className="mvx-multistatus-check-approve" onClick={this.handle_post_check_publish}>| Approve (10)</div>
              <div className="mvx-multistatus-check-pending status-active">| Pending (10)</div>
            </div>


            <div className="mvx-module-section-list-data"> 
              <label><span class="dashicons dashicons-search"></span></label>
              <input type="text" placeholder="Search Review" name="search"/>
            </div>
          </div>


          <div className="mvx-wrap-bulk-all-date">
            <div className="mvx-wrap-bulk-action">
              <Select placeholder="Bulk actions" options={appLocalizer.post_bulk_status} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_post_bulk_status} />
            </div>

            <div className="mvx-wrap-date-action">
              <Select placeholder="All Dates" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_work_board_chenage} />
            </div>
          </div>

          <div className="mvx-backend-datatable-wrapper">
            <DataTable
                columns={this.state.store_review}
                data={this.state.list_of_store_review}
                selectableRows
                pagination
              />
          </div>
      </div>

      :

      name == appLocalizer.mvx_all_backend_tab_list['marketplace-workboard'][4]['modulename'] ?

      'report_abuse'

      :

      name == appLocalizer.mvx_all_backend_tab_list['marketplace-workboard'][5]['modulename'] ?

        <div className="mvx-module-grid">

          <div className="mvx-search-and-multistatus-wrap">
            <div className="mvx-multistatus-check">
              <div className="mvx-multistatus-check-all">All (10)</div>
              <div className="mvx-multistatus-check-approve" onClick={this.handle_post_check_publish}>| Published (10)</div>
              <div className="mvx-multistatus-check-pending status-active">| Pending (10)</div>
            </div>


            <div className="mvx-module-section-list-data"> 
              <label><span class="dashicons dashicons-search"></span></label>
              <input type="text" placeholder="Search Question" name="search"/>
            </div>
          </div>


          <div className="mvx-wrap-bulk-all-date">
            <div className="mvx-wrap-bulk-action">
              <Select placeholder="Bulk actions" options={appLocalizer.post_bulk_status} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_post_bulk_status} />
            </div>

            <div className="mvx-wrap-date-action">
              <Select placeholder="All Dates" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_work_board_chenage} />
            </div>
          </div>

          <div className="mvx-backend-datatable-wrapper">
            {this.state.pending_questions ?
              <DataTable
                  columns={this.state.pending_questions}
                  data={this.state.list_of_pending_question}
                  selectableRows
                  pagination
                />
            : '' }
          </div>
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