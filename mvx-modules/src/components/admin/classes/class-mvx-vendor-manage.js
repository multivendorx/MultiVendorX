import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import DataTable from 'react-data-table-component';

import FilterComponent from 'react-data-table-component';

import {
  BrowserRouter as Router,
  Link,
  useLocation,
  withRouter,
  useParams,
  NavLink
} from "react-router-dom";


import Dialog from '@material-ui/core/Dialog';
import DialogActions from '@material-ui/core/DialogActions';
import DialogContent from '@material-ui/core/DialogContent';
import DialogContentText from '@material-ui/core/DialogContentText';
import DialogTitle from '@material-ui/core/DialogTitle';

import Button from '@material-ui/core/Button';

import DynamicForm from "../../../DynamicForm";

import HeaderSection from './class-mvx-page-header';

class App extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      error: null,
      isLoaded: false,
      items: [],
      checkedState: [],
      module_ids: [],
      open_model: false,
      open_child_model: [],
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
      filterText: '',
      resetPaginationToggle: false,
      module_ids: [],
      details_vendor: [],
      add_shipping_options_data: [],
      open1: false,
      vendor_shipping_option_choice: '',
      bulkselectlist: [],
      data_setting_fileds: [],
      
      /*filteredItems: fakeUsers.filter(
        item => item.name && item.name.toLowerCase().includes(filterText.toLowerCase()),
        ),*/

      columns_vendor: [
        {
            name: <div className="mvx-datatable-header-text">Name</div>,
            selector: row => <div className="mvx-vendor-table-name" dangerouslySetInnerHTML={{__html: row.name}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Email</div>,
            selector: row => <div className="mvx-vendor-table-email" dangerouslySetInnerHTML={{__html: row.email}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Registered</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.registered}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Products</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.products}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Status</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.status}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Action</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.action}}></div>,
            sortable: true,
        },
      ],

      columns_followers: [
        {
            name: <div className="mvx-datatable-header-text">Customer Name</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.name}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Date</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.time}}></div>,
            sortable: true,
        }
      ],

      columns_zone_shipping: [
        {
            name: <div className="mvx-datatable-header-text">Zone name</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.zone_name}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Region(s)</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.region}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Shipping method(s) </div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.shipping_method}}></div>,
            sortable: true,
        }
      ],

      data_zone_shipping: [],

      datavendor: [],
      datafollowers: [],

      data_zone_in_shipping: [],
    };

    this.ddddffffff = 'fffffffffffff';

    this.handleChange = this.handleChange.bind(this);

    this.subHeaderComponentMemo = this.subHeaderComponentMemo.bind(this);

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);

    this.Child = this.Child.bind(this);
  
    this.Childparent = this.Childparent.bind(this);

    this.handlevendorsearch = this.handlevendorsearch.bind(this);

    this.isClearable = this.isClearable.bind(this);

    this.handleClose = this.handleClose.bind(this);

    this.handlechildClose = this.handlechildClose.bind(this);

    this.handleaddshipping_method = this.handleaddshipping_method.bind(this);

    this.onChangeshipping = this.onChangeshipping.bind(this);

    this.handle_different_shipping_add = this.handle_different_shipping_add.bind(this);

    this.handle_different_shipping_delete = this.handle_different_shipping_delete.bind(this);


    this.handleOnChange = this.handleOnChange.bind(this);

    this.toggle_shipping_method = this.toggle_shipping_method.bind(this);

    this.update_post_code = this.update_post_code.bind(this);

    this.onChangeshippingoption = this.onChangeshippingoption.bind(this);

    this.handledeletevendor = this.handledeletevendor.bind(this);

    

  }

  handledeletevendor(e) {

    if(confirm("Confirm delete?") == true) {

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/vendor_delete`,
        data: {
          vendor_ids: this.state.bulkselectlist,
        }
      })
      .then( ( responce ) => {
        location.reload();
        if (responce.data.redirect_link) {
           window.location.href = responce.data.redirect_link;
        }
      } );
    }
  }

  onChangeshippingoption(e, vendor_id) {

    this.setState({
      vendor_shipping_option_choice: e.value
    });

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/update_specific_vendor_shipping_option`,
      data: {
        value: e.value,
        vendor_id: vendor_id,
      }
    })
    .then( ( responce ) => {
      console.log('success');
    } );
  }

  update_post_code(e, zone_id, vendor_id, type) {
    var getvalue = '';
    if (type == 'select_state') {
      getvalue = e;
      this.state.data_zone_in_shipping['get_database_state_name'] = getvalue;
      this.setState({
        data_zone_in_shipping: this.state.data_zone_in_shipping,
      });
    } else {
      getvalue = e.target.value;
    }

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/update_post_code`,
      data: {
        value: getvalue,
        vendor_id: vendor_id,
        zone_id: zone_id,
        type: type
      }
    })
    .then( ( responce ) => {
      console.log('success');
    } );
  }

  toggle_shipping_method(e, instance_id, zone_id, vendor_id) {
    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/toggle_shipping_method`,
      data: {
        value: e.target.checked,
        instance_id: instance_id,
        vendor_id: vendor_id,
        zone_id: zone_id
      }
    })
    .then( ( responce ) => {
      console.log('success');
    } );


    var params = {
      vendor_id: vendor_id,
      zone_id: zone_id
    };


    // update text
    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/specific_vendor_shipping_zone`, { params }
    )
    .then(response => {
      this.setState({
        data_zone_in_shipping: response.data,
      });
    })


  }

  handleOnChange(e, getdata, zoneid, vendorid, type) {
    
    if (type == 'title') {
      getdata['title'] = e.target.value;
      getdata['settings']['title'] = e.target.value;
    } else if (type == 'cost') {
      getdata['settings']['cost'] = e.target.value;
    } else if (type == 'tax') {
      getdata['settings']['tax_status'] = e.target.value;
    } else if (type == 'min_cost') {
      getdata['settings']['min_amount'] = e.target.value;
    }
    

    getdata['zone_id'] = zoneid;

    getdata['vendor_id'] = vendorid;

    getdata['method_id'] = getdata['id'];

    getdata['instance_id'] = getdata['instance_id'];

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/update_vendor_shipping_method`,
      data: {
        data_details: getdata,
        change_value: e.target.value,
        vendorid: vendorid,
        zoneid: zoneid
      }
    })
    .then( ( responce ) => {
      console.log('success');
    } );

  }

  handle_different_shipping_add(e, data, index) {
    var newModalShow = [...this.state.open_child_model];
    newModalShow[index] = true; 
    this.setState({
      open_child_model: newModalShow
    });
  }

  handle_different_shipping_delete(e, zone_id, instance_id, vendor_id) {
    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/delete_shipping_method`,
      data: {
        zone_id: zone_id,
        instance_id: instance_id,
        vendor_id: vendor_id
      }
    })
    .then( ( responce ) => {
      //console.log('success');
      location.reload();
    } );
  }

  handlechildClose(e) {
    var add_module_false = new Array(this.state.open_child_model.length).fill(false);
    this.setState({
      open_child_model: add_module_false
    });
  }

  onChangeshipping(e, zoneid, vendorid) {

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/add_vendor_shipping_method`,
      data: {
        method_id: e.value,
        vendorid: vendorid,
        zoneid: zoneid
      }
    })
    .then( ( responce ) => {
      console.log('success');
      location.reload();
    } );

  }


  handleaddshipping_method(e) {
    this.setState({
      open_model: true
    });
  }

  handleClose(e) {
    this.setState({
      open_model: false
    });
  }

  isClearable(e) {

  }

  handlevendorsearch(e) {
    if (e) {
      axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/search_specific_commission`, { params: { vendor_id: e.value } 
      })
      .then(response => {
        this.setState({
          datavendor: response.data,
        });
      })
    } else {
      axios({
        url: `${appLocalizer.apiUrl}/mvx_module/v1/all_commission`
      })
      .then(response => {
        this.setState({
          datavendor: response.data,
        });
      })
    }
  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo(e) {

/*    if (new URLSearchParams(window.location.hash).get("ID")) {

    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/list_of_all_tab_based_settings_field`, { params: { vendor_id: new URLSearchParams(window.location.hash).get("ID") } 
    })
    .then(response => {
      if (response.data) {
        this.setState({
          data_setting_fileds: response.data,
        });



      }

    })

      
    }
