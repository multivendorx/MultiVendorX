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

  // new registration settings

  handleAddClickNew(e) {
    var formJson = this.state.mvx_registration_fileds_list;
    var jsonLength = formJson.length;

    formJson.push({
        id: jsonLength,
        type: 'multiple_choice',
        label: '',
        hidden: false,
        placeholder: '',
        required: false,
        cssClass: '',
        tip_description: ''
    });

    this.setState({
      mvx_registration_fileds_list: formJson
    });

  }

  handleRemoveClickNew(e , index) {
    this.state.mvx_registration_fileds_list.splice(index, 1);
    this.setState({
      mvx_registration_fileds_list: this.state.mvx_registration_fileds_list
    });
  }

  handleActiveClick(e, index, label) {
    
    if (label == 'parent') {
      this.state.mvx_registration_fileds_list[0].hidden = true;


      this.state.mvx_registration_fileds_list.map((data_active, index_active) => {
          if (index == 0) {} else {
            this.state.mvx_registration_fileds_list[index_active].hidden = false;
          }
        }
      )

    } else if (label == 'sub') {
      this.state.mvx_registration_fileds_list.map((data_active, index_active) => {
          if (index == 0) {} else {
            if (index_active == index) {
              this.state.mvx_registration_fileds_list[index].hidden = true;
            } else {
              this.state.mvx_registration_fileds_list[index_active].hidden = false;
            }
          }
        }
      )
    }

    //registration_title_hidden

    this.setState({
      mvx_registration_fileds_list: this.state.mvx_registration_fileds_list
    });
  }

  OnRegistrationSelectChange(e, index, types) {
    let new_items = this.state.mvx_registration_fileds_list;

    if (types == 'select_drop') {
      new_items[index]['type'] = e.target.value;
    } else if (types == 'label') {
      new_items[index]['label'] = e.target.value;
    }


    this.setState({
      new_items,
    });
    console.log(e.target.value);
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


    // new registration from
    var formJson4 = this.state.mvx_registration_fileds_list;
    
    formJson4.push({
        id: 'parent_title',
        type: 'p_title',
        label: '',
        hidden: false,
        label_placeholder: '',
        description: '',
        description_placeholder: '',
    });

    if (this.state.mvx_registration_fileds_list.length == 1) {
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
    }

    this.setState({
      mvx_registration_fileds_list: formJson4
    });
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


 
  return (
    <div>
    {appLocalizer.mvx_all_backend_tab_list['marketplace-general-settings'].map((data, index) => (
      <div>

      {
        /*name = !name ? 'registration' : name,*/

        data.modulename == name ?
          data.modulename == 'registration' ?

            <div className="mvx-form-vendor-register">

              <div className={`mvx-top-part-from ${this.state.mvx_registration_fileds_list && this.state.mvx_registration_fileds_list.length > 0 && this.state.mvx_registration_fileds_list[0].hidden ? 'mvx-form-left-line-active' : ''}`} onClick={(e) => this.handleActiveClick(e, '', 'parent')}>
                  <div className="content">
                   <div className="mvx-untitle-content"><input type="text" placeholder="Untitled form"/></div>
                   <div className="mvx-from-description"><input type="text" placeholder="From Description"/></div>
                  </div>
              </div>




              {console.log(this.state.mvx_registration_fileds_list)}


            
            {JSON.stringify(this.state.mvx_registration_fileds_list)}

            {this.state.mvx_registration_fileds_list.map((registration_json_value, registration_json_index) => (
              
              registration_json_value.id == 'parent_title' ? '' :

              <div className= {`mvx-option-part ${registration_json_value.hidden ? 'mvx-form-left-line-active' : ''}`} onClick={(e) => this.handleActiveClick(e, registration_json_index, 'sub')}>
                <div className="content">
                    <div className="question-input">
                        <div className="question-input-items first-question"><input type="text" placeholder="Untitled Question" value={registration_json_value.label}
                                        onChange={e => {this.OnRegistrationSelectChange(e, registration_json_index, 'label') }}/></div>
                         
                          <div className="question-input-items ">
                            <select className="mvx-registration-select-choice" value={registration_json_value.type}
                                        onChange={e => {this.OnRegistrationSelectChange(e, registration_json_index, 'select_drop') }}>
                              <option value="checkbox">Checkbox</option>
                              <option value="multiple_choice">Multiple Choice</option>
                              <option value="dropdown">Dropdown</option>
                              <option value="file_upload">File Upload</option>
                            </select>
                        </div>

                    </div>
                    <div className="next_option_part">
                        <p className="add-option"><sapn><i className="far fa-circle"></i> <input type="text" placeholder="option 1"/></sapn></p>

                        <p className="add-option"><sapn><i className="far fa-circle"></i> <input type="text" placeholder="Add option  or add others"/></sapn></p>

                       </div>
                      <div className="mvx-footer-icon-form">
                          <span class="dashicons dashicons-admin-page"></span>
                          <span class="dashicons dashicons-trash" onClick={(e) => this.handleRemoveClickNew(e, registration_json_index)}></span>
                          <span class="dashicons dashicons-plus-alt" onClick={(e) => this.handleAddClickNew(e)}></span>
                          <span>Require <input type="checkbox"/></span>
                      </div>
                </div>
              </div>
            
            )

            )}

























          
            </div>
            :
          
            <div>
              <DynamicForm
              key={`dynamic-form-${data.modulename}`}
              className={data.classname}
              title={data.tablabel}
              defaultValues={this.state.current}
              model= {appLocalizer.settings_fields[data.modulename]}
              method="post"
              modulename={data.modulename}
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