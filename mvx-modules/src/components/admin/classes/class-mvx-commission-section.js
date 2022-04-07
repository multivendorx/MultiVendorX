import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import RingLoader from "react-spinners/RingLoader";
import { css } from "@emotion/react";

import { ReactSortable } from "react-sortablejs";

import DataTable from 'react-data-table-component';

import {
  BrowserRouter as Router,
  Link,
  useLocation,
  withRouter,
  useParams,
  NavLink
} from "react-router-dom";


import DynamicForm from "../../../DynamicForm";

import { CSVLink } from "react-csv";

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
      commission_select_option_open: false,
      lastname: '',
      email: '',
      abcarray: [],
      first_toggle: '',
      second_toggle: '',      
      current: {},
      commission_details: [],
      updated_commission_status: [],
      get_commission_id_status: [],
      columns_commission_list: [],
      datacommission: [],

      data_paid_commission: [],
      data_unpaid_commission: [],

      details_commission: [

      ],
      show_commission_status: [

      ],

      show_vendor_name: [

      ],

      commisson_bulk_choose: [

      ],

      commissiondata: [
      ],


    };

    this.query = null;

    this.handleChange = this.handleChange.bind(this);

    this.handlecommissionsearch = this.handlecommissionsearch.bind(this);

    this.handlecommissionwork = this.handlecommissionwork.bind(this);
    
    this.handleupdatecommission = this.handleupdatecommission.bind(this);

    this.handleCommisssionDismiss = this.handleCommisssionDismiss.bind(this);
     
    this.handle_commission_live_search = this.handle_commission_live_search.bind(this);

    this.handlecommission_paid = this.handlecommission_paid.bind(this);
    
     
  }

  handlecommission_paid(e) {
    this.setState({
      commission_select_option_open: true,
    });
  }

  handle_commission_live_search(e) {

    if (e.target.value) {
      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/search_specific_commission`, { params: { commission_ids: e.target.value } 
      })
      .then(response => {
        //console.log(response.data);
        this.setState({
          datacommission: response.data,
        });
      })
    } else {
      axios({
        url: `${appLocalizer.apiUrl}/mvx_module/v1/all_commission`
      })
      .then(response => {
        this.setState({
          datacommission: response.data,
        });
      })
    }

  }

  handleCommisssionDismiss(e) {
    
      if(confirm("Confirm delete?") == true) {
        axios({
          method: 'post',
          url: `${appLocalizer.apiUrl}/mvx_module/v1/commission_delete`,
          data: {
            commission_ids: e,
          }
        })
        .then( ( response ) => {
          this.setState({
            datacommission: response.data,
          });

        } );
      }

  }

  handleupdatecommission(e) {
    /*this.setState({
      commission_select_option_open: false,
    });*/

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/update_commission_status`,
      data: {
        value: e.value,
        commission_id: new URLSearchParams(window.location.hash).get("CommissionID")
      }
    })
    .then( ( responce ) => {


      var params = {
        commission_id: new URLSearchParams(window.location.hash).get("CommissionID"),
      };
      axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/details_specific_commission`, { params }
      )
      .then(responsenew => {
        this.setState({
          commission_details: responsenew.data,
        });
      })



      this.setState({
        commission_select_option_open: false,
      });
    } );

  }

  handlecommissionwork(e) {
    if (e) {

      if (this.state.commisson_bulk_choose.length > 0) {
        axios({
          method: 'post',
          url: `${appLocalizer.apiUrl}/mvx_module/v1/update_commission_bulk`,
          data: {
            value: e.value,
            commission_list: this.state.commisson_bulk_choose
          }
        })
        .then( ( responce ) => {
            this.setState({
              datacommission: response.data,
            });
        } );
      } else {
        alert('Please select commission');
      }
    } else {
        axios({
          url: `${appLocalizer.apiUrl}/mvx_module/v1/all_commission`
        })
        .then(response => {
          this.setState({
            datacommission: response.data,
          });
        })
    }



  }


  componentDidUpdate(prevProps) {

    if (new URLSearchParams(window.location.hash).get("CommissionID")) {
      var set_default_value = this.state.commission_details.length;
      set_default_value = 0;
      //complete commission details
      var params = {
        commission_id: new URLSearchParams(window.location.hash).get("CommissionID"),
      };
      axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/details_specific_commission`, { params }
      )
      .then(response => {
        if (response.data && this.state.commission_details.commission_id != new URLSearchParams(window.location.hash).get("CommissionID")) {
          this.setState({
            commission_details: response.data,
          });
        }
      })
    }
  }


  componentDidMount() {
    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/all_commission`
    })
    .then(response => {
      this.setState({
        datacommission: response.data,
      });
    })

    // paid status
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/show_commission_from_status_list`, { params: { commission_status: 'paid' } 
    })
    .then(response => {
      this.setState({
        data_paid_commission: response.data,
      });
    })

    // unpaid status
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/show_commission_from_status_list`, { params: { commission_status: 'unpaid' } 
    })
    .then(response => {
      this.setState({
        data_unpaid_commission: response.data,
      });
    })

    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/commission_list_search`
    })
    .then(response => {
      this.setState({
        details_commission: response.data,
      });
    })

    // show commision status
    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/show_commission_status_list`
    })
    .then(response => {
      this.setState({
        show_commission_status: response.data,
      });
    })

    // get vendor name on select
    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/show_vendor_name`
    })
    .then(response => {
      this.setState({
        show_vendor_name: response.data,
      });
    })

    //complete commission details
    var params = {
      commission_id: new URLSearchParams(window.location.hash).get("CommissionID"),
    };
    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/details_specific_commission`, { params }
    )
    .then(response => {
      this.setState({
        commission_details: response.data,
      });
    })


    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/get_commission_id_status`, { params }
    )
    .then(response => {
      this.setState({
        get_commission_id_status: response.data,
      });
    })










    // Display table column and row slection
    if (this.state.columns_commission_list.length == 0 && new URLSearchParams(window.location.hash).get("submenu") == 'commission') {
      appLocalizer.columns_commission.map((data_ann, index_ann) => {
        var data_selector = '';
        var set_for_dynamic_column = '';
        data_selector = data_ann['selector_choice'];
        data_ann.selector = row => <div dangerouslySetInnerHTML={{__html: row[data_selector]}}></div>;


        data_ann.cell ? data_ann.cell = (row) => <div className="mvx-vendor-action-icon">

          <a href={row.link}><i className="mvx-font icon-edit"></i></a>
          <div onClick={() => this.handleCommisssionDismiss(row.id)} id={row.id}><i className="mvx-font icon-no"></i></div>

        </div> : '';


        this.state.columns_commission_list[index_ann] = data_ann
        set_for_dynamic_column = this.state.columns_commission_list;
            this.setState({
              columns_commission_list: set_for_dynamic_column,
            });
        }
      )
    }
    // Display table column and row slection end


  }

  handleChange(e) {
    var commission_list = [];
    e.selectedRows.map((data, index) => {
        commission_list[index] = data.id;
    })
    this.setState({
      commisson_bulk_choose: commission_list,
    });


    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/update_commission_bulk`,
      data: {
        value: 'export',
        commission_list: commission_list
      }
    })
    .then( ( response ) => {
      this.setState({
        commissiondata: response.data,
      });
    } );

  }

  handlecommissionsearch(e, status) {

    if (status == 'searchstatus') {

        if (e) {
          axios.get(
            `${appLocalizer.apiUrl}/mvx_module/v1/show_commission_from_status_list`, { params: { commission_status: e.value } 
          })
          .then(response => {
            this.setState({
              datacommission: response.data,
            });
          })
        } else {
          axios({
            url: `${appLocalizer.apiUrl}/mvx_module/v1/all_commission`
          })
          .then(response => {
            this.setState({
              datacommission: response.data,
            });
          })
        }

    } else if(status == 'showvendor') {
      if (e) {
        axios.get(
          `${appLocalizer.apiUrl}/mvx_module/v1/show_vendor_name_from_list`, { params: { vendor_name: e.value } 
        })
        .then(response => {
          this.setState({
            datacommission: response.data,
          });
        })
      } else {
        axios({
          url: `${appLocalizer.apiUrl}/mvx_module/v1/all_commission`
        })
        .then(response => {
          this.setState({
            datacommission: response.data,
          });
        })
      }
    } else {

      /*if (e) {
        axios.get(
          `${appLocalizer.apiUrl}/mvx_module/v1/search_specific_commission`, { params: { commission_ids: e.value } 
        })
        .then(response => {
          this.setState({
            datacommission: response.data,
          });
        })
      } else {
        axios({
          url: `${appLocalizer.apiUrl}/mvx_module/v1/all_commission`
        })
        .then(response => {
          this.setState({
            datacommission: response.data,
          });
        })
      }*/

    }
  }

  render() {
    //console.log(this.state.commission_details);
    return (
      <div>

        <HeaderSection />

      { new URLSearchParams(window.location.hash).get("CommissionID") 

      ?

      this.state.commission_details ?
      <div className="container">
        <div className="mvx-child-container">
          <div className="mvx-sub-container">
            <div className="woocommerce-order-data">

              <div className="mvx-datatable-text">Edit Commission</div>

              { /* Commission Details Start */  }
              <div className="mvx-commission-details-section">
                <div className="woocommerce-order-data-heading">
                  {this.state.commission_details.commission_type_object ? this.state.commission_details.commission_type_object.labels.singular_name + ' #' + this.state.commission_details.commission_id + ' ' + appLocalizer.commission_page_string.details : ''}
                </div>

                <div className="mvx-commission-wrap-vendor-order-status">

                  <p className="commission-details-data-value" dangerouslySetInnerHTML={{__html: this.state.commission_details.meta_list_associate_vendor}} ></p>

                  <p className="commission-details-data-value">
                    <div className="mvx-commission-label-class">{appLocalizer.commission_page_string.associated_order}:</div>
                    <div className="mvx-commission-value-class"><a href={this.state.commission_details.order_edit_link}>#{this.state.commission_details.commission_order_id}</a></div>
                  </p>

                  <p className="commission-details-data-value">
                    <div className="mvx-commission-label-class">{appLocalizer.commission_page_string.order_status}:</div>
                    <div className="mvx-commission-value-class">{this.state.commission_details.order_status_display}</div>
                  </p>

                </div>

                <div className="mvx-commission-wrap-amount-shipping-tax">

                  <div className="mvx-commission-status-wrap">
                    { this.state.commission_select_option_open ? <div className="commission-status-hide-and-show-wrap"><p className="commission-status-text-check">Commission status: </p><Select placeholder="Status" options={appLocalizer.commission_status_list_action} defaultValue={this.state.get_commission_id_status} className="mvx-module-section-nav-child-data" onChange={(e) => this.handleupdatecommission(e)} /></div> : '' }
                    { !this.state.commission_select_option_open ? <div className="woocommerce-order-data-meta order_number" dangerouslySetInnerHTML={{__html: this.state.commission_details.order_meta_details}} ></div> : '' }
                    { !this.state.commission_select_option_open ? <i className="mvx-font icon-edit" onClick={(e) => this.handlecommission_paid(e)}></i> : '' }

                  </div>

                  { /*<p className="woocommerce-order-data-meta order_number" dangerouslySetInnerHTML={{__html: this.state.commission_details.order_meta_details}} ></p> */ }
                  
                  <p className="form-field form-field-wide mvx-commission-amount">
                    <div className="mvx-commission-label-class">{appLocalizer.commission_page_string.commission_amount}:</div>
                    <div className="mvx-commission-value-class">
                      <p dangerouslySetInnerHTML={{__html: this.state.commission_details.commission_amount != this.state.commission_details.commission_total_calculate ? this.state.commission_details.commission_totals : this.state.commission_details.commission_total_calculate}} ></p>
                    </div>
                  </p>

                  <p className="commission-details-data-value">
                      <div className="mvx-commission-label-class">{appLocalizer.commission_page_string.shipping}:</div>
                      <div className="mvx-commission-value-class">
                        <p dangerouslySetInnerHTML={{__html: this.state.commission_details.shipping_amount != this.state.commission_details.commission_shipping_totals ? this.state.commission_details.commission_shipping_totals_output : this.state.commission_details.commission_shipping_totals}} ></p>
                      </div>
                  </p>

                  <p className="commission-details-data-value">
                    <div className="mvx-commission-label-class">{appLocalizer.commission_page_string.tax}:</div>
                    <div className="mvx-commission-value-class">
                      <p dangerouslySetInnerHTML={{__html: this.state.commission_details.tax_amount != this.state.commission_details.commission_tax_total ? this.state.commission_details.commission_tax_total_output : this.state.commission_details.commission_tax_total}} ></p>
                    </div>
                  </p>
                </div>
              </div>
              {/* Commission Details End */}

              {/* Commission vendor and order details start*/}
              <div className="mvx-order-details-vendor-details-wrap">



                  {/* Commission order and others details start*/}
                  <div className="mvx-order-details-wrap">
                    

                      {/* Commission order details start*/}
                      <div className="mvx-commission-order-details-text">Order Details</div>
                      <div className="mvx-commission-order-data woocommerce_order_items_wrapper wc-order-items-editable">
                        <table cellpadding="0" cellspacing="0" className="woocommerce_order_items">
                            <thead>
                                <tr>
                                  <th className="item sortable" colspan="2">Item</th>
                                  <th className="item_cost sortable" data-sort="float">Cost</th>
                                  <th className="quantity sortable" data-sort="int">Qty</th>
                                  <th className="line_cost sortable" data-sort="float">Total</th>
                                </tr>
                            </thead>
                            
                            <tbody id="order_line_items">

                                <tr>

                                  {this.state.commission_details.line_items ? 
                                    <td className="thumb">
                                      <p dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items.item_thunbail}}></p>
                                    </td>
                                  : '' }

                                  {this.state.commission_details.line_items ? 
                                  <td>
                                    <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items.product_link_display}}></div>
                                    <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items.product_sku}}></div>
                                    <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items.check_variation_id ? this.state.commission_details.line_items.variation_id_text : ''}}></div>
                                    
                                    {this.state.commission_details.line_items.check_variation_id ? <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items.get_variation_post_type === 'product_variation' ? this.state.commission_details.line_items.item_variation_display : this.state.commission_details.line_items.no_longer_exist}}></div> : '' }

                                    <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items.close_div}}></div>


                                    <div className="view">
                                    {
                                      this.state.commission_details.line_items.meta_format_data ? 

                                      <table cellspacing="0" className="display_meta">
                                        {
                                        this.state.commission_details.line_items.meta_data.map((data, index) => (
                                          <tr>
                                            <th>{data.display_key}:</th>
                                            <td><div dangerouslySetInnerHTML={{__html: data.display_value}}></div></td>
                                          </tr>
                                        ))
                                        }
                                      </table>

                                      : ''
                                    }
                                    </div>
                                  </td>
                                  : ''}


                                  <td className="item_cost">
                                      <div className="view">
                                          <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items ? this.state.commission_details.line_items.item_cost : ''}}></div>
                                          <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items ? this.state.commission_details.line_items.line_cost_html : ''}}></div>
                                      </div>
                                  </td>

                                  <td className="quantity">
                                      <div className="view">
                                          <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items ? this.state.commission_details.line_items.quantity_1st : ''}}></div>
                                          <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items ? this.state.commission_details.line_items.quantity_2nd : ''}}></div>
                                      </div>
                                  </td>

                                  <td class="line_cost">
                                    <div class="view">
                                        <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items ? this.state.commission_details.line_items.line_cost : ''}}></div>
                                        <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items ? this.state.commission_details.line_items.line_cost_1st : ''}}></div>
                                        <div dangerouslySetInnerHTML={{__html: this.state.commission_details.line_items ? this.state.commission_details.line_items.line_cost_2nd : ''}}></div>
                                    </div>
                                  </td>


                                </tr>

                            </tbody>
                          </table>
                      </div>
                    {/* Commission order details end*/}














                    {/* Commission order details start*/}
                      <div className="mvx-commission-order-details-text">Shipping</div>
                      <div className="mvx-commission-order-data woocommerce_order_items_wrapper wc-order-items-editable">
                        <table cellpadding="0" cellspacing="0" className="woocommerce_order_items">
                            
                            <tbody id="order_line_items">
                                <tr>
                                  <td className="thumb"></td>
                                  {this.state.commission_details.shipping_items_details ? 
                                  <td>
                                    <p dangerouslySetInnerHTML={{__html: this.state.commission_details.shipping_items_details.shipping_text}}></p>
                                    <div className="view">
                                    {
                                      this.state.commission_details.shipping_items_details.meta_data ? 

                                      <table cellspacing="0" className="display_meta">
                                        {
                                        this.state.commission_details.shipping_items_details.meta_data.map((data, index) => (
                                          <tr>
                                            <th>{data.display_key}:</th>
                                            <td><div dangerouslySetInnerHTML={{__html: data.display_value}}></div></td>
                                          </tr>
                                        ))
                                        }
                                      </table>

                                      : ''
                                    }
                                    </div>
                                  </td>
                                  : ''}

                                  <td className="item_cost" width="1%">&nbsp;</td>

                                  <td className="quantity" width="1%">&nbsp;</td>

                                  <td className="line_cost" width="1%">
                                    <div className="view">
                                        <p dangerouslySetInnerHTML={{__html: this.state.commission_details.shipping_items_details ? this.state.commission_details.shipping_items_details.shipping_price : ''}}></p>
                                        <p dangerouslySetInnerHTML={{__html: this.state.commission_details.shipping_items_details ? this.state.commission_details.shipping_items_details.refunded_shipping : ''}}></p>
                                    </div>
                                  </td>

                                </tr>

                            </tbody>
                          </table>
                      </div>
                    {/* Commission order details end*/}







                      <div className="wc-used-coupons">
                        <ul className="wc_coupon_list">
                                                </ul>
                      </div>


                      <div className="mvx-wrap-table-commission-and-coupon-commission">
                        <div className="mvx-coupon-shipping-tax">
                          <ul className="mvx-child-coupon-shipping-tax">
                            { this.state.commission_details.order_total_discount > 0 && this.state.commission_details.commission_include_coupon ? <li><em>*Commission calculated including coupon</em></li> : '' }
                            { this.state.commission_details.is_shipping > 0 && this.state.commission_details.commission_total_include_shipping ? <li><em>*Commission total calcutated including shipping charges.</em></li> : '' }
                            { this.state.commission_details.is_tax > 0 && this.state.commission_details.commission_total_include_tax ? <li><em>*Commission total calcutated including tax charges.</em></li> : '' }
                         </ul>
                        </div>

                        <table className="mvx-order-totals">
                          <tbody>
                            <tr>
                              <td className="mvx-order-label-td">
                              {this.state.commission_details.order_total_discount > 0 && this.state.commission_details.commission_include_coupon ? '*' : ''}
                              {appLocalizer.commission_page_string.commission}:
                              </td>
                              <td width="1%" />
                              <td className="total">
                                <div dangerouslySetInnerHTML={{__html: this.state.commission_details.formated_commission_total}}></div>
                              </td>
                            </tr>

                           { this.state.commission_details.get_shipping_method ?
                            <tr>
                              <td className="mvx-order-label-td">
                                {appLocalizer.commission_page_string.shipping}:
                              </td>
                              <td width="1%" />
                              <td className="total">
                              <div dangerouslySetInnerHTML={{__html: this.state.commission_details.get_total_shipping_refunded > 0 ? this.state.commission_details.refund_shipping_display : this.state.commission_details.else_shipping}}></div>
                              </td>
                            </tr>
                            : '' }


                            {this.state.commission_details.tax_data && Object.keys(this.state.commission_details.tax_data).length > 0 ? 
                              Object.keys(this.state.commission_details.tax_data).map((data, index) => (
                              <tr>
                                <td className="mvx-order-label-td">
                                  <div dangerouslySetInnerHTML={{__html: data.tax_label}}></div>
                                </td>
                                <td width="1%" />
                                <td className="total">
                                <div dangerouslySetInnerHTML={{__html: data.get_total_tax_refunded_by_rate_id > 0 ? data.greater_zero : data.else_output}}></div>
                                </td>
                              </tr>
                              ))
                            : '' }
                            
                            <tr>
                              <td className="mvx-order-label-td">
                                **{appLocalizer.commission_page_string.total}:
                              </td>
                              <td width="1%" />
                              <td className="total">
                              <div dangerouslySetInnerHTML={{__html: !this.state.commission_details.is_migration_order && this.state.commission_details.commission_total != this.state.commission_details.commission_total_edit ? this.state.commission_details.commission_total_display : this.state.commission_details.commission_total_edit}}></div>
                              </td>
                            </tr>


                            {this.state.commission_details.is_refuned ? 
                            <tr>
                              <td className="label refunded-total">
                                {appLocalizer.commission_page_string.refunded}:
                              </td>
                              <td width="1%" />
                              <td className="total refunded-total">
                              <div dangerouslySetInnerHTML={{__html: this.state.commission_details.refunded_output}}></div>
                              </td>
                            </tr>
                            : '' }

                          </tbody>
                        </table>
                      </div>

                </div>
                {/* Commission order and others details end*/}


                {/* Commission vendor and notes details start*/}
                <div className="mvx-vendor-notes-details-wrap">
                  {/* Commission vendor details start*/}
                  <div className="mvx-vendor-details-wrap">
                    <div className="mvx-commission-vendor-details-class">{appLocalizer.commission_page_string.vendor_details}</div>
                    {this.state.commission_details.vendor ? 
                      <div className="mvx-child-vendor-details">

                        <p className="commission-details-data-value">
                          <div className="mvx-commission-label-class"><p dangerouslySetInnerHTML={{__html: this.state.commission_details.avater_image}} ></p></div>
                          <div className="mvx-commission-value-class"><a href={this.state.commission_details.vendor_edit_link}>{this.state.commission_details.vendor.user_data.data.display_name}</a></div>
                        </p>

                        <p className="commission-details-data-value">
                          <div className="mvx-commission-label-class">{appLocalizer.commission_page_string.email}:</div>
                          <div className="mvx-commission-value-class"><a href={`mailto:${this.state.commission_details.vendor.user_data.data.user_email}`}>{this.state.commission_details.vendor.user_data.data.user_email}</a></div>
                        </p>

                        <p className="commission-details-data-value">
                          <div className="mvx-commission-label-class">{appLocalizer.commission_page_string.payment_mode}:</div>
                          <div className="mvx-commission-value-class">{this.state.commission_details.payment_title}</div>
                        </p>
                      </div>
                    : '' }
                  </div>
                  {/* Commission vendor details end*/}

                  {/* Commission notes start*/}
                  <div className="mvx-notes-details-wrap">
                    <div className="mvx-commission-notes-details-class">{appLocalizer.commission_page_string.commission_notes}</div>
                      <div className="mvx_commision_note_clm">
                        <p dangerouslySetInnerHTML={{__html: this.state.commission_details.notes_data ? this.state.commission_details.notes_data.comment_content : ''}}></p>
                        <small dangerouslySetInnerHTML={{__html: this.state.commission_details.notes_data ? this.state.commission_details.notes_data.comment_date : ''}}></small>
                      </div>
                    </div>
                  </div>
                  {/* Commission notes end*/}
                </div>
                {/* Commission vendor and notes details end*/}
          
          </div>
          {/* Commission vendor and order details end*/}

        </div>


          <div className="mvx-adv-image-display">
            <a href="https://www.qries.com/" target="__blank">
              <img alt="Multivendor X" src={appLocalizer.multivendor_logo}/>
            </a>
          </div>

          </div>

        </div>

      : ''

      :

        <div className="container">

          <div className="mvx-child-container">

          <div className="mvx-sub-container">
            <div className="mvx-commission-table-text-and-add-wrap">
              <div className="mvx-datatable-text">Commission</div>
              <CSVLink data={this.state.commissiondata} headers={appLocalizer.commission_header} filename={"Commissions.csv"} className="button-commission-secondary"><i className="mvx-font icon-download"></i>Download CSV</CSVLink>
            </div>

            <div className="mvx-search-and-multistatus-wrap">
              <div className="mvx-multistatus-check">
                <div className="mvx-multistatus-check-all">All ({this.state.datacommission.length})</div>
                <div className="mvx-multistatus-check-paid status-active">| Paid ({this.state.data_paid_commission.length})</div>
                <div className="mvx-multistatus-check-paid">| Unpaid ({this.state.data_unpaid_commission.length})</div>
              </div>

              <div className="mvx-module-search-commission-data"> 
                <label><i className="mvx-font icon-search"></i></label>
                <input type="text" placeholder="Search Commissions" name="search" onChange={this.handle_commission_live_search}/>
              </div>
            </div>


            <div className="mvx-wrap-bulk-all-date">
              <div className="mvx-wrap-bulk-action">
                { /*<button type="button" className="button-secondary">Apply</button> */ }
              </div>

              <div className="mvx-wrap-bulk-action">
                <Select placeholder={appLocalizer.commission_page_string.show_commission_status} options={this.state.show_commission_status} isClearable={true} className="mvx-module-section-nav-child-data" onChange={(e) => this.handlecommissionsearch(e, 'searchstatus')} />
                <Select placeholder={appLocalizer.commission_page_string.show_all_vendor} options={this.state.show_vendor_name} isClearable={true} className="mvx-module-section-nav-child-data" onChange={(e) => this.handlecommissionsearch(e, 'showvendor')} />
                <Select placeholder={appLocalizer.commission_page_string.bulk_action} options={appLocalizer.commission_bulk_list_option} isClearable={true} className="mvx-module-section-nav-child-data" onChange={(e) => this.handlecommissionwork(e)} />
              </div>
            </div>

            <div className="mvx-backend-datatable-wrapper">

              {this.state.columns_commission_list && this.state.columns_commission_list.length > 0 ?
              <DataTable
                columns={this.state.columns_commission_list}
                data={this.state.datacommission}
                selectableRows
                onSelectedRowsChange={this.handleChange}
                pagination
              />
              : ''}

            </div>

          </div>

          <div className="mvx-adv-image-display">
            <a href="https://www.qries.com/" target="__blank">
              <img alt="Multivendor X" src={appLocalizer.multivendor_logo}/>
            </a>
          </div>

          </div>

        </div>


      }
      </div>
    );
  }
}
export default App;