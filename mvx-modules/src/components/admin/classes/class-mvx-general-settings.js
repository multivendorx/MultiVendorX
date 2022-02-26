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
      
const override = css`
  display: block;
  margin: 0 auto;
  border-color: red;
`;

class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      testing: '',
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
      abcarraynew: [],
      current: {},
      
    };

    this.query = null;
    // when click on checkbox
    this.handleSubmitl = this.handleSubmitl.bind(this);

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);

    this.Child = this.Child.bind(this);

    this.handleAddClick = this.handleAddClick.bind(this);
    
    this.onlebelchange = this.onlebelchange.bind(this);

    this.removeFormField = this.removeFormField.bind(this);
    
    this.addSelectBoxOption = this.addSelectBoxOption.bind(this);

    this.removeSelectboxOption = this.removeSelectboxOption.bind(this);

    this.handleSaveRegistration = this.handleSaveRegistration.bind(this);

    this.togglePostboxField = this.togglePostboxField.bind(this);

    this.togglePostbox = this.togglePostbox.bind(this);

    this.togglevendorStoreField = this.togglevendorStoreField.bind(this);

  }

  togglePostbox(e) {
    if (this.state.first_toggle === "") {
      this.state.first_toggle = "closed";
    } else {
      this.state.first_toggle = "";
    }
    this.setState({
      first_toggle: this.state.first_toggle
    });
  }

  togglevendorStoreField(e) {
    if (this.state.second_toggle === "") {
      this.state.second_toggle = "closed";
    } else {
      this.state.second_toggle = "";
    }
    this.setState({
      second_toggle: this.state.second_toggle
    });
  }

  togglePostboxField(e, index) {
    if (this.state.abcarray[index].hidden) {
      this.state.abcarray[index].hidden = false;
    } else {
      this.state.abcarray[index].hidden = true;
    }
    this.setState({
      abcarray: this.state.abcarray
    });
  }

  handleSaveRegistration(e) {
    this.setState({ loading: true });
    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/save_registration`,
      data: {
        form_data: JSON.stringify(this.state.abcarray),
      }
    })
    .then( ( res ) => {
      console.log('success');
      this.setState({ loading: false });
    } );

  }

  addSelectBoxOption(e, index) {
    var count = this.state.abcarray[index].options.length + 1;
    this.state.abcarray[index].options.push({value: 'option' + count, label: 'Option ' + count, selected: false});
    this.setState({
      abcarray: this.state.abcarray
    });
  }

  removeSelectboxOption(e, index, key) {
    this.state.abcarray[index].options.splice(key, 1);
    this.setState({
      abcarray: this.state.abcarray
    });
  }

  removeFormField(e, index) {
    this.state.abcarray.splice(index, 1);
    this.setState({
      abcarray: this.state.abcarray
    });
  }

  onlebelchange(e, index, label, childindex) {
    
    var save_value;
    if (label == 'required' || label == 'muliple' || label == 'selected') {
      save_value = e.target.checked;
    } else {
      save_value = e.target.value;
    }

    let items = this.state.abcarray;

    if (label == 'selected') {
      items[index]['fileType'][childindex][label] = save_value;
    } else if (label == 'select_option') {
      items[index]['options'][childindex]['label'] = save_value;
    } else if (label == 'selected_radio_box') {

      items[index]['options'][childindex]['selected'] = save_value;
      items[index]['options'].map((number, indexs) => {
        if(childindex !== indexs) {
          items[index]['options'][indexs]['selected'] = false;
        }
      });

    } else if (label == 'selected_box') {
      items[index]['options'][childindex]['selected'] = e.target.checked;
    } else if (label == 'select_option1') {
      items[index]['options'][childindex]['value'] = save_value;
    } else {
      items[index][label] = save_value;
    }

    this.setState({
      items,
    });
  }

  handleAddClick(e, type, b) {
    var formJson = this.state.abcarray;
    var jsonLength = formJson.length;
    var label = b;

    switch (type) {

          case 'textbox' :
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    required: false,
                    cssClass: '',
                    tip_description: ''
                });
                break;

          case 'selectbox':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    selecttype: 'radio',
                    label: label,
                    hidden: false,
                    required: false,
                    options: [
                        {
                            value: 'option1',
                            label: 'Option 1',
                            selected: false
                        },
                        {
                            value: 'option2',
                            label: 'Option 2',
                            selected: true
                        },
                        {
                            value: 'option3',
                            label: 'Option 3',
                            selected: false
                        }
                    ],
                    cssClass: ''
                });
                break;
            case 'email':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    required: false,
//                    emailValidation: false,
                    cssClass: ''
                });
                break;
            case 'textarea':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    defaultValue: '',
                    limit : '',
                    required: false,
                    cssClass: '',
                    tip_description: ''
                });
                break;
            case 'checkbox':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    defaultValue: 'unchecked',
                    required: false,
                    cssClass: ''
                });
                break;
            case 'recaptcha':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    recaptchatype: 'v3',
                    hidden: false,
                    script: '',
                });
                break;
            case 'file':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    fileSize: '',
                    fileType: [
                        {
                            value : 'application/pdf',
                            label : 'PDF',
                            selected : false
                        },
                        {
                            value : 'image/jpeg',
                            label : 'JPEG',
                            selected : false
                        },
                        {
                            value : 'image/png',
                            label : 'PNG',
                            selected : false
                        },
                        {
                            value : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            label : 'DOC',
                            selected : false
                        },
                        {
                            value : 'application/vnd.ms-excel',
                            label : 'xls',
                            selected : false
                        }
                    ],
                    required: false,
                    muliple: false,
                    cssClass: ''
                });
                break;
            case 'separator':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    cssClass: ''
                });
                break;
            case 'vendor_description':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    defaultValue: '',
                    limit : '',
                    required: false,
                    cssClass: ''
                });
                break;
            case 'vendor_address_1':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    required: false,
                    cssClass: ''
                });
                break;
            case 'vendor_address_2':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    required: false,
                    cssClass: ''
                });
                break;
            case 'vendor_phone':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    required: false,
                    cssClass: ''
                });
                break;
            case 'vendor_country':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    required: false,
                    cssClass: ''
                });
                break;
            case 'vendor_state':
                // add dependancies with vendor country
                if (jQuery.inArray('vendor_country', jQuery.map(formJson, function(v) { return v.type; })) > -1) {
                    formJson.push({
                        id: jsonLength,
                        type: type,
                        label: label,
                        hidden: false,
                        placeholder: '',
                        required: false,
                        cssClass: ''
                    });
                } else { alert('vendor_registration_param.lang.need_country_dependancy');}
                break;
            case 'vendor_city':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    required: false,
                    cssClass: ''
                });
                break;
            case 'vendor_postcode':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    required: false,
                    cssClass: ''
                });
                break;
            case 'vendor_paypal_email':
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    required: false,
                    cssClass: ''
                });
                break;
            default :
                formJson.push({
                    id: jsonLength,
                    type: type,
                    label: label,
                    hidden: false,
                    placeholder: '',
                    required: false,
                    cssClass: ''
                });
                break;
    }

    this.setState({
      abcarray: formJson
    });
    console.log(formJson);
  }

  handleSubmitl(event) {
    event.preventDefault();
    return false;

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/save_settings`,
      data: {
        firstname: this.state.firstname,
        lastname: this.state.lastname,
        email: this.state.email
      }
    })
    .then( ( res ) => {
      console.log('success');
    } );
  }

  componentDidMount() {

  axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/get_registration`
    })
    .then(response => {
      this.setState({
        abcarray: response.data ? response.data : []
      });
    })

    var formJson4 = this.state.abcarraynew;
    formJson4.push({
      id: formJson4.length,
      type: 'multiple_choice',
      label: '',
      hidden: false,
      placeholder: '',
      required: false,
      cssClass: '',
      tip_description: ''
    });

    this.setState({
        abcarraynew: formJson4
      });
  }

  useQuery() {
    return new URLSearchParams(useLocation().search);
  }

  QueryParamsDemo() {
    let queryt = this.useQuery();

    if(!queryt.get("name")) {
      window.location.href = window.location.href+'&name=settings-general';
    }

    var tab_name_display = '';
    var tab_description_display = '';
    appLocalizer.mvx_all_backend_tab_list['marketplace-general-settings'].map((data, index) => {
        if(queryt.get("name") == data.tabname) {
          tab_name_display = data.tablabel;
          tab_description_display = data.description;
        }
      }
    )

    return (

      <div>

        <div className="mvx-module-section-nav">
          <div className="mvx-module-nav-left-section">
            <div className="mvx-module-section-nav-child-data">
              <img src={appLocalizer.mvx_logo} className="mvx-section-img-fluid"/>
            </div>
            <h1 className="mvx-module-section-nav-child-data">
              {appLocalizer.marketplace_text}
            </h1>
          </div>
          <div className="mvx-module-nav-right-section">
            <Select placeholder={appLocalizer.search_module_placeholder} options={this.state.module_ids} className="mvx-module-section-top-nav-select" isLoading={this.state.isLoading} onChange={this.handleselectmodule} />
            <a href={appLocalizer.knowledgebase} title={appLocalizer.knowledgebase_title} target="_blank" className="mvx-module-section-nav-child-data"><i className="dashicons dashicons-admin-users"></i></a>
          </div>
        </div>

      <div className="container">
      <div className="mvx-child-container">
        <div className="mvx-sub-container">
          <div className="general-tab-header-area">
            <h1>{tab_name_display}</h1>
            <p>{tab_description_display}</p>
          </div>

          <div className="general-tab-area">
            <ul className="mvx-general-tabs-list">
            {appLocalizer.mvx_all_backend_tab_list['marketplace-general-settings'].map((data, index) => (
                <li className={queryt.get("name") == data.tabname ? 'activegeneraltabs' : ''} ><i class="mvx-font ico-store-icon"></i><Link to={`?page=general-settings&name=${data.tabname}`} className={queryt.get("name") == data.tabname ? data.activeclass : ''}>{data.tablabel}</Link></li>
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
 
  return (
    <div>
    {appLocalizer.mvx_all_backend_tab_list['marketplace-general-settings'].map((data, index) => (
      <div>

      {
        /*name = !name ? 'registration' : name,*/

        data.tabname == name ?
          data.tabname == 'registration' ?

            <div id="nav-menus-frame">


            

            {this.state.abcarraynew.map((xl, il) => 
              <div className="mvx-google-from-loop">
              <input type="text" className="mvx-vendor-form-input-field" value={xl.placeholder} onChange={e => {this.onlebelchange(e, il, 'placeholder') }} />
                {JSON.stringify(xl)}
              </div>
            )}

























          {/* registration work start */}
            <div id="menu-settings-column" className="metabox-holder">
            <div id="side-sortables" className="meta-box-sortables ui-sortable">
              <div className={`postbox ${this.state.first_toggle}`} >
                <div className="postbox-header">
                <h3 className="hndle ui-sortable-handle"><span>Form Fields</span></h3>
                <button type="button" className="handlediv" aria-expanded="true" onClick={this.togglePostbox}><span className="screen-reader-text">Toggle panel: Format</span><span className="toggle-indicator" aria-hidden="true"></span></button>
              </div>
              <div className="inside">
                <p className="button-controls"><a onClick={(e) => this.handleAddClick(e, 'textbox', 'Text Box')} className="button-secondary">Textbox</a></p>
                <p className="button-controls"><a onClick={(e) => this.handleAddClick(e, 'email', 'Email')} className="button-secondary">Email</a></p>
                <p className="button-controls"><a onClick={(e) => this.handleAddClick(e, 'url', 'Url')} className="button-secondary">Url</a></p>
                <p className="button-controls"><a onClick={(e) => this.handleAddClick(e, 'textarea', 'Text Area')} className="button-secondary">Textarea</a></p>
                <p className="button-controls"><a onClick={(e) => this.handleAddClick(e, 'selectbox', 'Select Box')} className="button-secondary">List</a></p>
                <p className="button-controls"><a onClick={(e) => this.handleAddClick(e, 'checkbox', 'Checkbox')} className="button-secondary">Checkbox</a></p>
                <p className="button-controls"><a onClick={(e) => this.handleAddClick(e, 'recaptcha', 'Recaptcha')} className="button-secondary">Recaptcha</a></p>    
                <p className="button-controls"><a onClick={(e) => this.handleAddClick(e, 'file', 'Attachment')} className="button-secondary">Attachment</a></p> 
                <p className="button-controls"><a onClick={(e) => this.handleAddClick(e, 'separator', 'Section')} className="button-secondary">Section</a></p>
              </div>
            </div>
            </div>
            <div id="side-sortables" class="meta-box-sortables ui-sortable">
            <div class={`postbox ${this.state.second_toggle}`}>
              <div class="postbox-header">
                <h3 class="hndle ui-sortable-handle">
                    <span>Vendor Store Fields</span>
                </h3>
                <button type="button" class="handlediv" aria-expanded="true" onClick={this.togglevendorStoreField}><span class="screen-reader-text">Toggle panel: Format</span><span class="toggle-indicator" aria-hidden="true"></span></button>
              </div>
              <div class="inside">
                <p class="button-controls"><a onClick={(e) => this.handleAddClick(e, 'vendor_description', 'Store Description')} class="button-secondary">Store Description</a></p>
                <p class="button-controls"><a onClick={(e) => this.handleAddClick(e, 'vendor_address_1', 'Address 1')} class="button-secondary">Address 1</a></p>
                <p class="button-controls"><a onClick={(e) => this.handleAddClick(e, 'vendor_address_2', 'Address 2')} class="button-secondary">Address 2</a></p>
                <p class="button-controls"><a onClick={(e) => this.handleAddClick(e, 'vendor_phone', 'Phone')} class="button-secondary">Phone</a></p>
                <p class="button-controls"><a onClick={(e) => this.handleAddClick(e, 'vendor_country', 'Country')} class="button-secondary">Country</a></p>
                <p class="button-controls"><a onClick={(e) => this.handleAddClick(e, 'vendor_state', 'State')} class="button-secondary">State</a></p>
                <p class="button-controls"><a onClick={(e) => this.handleAddClick(e, 'vendor_city', 'City')} class="button-secondary">City</a></p>
                <p class="button-controls"><a onClick={(e) => this.handleAddClick(e, 'vendor_postcode', 'PostCode')} class="button-secondary">Postcode</a></p>
                <p class="button-controls"><a onClick={(e) => this.handleAddClick(e, 'vendor_paypal_email', 'Paypal Email')} class="button-secondary">PayPal Email</a></p>
              </div>
            </div>
            </div>
            </div>
            {/*JSON.stringify(this.state.abcarray)*/}
            <div id="poststuff">
            <div id="post-body">
            <div id="post-body-content">
              <div id="mvx-vendor-form">
              <button className="button-primary menu-save" onClick={this.handleSaveRegistration} disabled={this.state.loading}>
                {this.state.loading && (
                  <i className="dashicons dashicons-update" style={{ marginRight: "5px" }} />
                  )}
                  {this.state.loading && <span>Saving..</span>}
                  {!this.state.loading && <span>Save</span>}
              </button>
              { this.state.abcarray.length === 0 ? <div className="mvx-form-empty-container">Build your form here</div> : '' }
              <ul className="meta-box-sortables">
                <ReactSortable list={this.state.abcarray} setList={(newState) => this.setState({ abcarray: newState })}>    
                  {this.state.abcarray.map((x, i) => 
                    <li>
                      <div className={`postbox ${x.hidden ? 'closed' : ''}`}>
                        <div className="postbox-header">
                          <h3 className="hndle ui-sortable-handle">
                            <span>{x.label}</span>
                          </h3>
                          <button type="button" className="handlediv" onClick={(e) => this.togglePostboxField(e, i)} aria-expanded="true"><span className="screen-reader-text">Toggle panel: Format</span><span className="toggle-indicator" aria-hidden="true"></span></button>
                        </div>
                          <div className="inside">
                              <div id="post-formats-select">
                                <div className="mvx-vendor-form-field-content">
                                  <div className="mvx-vendor-form-input-field-container">
                                    <label>Label</label>
                                    <input type="text" className="mvx-vendor-form-input-field" value={x.label} onChange={e => {this.onlebelchange(e, i, 'label') }} />
                                  </div>

                                  {x.hasOwnProperty('placeholder') ?
                                    <div className="mvx-vendor-form-input-field-container">
                                      <label>Placeholder</label>
                                      <input type="text" className="mvx-vendor-form-input-field" value={x.placeholder} onChange={e => {this.onlebelchange(e, i, 'placeholder') }} />
                                    </div>
                                    : ''
                                  }

                                  {x.hasOwnProperty('limit') ?
                                  <div className="mvx-vendor-form-input-field-container">
                                    <label>Characters Limit</label>
                                    <input type="number" className="mvx-vendor-form-input-field" value={x.limit} onChange={e => {this.onlebelchange(e, i, 'limit') }} />
                                  </div>
                                  : '' }

                                  {x.hasOwnProperty('required') ?
                                  <div className="mvx-vendor-form-input-field-container">
                                    <input type="checkbox" className="mvx-vendor-form-input-field" checked={x.required} onChange={e => {this.onlebelchange(e, i, 'required') }} />
                                    <label> Required</label>
                                  </div>
                                  : '' }

                                  {x.hasOwnProperty('tip_description') ? 
                                  <div className="mvx-vendor-form-input-field-container">
                                    <label>Tooltip description</label>
                                    <input type="text" className="mvx-vendor-form-input-field" value={x.tip_description} onChange={e => {this.onlebelchange(e, i, 'tip_description') }} />
                                  </div>
                                  : '' }

                                  {x.hasOwnProperty('cssClass') ? 
                                  <div className="mvx-vendor-form-input-field-container">
                                    <label>Custom CSS classes</label>
                                    <input type="text" className="mvx-vendor-form-input-field" value={x.cssClass} onChange={e => {this.onlebelchange(e, i, 'cssClass') }} />
                                  </div>
                                  : '' }

                                  {x.type && x.type == 'checkbox' ?
                                  <div className="mvx-vendor-form-input-field-container" value={x.defaultValue}
                                      onChange={e => {this.onlebelchange(e, i, 'defaultValue') }}>
                                    <label>Default Value</label>
                                    <select className="mvx-vendor-form-input-field">
                                      <option value="unchecked">Unchecked</option>
                                      <option value="checked">Checked</option>
                                    </select>
                                  </div>
                                  : '' }

                                  {x.type && x.type == 'file' ?
                                  <div className="mvx-vendor-form-input-field-container">
                                    <input type="checkbox" className="mvx-vendor-form-input-field" checked={x.muliple} onChange={e => {this.onlebelchange(e, i, 'muliple') }} />
                                    <label>Multiple</label>
                                  </div>
                                  : '' }

                                  {x.type && x.type == 'file' ?
                                  <div className="mvx-vendor-form-input-field-container">
                                    <label>File size limit (bytes)</label>
                                    <input type="text" className="mvx-vendor-form-input-field" value={x.fileSize} onChange={e => {this.onlebelchange(e, i, 'fileSize') }} />
                                  </div>
                                  : '' }

                                  {x.type && x.type == 'file' ?
                                  <div className="mvx-vendor-form-input-field-container">
                                    <label>Acceptable file types</label>
                                    {x.fileType.map((xnew, inew) => 
                                        <div>
                                          <input type="checkbox" checked={xnew.selected} onChange={e => {this.onlebelchange(e, i, 'selected', inew) }} />
                                          <label>{xnew.label}</label>
                                        </div>
                                      )}
                                  </div>
                                  : '' }

                                  {x.type && x.type == 'selectbox' ?
                                  <div className="mvx-vendor-form-input-field-container">
                                    <label>List Type</label>
                                    <select className="mvx-vendor-form-input-field" value={x.selecttype}
                                        onChange={e => {this.onlebelchange(e, i, 'selecttype') }} >
                                      <option value="dropdown">Dropdown</option>
                                      <option value="radio">Radio</option>
                                      <option value="checkboxes">Checkboxes</option>
                                      <option value="multi-select">Multi select</option>
                                    </select>
                                  </div>
                                  : '' }

                                  {x.type && x.type == 'selectbox' ?
                                  <div className="mvx-vendor-form-input-field-container">
                                    <label>Options</label>
                                    <a className="button-secondary" onClick={(e) => this.addSelectBoxOption(e, i)}>Add New</a>
                                    <ul className="field-selectbox-options">
                                    {x.options.map((key, option) =>
                                      <li>
                                        <a onClick={(e) => this.removeSelectboxOption(e, i, option)}><span className="dashicons dashicons-dismiss"></span></a>
                                        Label
                                        <input type="text" value={key.label} onChange={e => {this.onlebelchange(e, i, 'select_option', option) }}/>
                                        Value 
                                        <input type="text" value={key.value} onChange={e => {this.onlebelchange(e, i, 'select_option1', option) }} />
                                        Selected
                                        {x.type && x.selecttype === 'radio' || x.selecttype === 'dropdown' ?
                                        <input type="radio" value="1"  name={`option-${x.id}`} checked={key.selected} onChange={e => {this.onlebelchange(e, i, 'selected_radio_box', option) }} />
                                        :
                                        <input type="checkbox" value="true" checked={key.selected} onChange={e => {this.onlebelchange(e, i, 'selected_box', option) }} />
                                        }
                                      </li>
                                    )}
                                    </ul>
                                  </div>
                                  : '' }

                                  {x.type && x.type == 'recaptcha' ?
                                    <div className="mvx-vendor-form-input-field-container" value={x.recaptchatype}
                                        onChange={e => {this.onlebelchange(e, i, 'recaptchatype') }}>
                                      <label>reCAPTCHA Type</label>
                                      <select className="mvx-vendor-form-input-field">
                                        <option value="v3">reCAPTCHA v3</option>
                                        <option value="v2">reCAPTCHA v2</option>
                                      </select>
                                    </div>
                                  : '' }

                                  {x.type && x.type == 'recaptcha' && x.recaptchatype === 'v3' ?
                                    <div class="mvx-vendor-form-input-field-container">
                                      <label>Site key</label>
                                      <input type="text" class="mvx-vendor-form-input-field" value={x.sitekey} onChange={e => {this.onlebelchange(e, i, 'sitekey') }} />
                                    </div>
                                  : '' }

                                  {x.type && x.type == 'recaptcha' && x.recaptchatype === 'v3' ?
                                    <div class="mvx-vendor-form-input-field-container">
                                      <label>Secret key</label>
                                      <input type="text" ng-model="field.secretkey" class="mvx-vendor-form-input-field" value={x.secretkey} onChange={e => {this.onlebelchange(e, i, 'secretkey') }} />
                                    </div>
                                  : '' }

                                  {x.type && x.type == 'recaptcha' && x.recaptchatype === 'v2' ?
                                    <div class="mvx-vendor-form-input-field-container">
                                      <label>Recaptcha Script</label>
                                      <textarea cols="20" rows="3" class="mvx-vendor-form-input-field" value={x.script} onChange={e => {this.onlebelchange(e, i, 'script') }}></textarea>
                                    </div>
                                  : '' }

                                  {x.type && x.type == 'recaptcha' ?
                                    <div class="mvx-vendor-form-input-field-container">
                                      <p>To get <b>reCAPTCHA</b> script, register your site with google account <a href="https://www.google.com/recaptcha" target="_blank">Register</a></p>
                                    </div>
                                  : '' }

                                </div>
                              </div>
                            <div className="mvx-vendor-form-input-field-container">
                              <a onClick={(e) => this.removeFormField(e, i)} className="mvx-remove-form-field">Remove</a>
                            </div>
                          </div>
                        </div>
                      </li>
                    )}
                </ReactSortable>     
              </ul>
              <button className="button-primary menu-save" onClick={this.handleSaveRegistration} disabled={this.state.loading}>
                {this.state.loading && (
                  <i
                  className="dashicons dashicons-update"
                  style={{ marginRight: "5px" }}
                  />
                  )}
                  {this.state.loading && <span>Saving..</span>}
                  {!this.state.loading && <span>Save</span>}
              </button>
              </div>
            </div>
            </div>
            </div>
            {/* registration work end */}
            </div>
            :
          
            <div>
              <DynamicForm
              key={`dynamic-form-${data.tabname}`}
              className={data.classname}
              title={data.tablabel}
              defaultValues={this.state.current}
              model= {appLocalizer.settings_fields[data.modelname]}
              method="post"
              modelname={data.modelname}
              url={data.apiurl}
              submitbutton="false"
              />
            </div>
            
        : ''

      }
      </div>
    ))}
    </div>
  );
}


  onEdit = id => {
    let record = this.state.data.find(d => {
      return d.id == id;
    });
    //alert(JSON.stringify(record));
    this.setState({
      current: record
    });
  };

  onNewClick = e => {
    this.setState({
      current: {}
    });
  };


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