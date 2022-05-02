import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import RingLoader from "react-spinners/RingLoader";
import { css } from "@emotion/react";
import PuffLoader from "react-spinners/PuffLoader";
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

import HeaderSection from './class-mvx-page-header';


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
      mvx_registration_fileds_list: [],
      current: {},
      registration_title_hidden: false,
      list_of_module_data: [],
      set_tab_name: ''
    };

    this.query = null;
    // when click on checkbox
    this.handleSubmitl = this.handleSubmitl.bind(this);

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);

    this.Child = this.Child.bind(this);

    this.handleAddClick = this.handleAddClick.bind(this);

    // add new click
    this.handleAddClickNew = this.handleAddClickNew.bind(this);
    // remove click
    this.handleRemoveClickNew = this.handleRemoveClickNew.bind(this);
    // for active from content
    this.handleActiveClick = this.handleActiveClick.bind(this);
    // select from dropdown
    this.OnRegistrationSelectChange = this.OnRegistrationSelectChange.bind(this);
    
    
    this.onlebelchange = this.onlebelchange.bind(this);

    this.removeFormField = this.removeFormField.bind(this);
    
    this.addSelectBoxOption = this.addSelectBoxOption.bind(this);

    this.removeSelectboxOption = this.removeSelectboxOption.bind(this);

    this.handleSaveRegistration = this.handleSaveRegistration.bind(this);

    this.togglePostboxField = this.togglePostboxField.bind(this);

    this.togglePostbox = this.togglePostbox.bind(this);

    this.togglevendorStoreField = this.togglevendorStoreField.bind(this);

    // new registration save function
    this.handleSaveNewRegistration = this.handleSaveNewRegistration.bind(this);

    // duplicate content
    this.OnDuplicateSelectChange = this.OnDuplicateSelectChange.bind(this);
    
    // sortable change
    this.handleResortClick = this.handleResortClick.bind(this);

    
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


  // save new registration form
  handleSaveNewRegistration(e) {
    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/save_registration`,
      data: {
        form_data: JSON.stringify(this.state.mvx_registration_fileds_list),
      }
    })
    .then( ( res ) => {
      console.log('success');
    } );
  }


  addSelectBoxOption(e, index) {
    /*var count = this.state.abcarray[index].options.length + 1;
    this.state.abcarray[index].options.push({value: 'option' + count, label: 'Option ' + count, selected: false});
    this.setState({
      abcarray: this.state.abcarray
    });*/


    var count = this.state.mvx_registration_fileds_list[index].options.length + 1;
    this.state.mvx_registration_fileds_list[index].options.push({value: 'option' + count, label: 'Option ' + count, selected: false});
    this.setState({
      mvx_registration_fileds_list: this.state.mvx_registration_fileds_list
    });
  }

  removeSelectboxOption(e, index, key) {
    /*this.state.abcarray[index].options.splice(key, 1);
    this.setState({
      abcarray: this.state.abcarray
    });*/

    this.state.mvx_registration_fileds_list[index].options.splice(key, 1);
    this.setState({
      mvx_registration_fileds_list: this.state.mvx_registration_fileds_list
    });
  }

  removeFormField(e, index) {
    this.state.abcarray.splice(index, 1);
    this.setState({
      abcarray: this.state.abcarray
    });
  }

  onlebelchange(e, index, label, childindex) {
    
    /*var save_value;
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
    });*/






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
        if(childindex !== indexs) {
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

  // duplicate
  OnDuplicateSelectChange(e, index, duplicate) {
    var formJson = this.state.mvx_registration_fileds_list;
    var jsonLength = formJson.length;
    formJson.push(formJson[index]);
  }

  // sore
  handleResortClick(sort) {

    this.setState({
      mvx_registration_fileds_list: sort
    });

    setTimeout(() => {
      this.handleSaveNewRegistration('');
    }, 10)
  }

  handleRemoveClickNew(e , index) {
    this.state.mvx_registration_fileds_list.splice(index, 1);
    this.setState({
      mvx_registration_fileds_list: this.state.mvx_registration_fileds_list
    });

    setTimeout(() => {
      this.handleSaveNewRegistration('');
    }, 10)
  }

  handleActiveClick(e, index, label) {

    let new_items = this.state.mvx_registration_fileds_list;
    if (label == 'parent') {
      new_items[0].hidden = true;


      new_items.map((data_active, index_active) => {
          if (index == 0) {} else {
            new_items[index_active].hidden = false;
          }
        }
      )

    } else if (label == 'sub') {
      new_items.map((data_active, index_active) => {
          if (index == 0) {} else {
            if (index_active == index) {
              new_items[index].hidden = true;
            } else {
              new_items[index_active].hidden = false;
            }
          }
        }
      )
    } else if (label == 'sortable') {}

    //registration_title_hidden


    this.setState({
      new_items
    });


    setTimeout(() => {
      this.handleSaveNewRegistration('');
    }, 10)
  }

  OnRegistrationSelectChange(e, index, types) {
    let new_items = this.state.mvx_registration_fileds_list;

    if (types == 'select_drop') {
      new_items[index]['type'] = e.target.value;

      if (new_items[index].options.length == 0) {
        if (e.target.value == 'checkboxes' || e.target.value == 'multi-select' || e.target.value == 'radio' || e.target.value == 'dropdown') {
          var count = new_items[index].options.length + 1;
          new_items[index].options.push({value: 'option' + count, label: 'Option ' + count, selected: false});
        } else if (e.target.value == 'attachment') {

          new_items[index].fileType.push( 
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
    //console.log(e.target.value);
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
    //console.log(formJson);
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
    
    /*axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/get_registration`
    })
    .then(response => {
      this.setState({
        abcarray: response.data ? response.data : []
      });
    })*/

    


    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/get_registration`
    })
    .then(response => {

    var formJson4 = this.state.mvx_registration_fileds_list;

    if (response.data.length > 0) {
      formJson4 = response.data;
    } else {

      // new registration from
      
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


      /*this.setState({
        mvx_registration_fileds_list: response.data ? response.data : []
      });*/
    })




      

  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {
    let queryt = this.useQuery();




    

    if(!queryt.get("name")) {
      //window.location.href = window.location.href+'&name=settings-general';
    }

    var tab_name_display = '';
    var tab_description_display = '';
    appLocalizer.mvx_all_backend_tab_list['marketplace-general-settings'].map((data, index) => {
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
          <div className="general-tab-header-area">
            <div className="mvx-tab-name-display">{tab_name_display}</div>
            <p>{tab_description_display}</p>
          </div>

          <div className="general-tab-area">
            <ul className="mvx-general-tabs-list">
            {appLocalizer.mvx_all_backend_tab_list['marketplace-general-settings'].map((data, index) => (
                <Link to={`?page=mvx#&submenu=settings&name=${data.modulename}`} className={queryt.get("name") == data.modulename ? data.activeclass : ''}><li className={queryt.get("name") == data.modulename ? 'activegeneraltabs' : ''} >{data.icon ? <i class={`mvx-font ${data.icon}`}></i> : ''}{data.tablabel}</li></Link>
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
    <div>
    {appLocalizer.mvx_all_backend_tab_list['marketplace-general-settings'].map((data, index) => (
      <div>

      {
        /*name = !name ? 'registration' : name,*/

        data.modulename == name ?
          data.modulename == 'registration' ?

            <div className="mvx-form-vendor-register">


            { this.state.mvx_registration_fileds_list.length > 0 ?
              <div className={`mvx-top-part-from ${this.state.mvx_registration_fileds_list && this.state.mvx_registration_fileds_list.length > 0 && this.state.mvx_registration_fileds_list[0].hidden ? 'mvx-form-left-line-active' : ''}`} onClick={(e) => this.handleActiveClick(e, '', 'parent')}>
                  <div className="content">
                    <div className='form-group'>
                      <div className="mvx-untitle-content w-50">
                        <label className='form-title w-100'>Form Title</label>
                        <input className='default-input' type="text" placeholder="Untitled form" value={this.state.mvx_registration_fileds_list[0].label} onChange={e => {this.OnRegistrationSelectChange(e, '', 'parent_label') }}/>
                      </div>
                      <div className="mvx-from-description w-50">
                        <label className='form-title w-100'>Form Description</label>
                        <input className='default-input' type="text" placeholder="From Description" value={this.state.mvx_registration_fileds_list[0].description} onChange={e => {this.OnRegistrationSelectChange(e, '', 'parent_description') }} />
                      </div>
                    </div>
                  </div>
              </div>
              : '' }


            <ul className="meta-box-sortables">
            <ReactSortable list={this.state.mvx_registration_fileds_list} setList={(newState) => this.handleResortClick(newState)}>

            {this.state.mvx_registration_fileds_list.map((registration_json_value, registration_json_index) => (
              <li>

              {
              registration_json_value.id == 'parent_title' ? '' :

              <div className= {`mvx-option-part ${registration_json_value.hidden ? 'mvx-form-left-line-active' : ''}`} onClick={(e) => this.handleActiveClick(e, registration_json_index, 'sub')}>
                <div className="content">
                  <div className='form-group-loop'>
                    <div className="question-input form-group">
                      
                        <div className="question-input-items first-question w-50">
                        <label className='form-title w-100'>Question</label>
                          <input type="text" className='default-input' placeholder="Untitled Question" value={registration_json_value.label} onChange={e => {this.OnRegistrationSelectChange(e, registration_json_index, 'label') }}/>
                        </div>
                          
                         {registration_json_value.hidden ? 
                          <div className="question-input-items w-50">
                            <label className='form-title w-100'>Question Type</label>
                            <select className="mvx-registration-select-choice default-select" value={registration_json_value.type}
                                        onChange={e => {this.OnRegistrationSelectChange(e, registration_json_index, 'select_drop') }}>
                              <option value="textbox">Textbox</option>
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
                        </div>
                        : '' }

                    </div>
                  </div>

                    {registration_json_value.hidden ? 
                    <div className="next_option_part">

                      {
                        registration_json_value.type == 'textbox' || registration_json_value.type == 'email' || registration_json_value.type == 'url' || registration_json_value.type == 'textarea' || registration_json_value.type == 'vendor_description' || registration_json_value.type == 'vendor_address_1' || registration_json_value.type == 'vendor_address_2' || registration_json_value.type == 'vendor_phone' || registration_json_value.type == 'vendor_country' || registration_json_value.type == 'vendor_state' || registration_json_value.type == 'vendor_city' || registration_json_value.type == 'vendor_postcode' || registration_json_value.type == 'vendor_paypal_email' ?
                          <div className="mvx-basic-description">
                            <div className="mvx-vendor-form-input-field-container">
                              <label>Placeholder</label>
                              <input type="text" className="mvx-vendor-form-input-field" value={registration_json_value.placeholder} onChange={e => {this.onlebelchange(e, registration_json_index, 'placeholder') }} />
                            </div>

                            <div className="mvx-vendor-form-input-field-container">
                              <label>Tooltip description</label>
                              <input type="text" className="mvx-vendor-form-input-field" value={registration_json_value.tip_description} onChange={e => {this.onlebelchange(e, registration_json_index, 'tip_description') }} />
                            </div>
                          </div>
                        : ''
                      }


                      {
                        registration_json_value.type == 'textarea' ?
                          <div className="mvx-vendor-form-input-field-container">
                            <label>Characters Limit</label>
                            <input type="number" className="mvx-vendor-form-input-field" value={registration_json_value.limit} onChange={e => {this.onlebelchange(e, registration_json_index, 'limit') }} />
                          </div>
                        : ''
                      }


                      {
                        registration_json_value.type == 'attachment' ?
                          <div className='row'>
                            <div className="mvx-vendor-form-input-field-container col-50">
                            <label>File Type</label>
                              <input type="checkbox" className="mvx-vendor-form-input-field" checked={registration_json_value.muliple} onChange={e => {this.onlebelchange(e, registration_json_index, 'muliple') }} />
                              <label className='auto-width'>Multiple</label>
                            </div>

                            <div className="mvx-vendor-form-input-field-container col-50">
                              <label>Maximum file size</label>
                              <input type="text" className="mvx-vendor-form-input-field" value={registration_json_value.fileSize} onChange={e => {this.onlebelchange(e, registration_json_index, 'fileSize') }} />
                            </div>

                            <div className="mvx-vendor-form-input-field-container col-50">
                              <label className='auto-width'>Acceptable file types</label>
                              {registration_json_value.fileType.map((xnew, inew) => 
                                  <div>
                                    <input type="checkbox" checked={xnew.selected} onChange={e => {this.onlebelchange(e, registration_json_index, 'selected', inew) }} />
                                    <label>{xnew.label}</label>
                                  </div>
                                )}
                            </div>
                          </div>
                        : ''
                      }



                      {
                        registration_json_value.type == 'recapta' ?
                          <div className='row'>
                            <div className="mvx-vendor-form-input-field-container col-100" value={registration_json_value.recaptchatype}
                                onChange={e => {this.onlebelchange(e, registration_json_index, 'recaptchatype') }}>
                              <div className='w-50 pr-15'>
                                <label className='form-title w-100'>reCAPTCHA Type</label>
                                <select className="mvx-vendor-form-input-field default-select">
                                  <option value="v3">reCAPTCHA v3</option>
                                  <option value="v2">reCAPTCHA v2</option>
                                </select>
                              </div>
                            </div>



                            {registration_json_value.recaptchatype === 'v3' ?
                              <div class="mvx-vendor-form-input-field-container col-50">
                                <label className='form-title w-100'>Site key</label>
                                <input type="text" class="mvx-vendor-form-input-field" value={registration_json_value.sitekey} onChange={e => {this.onlebelchange(e, registration_json_index, 'sitekey') }} />
                              </div>
                            : '' }


                            {registration_json_value.recaptchatype === 'v3' ?
                              <div class="mvx-vendor-form-input-field-container col-50">
                                <label className='form-title w-100'>Secret key</label>
                                <input type="text" ng-model="field.secretkey" class="mvx-vendor-form-input-field" value={registration_json_value.secretkey} onChange={e => {this.onlebelchange(e, registration_json_index, 'secretkey') }} />
                              </div>
                            : '' }

                            {registration_json_value.recaptchatype === 'v2' ?
                              <div class="mvx-vendor-form-input-field-container col-100">
                                <label className='form-title w-100'>Recaptcha Script</label>
                                <textarea cols="20" rows="3" class="mvx-vendor-form-input-field default-textarea" value={registration_json_value.script} onChange={e => {this.onlebelchange(e, registration_json_index, 'script') }}></textarea>
                              </div>
                            : '' }


                            <div class="mvx-vendor-form-input-field-container col-100">
                              <p>To get <b>reCAPTCHA</b> script, register your site with google account <a href="https://www.google.com/recaptcha" target="_blank">Register</a></p>
                            </div>

                          </div>

                        : ''
                      }

                      
                      
                      {registration_json_value.type == 'checkboxes' || registration_json_value.type == 'multi-select' || registration_json_value.type == 'radio' || registration_json_value.type == 'dropdown' ? 
                        <div className="mvx-vendor-form-input-field-container">
                          <a className="btn red-btn" onClick={(e) => this.addSelectBoxOption(e, registration_json_index)}>Add New</a>
                          <ul className="field-selectbox-options mt-15">
                          {registration_json_value.options.map((chekbox_option_key, checkbox_option_index) =>
                            <li>
                              <div className='f-row-50'>
                                <label class="form-title w-100">Label</label>
                                <input type="text" value={chekbox_option_key.label} onChange={e => {this.onlebelchange(e, registration_json_index, 'select_option', checkbox_option_index) }}/>
                              </div>
                              <div className='f-row-50'>
                                <label class="form-title w-100">Value</label>
                                <input type="text" value={chekbox_option_key.value} onChange={e => {this.onlebelchange(e, registration_json_index, 'select_option1', checkbox_option_index) }} />
                              </div>
                              <div className='f-row-100'>
                                <div className='del-btn'>
                                  <a onClick={(e) => this.removeSelectboxOption(e, registration_json_index, checkbox_option_index)}><i className="mvx-font icon-close"></i></a>
                                </div>
                                <div className='float-right'>
                                Selected
                                {registration_json_value.type === 'radio' || registration_json_value.type === 'dropdown' ?
                                <input type="radio" value="1"  name={`option-${registration_json_value.id}`} checked={chekbox_option_key.selected} onChange={e => {this.onlebelchange(e, registration_json_index, 'selected_radio_box', option) }} />
                                :
                                <input type="checkbox" value="true" checked={chekbox_option_key.selected} onChange={e => {this.onlebelchange(e, registration_json_index, 'selected_box', checkbox_option_index) }} />
                                }
                                </div>
                              </div>
                            </li>
                          )}
                          </ul>
                        </div>
                      : '' }

                        {/*<p className="add-option"><sapn><i className="far fa-circle"></i> <input type="text" placeholder="option 1"/></sapn></p>

                        <p className="add-option"><sapn><i className="far fa-circle"></i> <input type="text" placeholder="Add option  or add others"/></sapn></p>*/}

                       </div>
                       : '' }
                    

                       {registration_json_value.hidden ? 
                          <div className="mvx-footer-icon-form">
                              <i class="mvx-font icon-vendor-form-copy" onClick={e => {this.OnDuplicateSelectChange(e, registration_json_index, 'duplicate') }}></i>
                              <i class="mvx-font icon-vendor-form-delete" onClick={(e) => this.handleRemoveClickNew(e, registration_json_index)}></i>
                              <i class="mvx-font icon-vendor-form-add" onClick={(e) => this.handleAddClickNew(e, registration_json_value.type)}></i>
                              <span>Require <input type="checkbox" checked={registration_json_value.required} onChange={e => {this.OnRegistrationSelectChange(e, registration_json_index, 'require') }}/></span>
                          </div>
                        : '' }
                </div>


              </div>
            }
            </li>
            )

            )}
            </ReactSortable>
            </ul>























          
            </div>
            :
          
            <div>
            {Object.keys(this.state.list_of_module_data).length > 0 ?
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
              : <PuffLoader css={override} color={"#cd0000"} size={200} loading={true} />}
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