*/


    let queryt = this.useQuery();
    var tab_name_display = '';
    var tab_description_display = '';
    appLocalizer.mvx_all_backend_tab_list['marketplace-vendors'].map((data, index) => {
        if(queryt.get("name") == data.tabname) {
          tab_name_display = data.tablabel;
          tab_description_display = data.description;
        }
      }
    )

    if(queryt.get("ID")) {
      //window.location.href = window.location.href+'&name=vendor_personal';
    }
    return (
      <div>
          
      <HeaderSection />


      <div className="container">

      <div className="mvx-child-container">

       <div className="mvx-sub-container">
          
          {queryt.get("name") == 'add_new' ?
           <div className="general-tab-area">
              <div className="tabcontentclass tabcontentclass-child">
                <this.Childparent name={queryt.get("name")} />
              </div>
            </div>
          : '' }
        


      {!queryt.get("ID") ?

        queryt.get("name") == "add_new" ? '' :

          <div className="mvx-before-table-content">

            <div className="mvx-table-text-and-add-wrap">
              <div className="mvx-datatable-text">Vendors</div>
              <Link to={`?page=mvx#&submenu=vendor&name=add_new`}><span class="dashicons dashicons-plus"></span>Add Vendor</Link>
            </div>
            
            <div className="mvx-search-and-multistatus-wrap">

              <div className="mvx-multistatus-check">
                <div className="mvx-multistatus-check-all">All (10)</div>
                <div className="mvx-multistatus-check-approve">| Approve (10)</div>
                <div className="mvx-multistatus-check-pending status-active">| Pending (10)</div>
                <div className="mvx-multistatus-check-rejected">| Rejected (10)</div>
              </div>


              <div className="mvx-module-section-list-data"> 
                <label><span class="dashicons dashicons-search"></span></label>
                <input type="text" placeholder="Search Vendors" name="search"/>
              </div>

              { /*<Select placeholder="Search Vendors" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handlevendorsearch} /> */}
            </div>

            <div className="mvx-wrap-bulk-all-date">
              <div className="mvx-wrap-bulk-action">
                <Select placeholder="Bulk actions" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handlevendorsearch} />
                { /*<button type="button" className="button-secondary" onClick={(e) => this.handledeletevendor(e)}>Apply</button> */ }
              </div>

              <div className="mvx-wrap-date-action">
                <Select placeholder="All Dates" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handlevendorsearch} />
              </div>
            </div>

            { /*<button type="button" className="button-primary" onClick={(e) => this.handledeletevendor(e)}>Delete Vendor</button> */}
            <div className="mvx-backend-datatable-wrapper">
              <DataTable
                columns={this.state.columns_vendor}
                data={this.state.datavendor}
                selectableRows
                onSelectedRowsChange={this.handleChange}
                pagination
              />
            </div>

          </div>
        : 
          <div>
            <div className="general-tab-header-area">
              <div className="mvx-tab-name-display">{tab_name_display}</div>
              <p>{tab_description_display}</p>
            </div>

            <div className="general-tab-area">
              <ul className="mvx-general-tabs-list">
              {appLocalizer.mvx_all_backend_tab_list['marketplace-vendors'].map((data, index) => (
                  <li className={queryt.get("name") == data.tabname ? 'activegeneraltabs' : ''}><i class="mvx-font ico-store-icon"></i><Link to={`?page=mvx#&submenu=vendor&ID=${queryt.get("ID")}&name=${data.tabname}`} >{data.tablabel}</Link></li>
              ))}
              </ul>

              <div className="tabcontentclass">
                <this.Child name={queryt} />
              </div>
          </div>
        </div>
        }
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


   Childparent({ name }) {
  return (
    <div>
    {name ?
      <DynamicForm
      key={`dynamic-form-add-new`}
      className="mvx-vendor-add-new"
      title="Add new vendor"
      model= {appLocalizer.settings_fields['vendor_add_personal']}
      method="post"
      location={useLocation().search}
      modelname="vendor_add_personal"
      url="mvx_module/v1/create_vendor"
      />
      : '' }
    </div>
  );
}


  Child({ name }) {

    if (!new URLSearchParams(window.location.hash).get("ID")) {
      this.state.data_setting_fileds = [];
      this.state.data_zone_shipping = [];
    }

    if (!new URLSearchParams(window.location.hash).get("zone_id")) {
      this.state.data_zone_in_shipping = [];
    }

    if (new URLSearchParams(window.location.hash).get("ID")) {
      axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/list_of_all_tab_based_settings_field`, { params: { vendor_id: new URLSearchParams(window.location.hash).get("ID") } 
      })
      .then(response => {
        if (response.data && this.state.data_setting_fileds.length == 0) {

            this.setState({
              data_setting_fileds: response.data,
            });
            
        }
      })
    }


    if (new URLSearchParams(window.location.hash).get("name") == 'vendor_shipping') {
      axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/specific_vendor_shipping`, { params: { vendor_id: new URLSearchParams(window.location.hash).get("ID") } 
      })
      .then(response => {
        if (response.data && this.state.data_zone_shipping.length == 0) {
          this.setState({
            data_zone_shipping: response.data,
          });
        }
      })
    }


    if (new URLSearchParams(window.location.hash).get("zone_id")) {
      var params = {
        vendor_id: new URLSearchParams(window.location.hash).get("ID"),
        zone_id: new URLSearchParams(window.location.hash).get("zone_id")
      };

      axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/specific_vendor_shipping_zone`, { params }
      )
      .then(response => {

        var add_module_false = response.data.vendor_shipping_methods ? new Array(Object. keys(response.data.vendor_shipping_methods).length).fill(false) : '';
        if (response.data && this.state.data_zone_in_shipping.length == 0) {
          this.setState({
            data_zone_in_shipping: response.data,
            open_child_model: add_module_false
          });
        }
      })
    }
    
  return (
    <div>
    {appLocalizer.mvx_all_backend_tab_list['marketplace-vendors'].map((data, index) => (
      <div>
        
      {

        data.tabname == name.get("name") ?


          name.get("name") == 'vendor_followers' ? 

          <DataTable
            columns={this.state.columns_followers}
            data={this.state.datafollowers}
            selectableRows
            pagination
          />

          :

            name.get("name") == 'vendor_shipping' ? 

            name.get("zone_id") ? 






            <div>


            



              <table className="form-table wcmp-shipping-zone-settings wc-shipping-zone-settings">
                <tbody>
                  <tr>
                    <th scope="row" className="titledesc">
                      <label>
                        Zone Name
                      </label>
                    </th>
                    <td className="forminp">{this.state.data_zone_in_shipping.zones ? this.state.data_zone_in_shipping.zones.data.zone_name : ''}</td>
                  </tr>

                  <tr>
                    <th scope="row" className="titledesc">
                      <label htmlFor>
                      Zone region
                      </label>
                    </th>
                    <td className="forminp">{this.state.data_zone_in_shipping.zones ? this.state.data_zone_in_shipping.zones.formatted_zone_location : ''}</td>
                  </tr>

                  <tr>
                    
                  </tr>

                  <tr className="hide_if_zone_not_limited">
                    <th scope="row" className="titledesc">
                      <label htmlFor>
                      Select specific states
                      </label>
                    </th>
                    <td className="forminp">
                        <Select
                        value={this.state.data_zone_in_shipping.get_database_state_name ? this.state.data_zone_in_shipping.get_database_state_name : ''}
                        isMulti
                        options={this.state.data_zone_in_shipping.state_select}
                        onChange={(e) => this.update_post_code(e,  name.get("zone_id"), name.get("ID"), 'select_state')}
                        >
                        </Select>
                    </td>
                  </tr>

                  <tr className="hide_if_zone_not_limited">
                    <th className="titledesc">
                      <label htmlFor>
                      Set your postcode
                      </label>
                    </th>
                    <td className="mvx-settings-basic-input-class">
                      <input id="select_zone_postcodes" className="mvx-setting-form-input" type="text" defaultValue={this.state.data_zone_in_shipping.postcodes} placeholder="Postcodes need to be comma separated" onChange={(e) => this.update_post_code(e,  name.get("zone_id"), name.get("ID"), 'postcode')}/>
                    </td>
                  </tr>

                  <tr>
                    <th className="titledesc">
                      <label>
                      Shipping methods
                      </label>
                    </th>
                    <td className>
                      <table className="wcmp-shipping-zone-methods wc-shipping-zone-methods widefat">
                        <thead>
                          <tr>   
                            <th className="wcmp-title wc-shipping-zone-method-title">Title</th>
                            <th className="wcmp-enabled wc-shipping-zone-method-enabled">Enabled</th> 
                            <th className="wcmp-description wc-shipping-zone-method-description">Description</th>
                            <th className="wcmp-action">Action</th>
                          </tr>
                        </thead>

                        <tfoot>
                          <tr>
                            <td>
                              <Button onClick={(e) => this.handleaddshipping_method(e)} className="button wcmp-shipping-zone-show-method wc-shipping-zone-add-method">Add shipping method</Button>
                            </td>
                          </tr>
                        </tfoot>

                        <tbody>


                        {this.state.data_zone_in_shipping.vendor_shipping_methods  ?
                          
                          Object.entries(this.state.data_zone_in_shipping.vendor_shipping_methods).map((data, index) => (

                            <tr className="wcmp-shipping-zone-method">
                              <td>
                                {data[1].title}
                              </td>
                              <td className="wcmp-shipping-zone-method-enabled wc-shipping-zone-method-enabled"> 
                                <span>
                                  <input className="inputcheckbox" defaultChecked={data[1].enabled && data[1].enabled == 'yes' ? true : false} type="checkbox" name="method_status" onChange={(e) => this.toggle_shipping_method(e, data[1].instance_id, name.get("zone_id"), name.get("ID"))} />
                                </span>
                              </td>

                              <td>
                                {data[1].settings.description}
                              </td>

                              <td>
                                <div className="mvx-actions">
                                  <span className="edit"><Button onClick={(e) => this.handle_different_shipping_add(e, data[1], index)} className="button wcmp-shipping-zone-show-method wc-shipping-zone-add-method">EDIT</Button>
                                  </span>
                                  <span className="delete"><Button onClick={(e) => this.handle_different_shipping_delete(e, name.get("zone_id"), data[1].instance_id, name.get("ID"))} className="button wcmp-shipping-zone-show-method wc-shipping-zone-add-method">DELETE</Button></span>
                                </div>
                              </td>




                              <Dialog open={this.state.open_child_model[index]} onClose={this.handlechildClose} aria-labelledby="form-dialog-title">
                            <DialogTitle id="form-dialog-title"><div className="wcmp-module-dialog-title">Differnet method</div></DialogTitle>
                            <DialogContent>
                            <DialogContentText>

                                    {data[1].id && data[1].id == 'flat_rate' ?

                                    <div className="mvx-vendor-shipping-method-details">
                                      <label className="control-label col-sm-3 col-md-3">Method Title</label>
                                      <input type="text" defaultValue={data[1].title} onChange={(e) => this.handleOnChange(e, data[1], name.get("zone_id"), name.get("ID"), 'title')}/>
                                    
                                      <label className="control-label col-sm-3 col-md-3">Cost</label>
                                      <input id="method_cost_fr" className="form-control" type="number" defaultValue={data[1].settings.cost} placeholder="0.00" onChange={(e) => this.handleOnChange(e, data[1], name.get("zone_id"), name.get("ID"), 'cost')}/>
                                    
                                      <select defaultValue={data[1].settings.tax_status} onChange={(e) => this.handleOnChange(e, data[1], name.get("zone_id"), name.get("ID"), 'tax')}>
                                        <option value="none">None</option>
                                        <option value="taxable">Taxable</option>
                                      </select>
                                    </div>

                                    :

                                    data[1].id == 'local_pickup' ?

                                     <div>
                                      <label className="control-label col-sm-3 col-md-3">Method Title</label>
                                      <input type="text" defaultValue={data[1].title} onChange={(e) => this.handleOnChange(e, data[1], name.get("zone_id"), name.get("ID"), 'title')}/>
                                    
                                      <label className="control-label col-sm-3 col-md-3">Cost</label>
                                      <input id="method_cost_fr" className="form-control" type="number" defaultValue={data[1].settings.cost} placeholder="0.00" onChange={(e) => this.handleOnChange(e, data[1], name.get("zone_id"), name.get("ID"), 'cost')}/>
                                    
                                      <select defaultValue={data[1].settings.tax_status} onChange={(e) => this.handleOnChange(e, data[1], name.get("zone_id"), name.get("ID"), 'tax')}>
                                        <option value="none">None</option>
                                        <option value="taxable">Taxable</option>
                                      </select>
                                    </div>


                                     :

                                    data[1].id == 'free_shipping' ? 

                                    <div>
                                      <label className="control-label col-sm-3 col-md-3">Method Title</label>
                                      <input type="text" defaultValue={data[1].title} onChange={(e) => this.handleOnChange(e, data[1], name.get("zone_id"), name.get("ID"), 'title')}/>
                                    
                                      <label className="control-label col-sm-3 col-md-3">Cost</label>
                                      <input id="method_cost_fr" className="form-control" type="number" defaultValue={data[1].settings.min_amount} placeholder="0.00" onChange={(e) => this.handleOnChange(e, data[1], name.get("zone_id"), name.get("ID"), 'min_cost')}/>
                                    
                                    </div>

                                    :

                                    ''

                                    }

                            </DialogContentText>
                            </DialogContent>
                            <DialogActions>
                            <Button onClick={this.handlechildClose} color="primary">Cancel</Button>
                            </DialogActions>
                            </Dialog>



                            </tr>


                            




                          ))


                        :


                        <tr>
                          <td colspan="4">You can add multiple shipping methods within this zone. Only customers within the zone will see them</td>
                        </tr>

                        }

                          
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>


              <Dialog open={this.state.open_model} onClose={this.handleClose} aria-labelledby="form-dialog-title">
                <DialogTitle id="form-dialog-title"><div className="wcmp-module-dialog-title">Add shipping method</div></DialogTitle>
                <DialogContent>
                  <DialogContentText>

                    <p>Choose the shipping method you wish to add. Only shipping methods which support zones are listed</p>

                    <Select
                      className="shipping_method"
                      options={this.state.add_shipping_options_data}
                      onChange={e => {
                        this.onChangeshipping(e, name.get("zone_id"), name.get("ID"));
                      }} 
                    >
                    </Select>
                    <div className="wc-shipping-zone-method-description">Lets you charge a rate for shipping.</div>
                  </DialogContentText>
                </DialogContent>
                <DialogActions>
                  <Button onClick={this.handleClose} color="primary">Cancel</Button>
                </DialogActions>
              </Dialog>


            </div>

            :

            <div>
            {console.log(this.state.data_setting_fileds.shipping_options ? this.state.data_setting_fileds.vendor_default_shipping_options : '')}

              { this.state.data_setting_fileds.shipping_options ?
                
                <div>

                  <Select
                    className="shipping_choice"
                    options={this.state.data_setting_fileds.shipping_options}
                    defaultValue={this.state.data_setting_fileds.vendor_default_shipping_options}
                    onChange={e => {
                      this.onChangeshippingoption(e, name.get("ID"));
                    }} 
                  />

                  {this.state.vendor_shipping_option_choice == 'distance_by_zone' || this.state.data_setting_fileds.vendor_default_shipping_options[0].value == 'distance_by_zone' ?
                    <DataTable
                      columns={this.state.columns_zone_shipping}
                      data={this.state.data_zone_shipping}
                      selectableRows
                      pagination
                    />
                  :
                  
                  this.state.vendor_shipping_option_choice == 'distance_by_shipping' ? 

                    <DynamicForm
                      key={`dynamic-form`}
                      className="class"
                      title="distance wise shipping"
                      model= {appLocalizer.settings_fields['distance_shipping']}
                      method="post"
                      modelname="distance_shipping"
                      vendor_id={name.get("ID")}
                      url="mvx_module/v1/update_vendor"
                      submitbutton="false"
                    />
                  :

                  this.state.vendor_shipping_option_choice == 'shipping_by_country' ? 

                    <DynamicForm
                    key={`dynamic-form`}
                    className="class"
                    title="country wise shipping"
                    model= {appLocalizer.settings_fields['country_shipping']}
                    method="post"
                    modelname="country_shipping"
                    vendor_id={name.get("ID")}
                    url="mvx_module/v1/update_vendor"
                    submitbutton="false"
                    />
                  
                  : '' }

                  </div>

                : '' }

             </div>

            :
            <div>

            { this.state.data_setting_fileds && Object.keys(this.state.data_setting_fileds).length > 0 ?

              <DynamicForm
              key={`dynamic-form-${data.tabname}`}
              className={data.classname}
              title={data.tablabel}
              model= {this.state.data_setting_fileds[data.modelname]}
              method="post"
              vendor_id={name.get("ID")}
              modelname={data.modelname}
              url={data.apiurl}
              submitbutton="false"
              />

              : '' }

            </div>
            
        : ''
      }
      </div>
    ))}
    </div>
  );
}


  handleChange(e) {
    this.setState({
      bulkselectlist: e.selectedRows,
    });
  }

  subHeaderComponentMemo(e) {
      React.useMemo(() => {
      

      return (
      <FilterComponent filterText={this.state.filterText} />
      );
      }, [this.state.filterText, this.state.resetPaginationToggle]);
  }

  componentDidUpdate(prevProps) {



  }

  componentDidMount() {

  


    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/all_vendors`
    })
    .then(response => {
      this.setState({
        datavendor: response.data,
      });
    })

    axios.get(
    `${appLocalizer.apiUrl}/mvx_module/v1/all_vendor_followers`, { params: { vendor_id: new URLSearchParams(window.location.hash).get("ID") } 
    })
    .then(response => {
      this.setState({
        datafollowers: response.data,
      });
    })


    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/vendor_list_search`
    })
    .then(response => {
      this.setState({
        details_vendor: response.data,
      });
    })



    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/add_shipping_option`
    })
    .then(response => {
      this.setState({
        add_shipping_options_data: response.data,
      });
    })

    if (new URLSearchParams(window.location.hash).get("ID") && new URLSearchParams(window.location.hash).get("name") == 'vendor_shipping' && this.state.data_setting_fileds && Object.keys(this.state.data_setting_fileds).length > 0 && this.state.data_setting_fileds.vendor_default_shipping_options[0]) {
      this.setState({
        vendor_shipping_option_choice: this.state.data_setting_fileds.vendor_default_shipping_options[0].value,
      });
    }
    

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