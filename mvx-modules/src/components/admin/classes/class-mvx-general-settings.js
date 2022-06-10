import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import { css } from "@emotion/react";
import PuffLoader from "react-spinners/PuffLoader";
import { ReactSortable } from "react-sortablejs";
import {
  BrowserRouter as Router,
  useLocation,
} from "react-router-dom";
import DynamicForm from "../../../DynamicForm";
const override = css`
  display: block;
  margin: 0 auto;
  border-color: red;
`;
import TabSection from './class-mvx-page-tab';

class MVX_Settings extends Component {
  constructor(props) {
    super(props);
    this.state = {
      mvx_registration_fileds_list: [],
      current: {},
      list_of_module_data: [],
      set_tab_name: '',
      list_of_all_tabs: []
    };

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);
    this.useQuery = this.useQuery.bind(this);
    this.Child = this.Child.bind(this);
    // add new click
    this.handleAddClickNew = this.handleAddClickNew.bind(this);
    // remove click
    this.handleRemoveClickNew = this.handleRemoveClickNew.bind(this);
    // for active from content
    this.handleActiveClick = this.handleActiveClick.bind(this);
    // select from dropdown
    this.OnRegistrationSelectChange = this.OnRegistrationSelectChange.bind(this);
    this.onlebelchange = this.onlebelchange.bind(this);
    this.addSelectBoxOption = this.addSelectBoxOption.bind(this);
    this.removeSelectboxOption = this.removeSelectboxOption.bind(this);
    // new registration save function
    this.handleSaveNewRegistration = this.handleSaveNewRegistration.bind(this);
    // duplicate content
    this.OnDuplicateSelectChange = this.OnDuplicateSelectChange.bind(this);
    // sortable change
    this.handleResortClick = this.handleResortClick.bind(this);
  }

  // save new registration form
  handleSaveNewRegistration(e) {
    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/save_registration`,
      data: {
        form_data: JSON.stringify(this.state.mvx_registration_fileds_list),
      }
    })
    .then((responce) => {});
  }

  addSelectBoxOption(e, index) {
    var count = this.state.mvx_registration_fileds_list[index].options.length + 1;
    this.state.mvx_registration_fileds_list[index].options.push({ value: 'option' + count, label: 'Option ' + count, selected: false });
    this.setState({
      mvx_registration_fileds_list: this.state.mvx_registration_fileds_list
    });
  }

  removeSelectboxOption(e, index, key) {
    this.state.mvx_registration_fileds_list[index].options.splice(key, 1);
    this.setState({
      mvx_registration_fileds_list: this.state.mvx_registration_fileds_list
    });
  }

  onlebelchange(e, index, label, childindex) {
    var save_value;
    if (label == 'required' || label == 'muliple' || label == 'selected') {
      save_value = e.target.checked;
    } else {
      save_value = e.target.value;
    }
    let items = this.state.mvx_registration_fileds_list;

    if (label == 'select_option') {
      items[index]['options'][childindex]['label'] = save_value;
    } else if (label == 'selected_radio_box') {
      items[index]['options'][childindex]['selected'] = save_value;
      items[index]['options'].map((number, indexs) => {
        if (childindex !== indexs) {
          items[index]['options'][indexs]['selected'] = false;
        }
      });
    } else if (label == 'selected_box') {
      items[index]['options'][childindex]['selected'] = e.target.checked;
    } else if (label == 'select_option1') {
      items[index]['options'][childindex]['value'] = save_value;
    } if (label == 'selected') {
      items[index]['fileType'][childindex][label] = save_value;
    } else {
      items[index][label] = save_value;
    }
    this.setState({
      items,
    });

    setTimeout(() => {
      this.handleSaveNewRegistration('');
    }, 10)
  }

  // new registration settings
  handleAddClickNew(e, type) {
    var formJson = this.state.mvx_registration_fileds_list;
    var jsonLength = formJson.length;

    formJson.push({
      id: jsonLength,
      type: 'textbox',
      label: '',
      hidden: false,
      placeholder: '',
      required: false,
      cssClass: '',
      tip_description: '',
      options: [],
      fileSize: '',
      fileType: [],
      muliple: false,
      recaptchatype: 'v3',
      sitekey: '',
      secretkey: '',
      script: ''
    });

    this.setState({
      mvx_registration_fileds_list: formJson
    });

    setTimeout(() => {
      this.handleSaveNewRegistration('');
    }, 10)

  }

  // duplicate icon click
  OnDuplicateSelectChange(e, index, duplicate) {
    var formJson = this.state.mvx_registration_fileds_list;
    var jsonLength = formJson.length;
    formJson.push(formJson[index]);
  }

  // sotring funtion
  handleResortClick(sort) {
    this.setState({
      mvx_registration_fileds_list: sort
    });
    setTimeout(() => {
      this.handleSaveNewRegistration('');
    }, 10)
  }
  // remove fileds
  handleRemoveClickNew(e, index) {
    this.state.mvx_registration_fileds_list.splice(index, 1);
    this.setState({
      mvx_registration_fileds_list: this.state.mvx_registration_fileds_list
    });
    setTimeout(() => {
      this.handleSaveNewRegistration('');
    }, 10)
  }
  // active metabox hide or show
  handleActiveClick(e, index, label) {
    let new_items = this.state.mvx_registration_fileds_list;
    if (label == 'parent') {
      new_items[0].hidden = true;
      new_items.map((data_active, index_active) => {
        if (index == 0) { } else {
          new_items[index_active].hidden = false;
        }
      })
    } else if (label == 'sub') {
      new_items.map((data_active, index_active) => {
        if (index == 0) { } else {
          if (index_active == index) {
            new_items[index].hidden = true;
          } else {
            new_items[index_active].hidden = false;
          }
        }
      })
    }
    this.setState({
      new_items
    });
    setTimeout(() => {
      this.handleSaveNewRegistration('');
    }, 10)
  }
  // select button trigger
  OnRegistrationSelectChange(e, index, types) {
    let new_items = this.state.mvx_registration_fileds_list;
    if (types == 'select_drop') {
      new_items[index]['type'] = e.target.value;
      if (new_items[index].options.length == 0) {
        if (e.target.value == 'checkboxes' || e.target.value == 'multi-select' || e.target.value == 'radio' || e.target.value == 'dropdown') {
          var count = new_items[index].options.length + 1;
          new_items[index].options.push({ value: 'option' + count, label: 'Option ' + count, selected: false });
        } else if (e.target.value == 'attachment') {
          new_items[index].fileType.push(
            {
              value: 'application/pdf',
              label: 'PDF',
              selected: false
            },
            {
              value: 'image/jpeg',
              label: 'JPEG',
              selected: false
            },
            {
              value: 'image/png',
              label: 'PNG',
              selected: false
            },
            {
              value: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
              label: 'DOC',
              selected: false
            },
            {
              value: 'application/vnd.ms-excel',
              label: 'xls',
              selected: false
            }
          );
        }
      }
    } else if (types == 'label') {
      new_items[index]['label'] = e.target.value;
    } else if (types == 'parent_label') {
      new_items[0]['label'] = e.target.value;
    } else if (types == 'parent_description') {
      new_items[0]['description'] = e.target.value;
    } else if (types == 'require') {
      new_items[index]['required'] = e.target.checked;
    }
    this.setState({
      new_items,
    });
    setTimeout(() => {
      this.handleSaveNewRegistration('');
    }, 10)
  }

  componentDidMount() {
    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/get_registration`
    })
      .then(response => {
        var formJson4 = this.state.mvx_registration_fileds_list;
        if (response.data.length > 0) {
          formJson4 = response.data;
        } else {
          formJson4.push({
            id: 'parent_title',
            type: 'p_title',
            label: '',
            hidden: false,
            label_placeholder: '',
            description: '',
            description_placeholder: '',
          });

          formJson4.push({
            id: formJson4.length,
            type: 'textbox',
            label: '',
            hidden: false,
            placeholder: '',
            required: false,
            cssClass: '',
            tip_description: '',
            options: [],
            fileSize: '',
            fileType: [],
            muliple: false,
            recaptchatype: 'v3',
            sitekey: '',
            secretkey: '',
            script: ''
          });
        }
        this.setState({
          mvx_registration_fileds_list: formJson4
        });
      })

    // list of all tabs
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
          model={this.state.list_of_all_tabs['marketplace-general-settings']}
          query_name={use_query.get("name")}
          funtion_name={this}
        />
      : <PuffLoader css={override} color={"#cd0000"} size={200} loading={true} />
    );
  }

  Child({ name }) {
    if (name != this.state.set_tab_name) {
      axios({
        url: `${appLocalizer.apiUrl}/mvx_module/v1/fetch_all_modules_data`
      })
      .then(response => {
        this.setState({
          list_of_module_data: response.data,
          set_tab_name: name
        });
      });
    }

    return (
      appLocalizer.mvx_all_backend_tab_list['marketplace-general-settings'].map((data, index) => (
        data.modulename == name ?
          data.modulename == 'registration' ?
              <div className="mvx-form-vendor-register">
                {this.state.mvx_registration_fileds_list.length > 0 ?
                  <div className={`mvx-top-part-registartion-from ${this.state.mvx_registration_fileds_list && this.state.mvx_registration_fileds_list.length > 0 && this.state.mvx_registration_fileds_list[0].hidden ? 'mvx-form-left-line-active' : ''}`} onClick={(e) => this.handleActiveClick(e, '', 'parent')}>
                    <div className="mvx-registration-content">
                      <div className='mvx-form-registration-group'>
                        <div className="mvx-untitle-content">
                          <input className='mvx-registration-default-input' type="text" placeholder="Registration form title" value={this.state.mvx_registration_fileds_list[0].label} onChange={e => { this.OnRegistrationSelectChange(e, '', 'parent_label') }} />
                          <div className="mvx-registration-fileds-description">Type the form title you want the vendor to see. eg registrazione del venditore</div>
                        </div>
                        <div className="mvx-registration-from-description">
                          <input className='mvx-registration-default-contetnt' type="text" placeholder="Registration form description" value={this.state.mvx_registration_fileds_list[0].description} onChange={e => { this.OnRegistrationSelectChange(e, '', 'parent_description') }} />
                          <div className="mvx-registration-fileds-description">Add guidelines or valuable information applicable for registration.</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  : ''}

                <ul className="meta-box-sortables">
                  <ReactSortable list={this.state.mvx_registration_fileds_list} setList={(newState) => this.handleResortClick(newState)}>
                    {this.state.mvx_registration_fileds_list.map((registration_json_value, registration_json_index) => (
                      <li>
                        {
                          registration_json_value.id == 'parent_title' ? '' :
                            <div className={`mvx-option-part ${registration_json_value.hidden ? 'mvx-form-left-line-active' : ''}`} onClick={(e) => this.handleActiveClick(e, registration_json_index, 'sub')}>
                              <div className="mvx-registration-content">
                                <div className='mvx-form-group-loop'>
                                  <div className="mvx-question-input mvx-form-group">

                                    <div className="mvx-question-input-items">
                                      <input type="text" className='default-input' placeholder="Question title" value={registration_json_value.label} onChange={e => { this.OnRegistrationSelectChange(e, registration_json_index, 'label') }} />
                                      {registration_json_value.hidden ? <div className="mvx-registration-fileds-description">Write questions applicable to your marketplace.</div> : ''}
                                    </div>

                                    {registration_json_value.hidden ?
                                      <div className="mvx-question-input-items">
                                        <select className="mvx-registration-select-choice default-select" value={registration_json_value.type}
                                          onChange={e => { this.OnRegistrationSelectChange(e, registration_json_index, 'select_drop') }}>
                                          <option value="select_question_type">Select question type</option>
                                          <option value="textbox" data-icon="glyphicon-music">Textbox</option>
                                          <option value="email">Email</option>
                                          <option value="url">Url</option>
                                          <option value="textarea">Textarea</option>
                                          <option value="checkboxes">Checkboxes</option>
                                          <option value="multi-select">Multi select</option>
                                          <option value="radio">Radio</option>
                                          <option value="dropdown">Dropdown</option>
                                          <option value="recapta">Recapta</option>
                                          <option value="attachment">Attachment</option>
                                          <option value="section">Section</option>
                                          <option value="vendor_description">Store Description</option>
                                          <option value="vendor_address_1">Address 1</option>
                                          <option value="vendor_address_2">Address 2</option>
                                          <option value="vendor_phone">Phone</option>
                                          <option value="vendor_country">Country</option>
                                          <option value="vendor_state">State</option>
                                          <option value="vendor_city">City</option>
                                          <option value="vendor_postcode">Postcode</option>
                                          <option value="vendor_paypal_email">PayPal Email</option>
                                        </select>
                                        <div className="mvx-registration-fileds-description">Select your preferred question format. Read doc to know more about each format.</div>
                                      </div>
                                      : ''}
                                  </div>
                                </div>
                                {registration_json_value.hidden ?
                                  <div className="next_option_part">
                                    {
                                      registration_json_value.type == 'textbox' || registration_json_value.type == 'email' || registration_json_value.type == 'url' || registration_json_value.type == 'textarea' || registration_json_value.type == 'vendor_description' || registration_json_value.type == 'vendor_address_1' || registration_json_value.type == 'vendor_address_2' || registration_json_value.type == 'vendor_phone' || registration_json_value.type == 'vendor_country' || registration_json_value.type == 'vendor_state' || registration_json_value.type == 'vendor_city' || registration_json_value.type == 'vendor_postcode' || registration_json_value.type == 'vendor_paypal_email' ?
                                        <div className="mvx-basic-description">
                                          <div className="mvx-vendor-form-input-field-container">
                                            <input type="text" className="mvx-vendor-form-input-field" placeholder="Placeholder" value={registration_json_value.placeholder} onChange={e => { this.onlebelchange(e, registration_json_index, 'placeholder') }} />
                                            <div className="mvx-registration-fileds-description">Leave this section blank or add examples of an answer here.</div>
                                          </div>

                                          <div className="mvx-vendor-form-input-field-container">
                                            <input type="text" className="mvx-vendor-form-input-field" placeholder="Tooltip description" value={registration_json_value.tip_description} onChange={e => { this.onlebelchange(e, registration_json_index, 'tip_description') }} />
                                            <div className="mvx-registration-fileds-description">Add more information or specific instructions here.</div>
                                          </div>

                                        </div>
                                      : ''
                                    }

                                    {
                                      registration_json_value.type == 'textarea' ?
                                        <div className="mvx-vendor-form-input-field-container">
                                          <div className='mvx-registration-texarea'>
                                            <label>Characters Limit</label>
                                            <input type="number" className="mvx-vendor-form-input-field" value={registration_json_value.limit} onChange={e => { this.onlebelchange(e, registration_json_index, 'limit') }} />
                                            <div className="mvx-registration-fileds-description">Restrict vendor descriptions to a certain number of characters.</div>
                                          </div>
                                        </div>
                                      : ''
                                    }

                                    {
                                      registration_json_value.type == 'attachment' ?
                                        <div className='row'>
                                          <div className="mvx-vendor-form-input-field-container col-50">
                                            <label>File Type</label>
                                            <input type="checkbox" className="mvx-vendor-form-input-field" checked={registration_json_value.muliple} onChange={e => { this.onlebelchange(e, registration_json_index, 'muliple') }} />
                                            <label className='auto-width'>Multiple</label>
                                          </div>

                                          <div className="mvx-vendor-form-input-field-container col-50">
                                            <input type="text" placeholder="Maximum file size" className="mvx-vendor-form-input-field" value={registration_json_value.fileSize} onChange={e => { this.onlebelchange(e, registration_json_index, 'fileSize') }} />
                                            <div className="mvx-registration-fileds-description">Add limitation for file size</div>
                                          </div>

                                          <div className="mvx-vendor-form-input-field-container col-50">
                                            <label className='auto-width'>Acceptable file types</label>
                                            {registration_json_value.fileType.map((xnew, inew) =>
                                              <div>
                                                <input type="checkbox" checked={xnew.selected} onChange={e => { this.onlebelchange(e, registration_json_index, 'selected', inew) }} />
                                                <label>{xnew.label}</label>
                                              </div>
                                            )}
                                            <div className="mvx-registration-fileds-description">Choose preferred file size.</div>
                                          </div>
                                          
                                        </div>
                                      : ''
                                    }

                                    {
                                      registration_json_value.type == 'recapta' ?
                                        <div className='row'>
                                          <div className="mvx-vendor-form-input-field-container col-100" value={registration_json_value.recaptchatype}
                                            onChange={e => { this.onlebelchange(e, registration_json_index, 'recaptchatype') }}>
                                            <div className='mvx-registration-recapta-option'>
                                              <label className='mvx-form-title'>reCAPTCHA Type</label>
                                              <select className="mvx-vendor-form-input-field default-select">
                                                <option value="v3">reCAPTCHA v3</option>
                                                <option value="v2">reCAPTCHA v2</option>
                                              </select>
                                            </div>
                                          </div>

                                          {registration_json_value.recaptchatype === 'v3' ?
                                            <div className="mvx-vendor-form-input-field-container col-50">
                                              <label className='mvx-form-title'>Site key</label>
                                              <input type="text" className="mvx-vendor-form-input-field" value={registration_json_value.sitekey} onChange={e => { this.onlebelchange(e, registration_json_index, 'sitekey') }} />
                                            </div>
                                          : ''}

                                          {registration_json_value.recaptchatype === 'v3' ?
                                            <div className="mvx-vendor-form-input-field-container col-50">
                                              <label className='mvx-form-title'>Secret key</label>
                                              <input type="text" ng-model="field.secretkey" className="mvx-vendor-form-input-field" value={registration_json_value.secretkey} onChange={e => { this.onlebelchange(e, registration_json_index, 'secretkey') }} />
                                            </div>
                                          : ''}

                                          {registration_json_value.recaptchatype === 'v2' ?
                                            <div className="mvx-vendor-form-input-field-container col-100">
                                              <label className='mvx-form-title'>Recaptcha Script</label>
                                              <textarea cols="20" rows="3" className="mvx-vendor-form-input-field default-textarea" value={registration_json_value.script} onChange={e => { this.onlebelchange(e, registration_json_index, 'script') }}></textarea>
                                            </div>
                                          : ''}

                                          <div className="mvx-vendor-form-input-field-container col-100">
                                            <p>To get <b>reCAPTCHA</b> script, register your site with google account <a href="https://www.google.com/recaptcha" target="_blank">Register</a></p>
                                          </div>

                                          <div className="mvx-registration-fileds-description">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</div>
                                        </div>
                                      : ''
                                    }

                                    {registration_json_value.type == 'checkboxes' || registration_json_value.type == 'multi-select' || registration_json_value.type == 'radio' || registration_json_value.type == 'dropdown' ?
                                      <div className="mvx-vendor-form-input-field-container">
                                        <ul className="field-selectbox-options mb-24">
                                          {registration_json_value.options.map((chekbox_option_key, checkbox_option_index) =>
                                            <li>
                                              <div className='f-row-8'>
                                                <div className='mt-35'>
                                                  {registration_json_value.type === 'radio' || registration_json_value.type === 'dropdown' ?
                                                    <input type="radio" value="1" name={`option-${registration_json_value.id}`} checked={chekbox_option_key.selected} onChange={e => { this.onlebelchange(e, registration_json_index, 'selected_radio_box', option) }} />
                                                    :
                                                    <input type="checkbox" value="true" checked={chekbox_option_key.selected} onChange={e => { this.onlebelchange(e, registration_json_index, 'selected_box', checkbox_option_index) }} />
                                                  }
                                                </div>
                                              </div>
                                              <div>
                                                <label className="form-title">Label</label>
                                                <input type="text" value={chekbox_option_key.label} onChange={e => { this.onlebelchange(e, registration_json_index, 'select_option', checkbox_option_index) }} />
                                                <div className="mvx-registration-fileds-description">Write titles for your options here.</div>
                                              </div>
                                              <div>
                                                <label className="form-title">Value</label>
                                                <input type="text" value={chekbox_option_key.value} onChange={e => { this.onlebelchange(e, registration_json_index, 'select_option1', checkbox_option_index) }} />
                                                <div className="mvx-registration-fileds-description">This section is available for developers who might want to mark the labels they create.</div>
                                              </div>
                                              <div className='f-row-8'>
                                                <a className='del-btn mt-35' onClick={(e) => this.removeSelectboxOption(e, registration_json_index, checkbox_option_index)}><i className="mvx-font icon-close"></i></a>
                                              </div>
                                            </li>
                                          )}
                                        </ul>
                                        <a className="btn purple-btn" onClick={(e) => this.addSelectBoxOption(e, registration_json_index)}>Add New</a>
                                      </div>
                                    : ''}
                                  </div>
                                : ''}

                                {registration_json_value.hidden ?
                                  <div className="mvx-footer-icon-form">
                                    <i className="mvx-font icon-vendor-form-copy" onClick={e => { this.OnDuplicateSelectChange(e, registration_json_index, 'duplicate') }}></i>
                                    {this.state.mvx_registration_fileds_list.length > 2 ? <i className="mvx-font icon-vendor-form-delete" onClick={(e) => this.handleRemoveClickNew(e, registration_json_index)}></i> : '' }
                                    <i className="mvx-font icon-vendor-form-add" onClick={(e) => this.handleAddClickNew(e, registration_json_value.type)}></i>
                                    <span className='mvx-perple-txt'>Require <input type="checkbox" checked={registration_json_value.required} onChange={e => { this.OnRegistrationSelectChange(e, registration_json_index, 'require') }} /></span>
                                  </div>
                                : ''}
                              </div>
                            </div>
                            }
                          </li>
                        ))}
                      </ReactSortable>
                    </ul>
                  </div>
                  :
                  Object.keys(this.state.list_of_module_data).length > 0 ?
                    <DynamicForm
                      key={`dynamic-form-${data.modulename}`}
                      className={data.classname}
                      title={data.tablabel}
                      defaultValues={this.state.current}
                      model={this.state.list_of_module_data[data.modulename]}
                      method="post"
                      modulename={data.modulename}
                      url={data.apiurl}
                      submitbutton="false"
                    />
                    : 
                  <PuffLoader css={override} color={"#cd0000"} size={200} loading={true} />
                : ''
        ))
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
export default MVX_Settings;