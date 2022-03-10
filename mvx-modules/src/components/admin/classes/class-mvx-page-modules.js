import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import PuffLoader from "react-spinners/PuffLoader";
import { css } from "@emotion/react";

import {
  BrowserRouter as Router,
  Link,
  useLocation,
  withRouter,
  useParams,
  NavLink
} from "react-router-dom";

import Button from '@material-ui/core/Button';
import TextField from '@material-ui/core/TextField';
import Dialog from '@material-ui/core/Dialog';
import DialogActions from '@material-ui/core/DialogActions';
import DialogContent from '@material-ui/core/DialogContent';
import DialogContentText from '@material-ui/core/DialogContentText';
import DialogTitle from '@material-ui/core/DialogTitle';
import HeaderSection from './class-mvx-page-header';


const override = css`
  display: block;
  margin: 0 auto;
  border-color: red;
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
      loading: true,
      module_tabs: [],
      tabIndex: 0,
      query: null,
    };
    this.query = null;
    // when click on checkbox
    this.handleOnChange = this.handleOnChange.bind(this);
    // popup close for paid module
    this.handleClose = this.handleClose.bind(this);
    // popup close for required plugin inactive popup
    this.handleClose_dynamic = this.handleClose_dynamic.bind(this);
    // search select module trigger
    this.handleselectmodule = this.handleselectmodule.bind(this);

    this.setTabIndex = this.setTabIndex.bind(this);

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);


  }

  setTabIndex(e) {
    console.log(e);

    this.setState({
      tabIndex: e
    });
  }

  // search select module trigger
  handleselectmodule(e) {
    this.setState({isLoaded: true})
    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/module_lists?module_id=${e.value}`
    })
    .then(response => {
      this.setState({
        items: response.data,
        isLoaded: false
      });
    })
  }
  // popup close for paid module
  handleClose(e) {
    this.setState({
      open_model: false
    });
  }
  // popup close for required plugin inactive popup
  handleClose_dynamic(e) {
    var add_module_false = new Array(this.state.items.length).fill(false);
    this.setState({
      open_model_dynamic: add_module_false
    });
  }
  // when click on checkbox
  handleOnChange(event, tab, plan, is_plugin_active, doc_id, items, parent_index, sub_index, module_id) {
 
    if (plan == 'pro') {
      this.setState({
        open_model: true
      });
    } else if(!is_plugin_active) {} else {
      // If everything works fine then checkbox trigger
      items[parent_index].options[sub_index].is_active = event.target.checked;

      this.setState({
        items: items,
      });

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/checkbox_update`,
        data: {
          module_id: module_id,
          is_checked: event.target.checked
        }
      })
      .then( ( res ) => {
        console.log('success');
      } );

    }
  }

  componentDidMount() {
      this.setState({isLoaded: true})
      // fetch all modules, checkbox values, select list values
      Promise.all([
        fetch(`${appLocalizer.apiUrl}/mvx_module/v1/module_lists?module_id=all`).then(res => res.json()),
      ]).then(([product, settings, module_ids, module_tabs]) => {
        this.setState({
          isLoaded: false,
          items: product,
        });
      }).catch((error) => {
        console.log(error);
      });
  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {



    const loader_text_display = this.state.isLoaded ? "loading_ongoing" : '';
    let queryt = this.useQuery();

    /*if (this.state.isLoaded) {
      return ( <PuffLoader css={override} color={"#cd0000"} size={500} loading={this.state.isLoaded} />);
    }*/

      return (

        <div className="mvx-module-section-before-header">
          
          <HeaderSection />

          
            <div className="mvx-child-container">
              
              <div className="mvx-sub-container">

              
                  <div className="mvx-module-section-ui module-listing dashboard-wrapper">
            <div className="mvx-module-grid">

            {this.state.items.length == 0 ? <PuffLoader css={override} color={"#cd0000"} size={200} loading={true} /> : this.state.items.map((student1, index1) => (

              <div className="mvx-module-list-start">
                <div className="mvx-module-list-container">
                <div className="mvx-module-category-label">{student1.label}</div>
                  <div className="mvx-module-option-row">
                  
                  {student1.options.map((student, index) => (
                    <div className="mvx-module-section-options-list">
                      <div className = {`mvx-module-settings-box ${student.is_active? 'active' : ''}`}>
                          <span class="dashicons dashicons-cover-image"></span>                        
                        <header>
                          <div className="mvx-module-list-label-plan-action-swap">
                            <div className="mvx-module-list-label-text">
                              {student.name}
                              {student.plan == 'pro' ? <span className="mvx-module-section-pro-badge">{appLocalizer.pro_text}</span> : ''}
                            </div>
                          </div>
                          <p>
                          {student.description}
                          </p>
                        </header>
                        {student.required_plugin_list ? <div className="mvx-module-require-name">Requires:</div> : ''}
                        <ul>
                        {
                          student.required_plugin_list &&
                          student.required_plugin_list.map((company, index_req) => 
                          <li>
                            {company.is_active ? <div className="mvx-module-active-plugin-class"><img src={appLocalizer.right_logo} width="10" height="10" alt="Active"/></div> : <div className="inactive-plugin-class"><img src={appLocalizer.cross_logo} width="10" height="10" alt="Inactive"/></div>}
                            <a href={company.plugin_link} className="mvx-third-party-plugin-link-class">{company.plugin_name}</a>
                          </li>
                          )}
                        </ul>
                        <div className="mvx-module-current-status wp-clearfix">
                          {student.is_active ? <a href={student.mod_link} className="module-settings button button-secondary mvx-module-url-button">{appLocalizer.settings_text}</a> : '' }
                          <a href={student.doc_link} className="button button-secondary mvx-module-url-button">{appLocalizer.documentation_text}</a>
                          <div class="mvx-toggle-checkbox-content">
                            <input type="checkbox" className="mvx-toggle-checkbox" id={`mvx-toggle-switch-${student.id}`} name="modules[]" value={student.id} checked={student.is_active ? true : false} onChange={(e) => this.handleOnChange(e, index, student.plan, student.is_required_plugin_active, student.doc_id, this.state.items, index1, index, student.id)}/>
                            <label for={`mvx-toggle-switch-${student.id}`}></label>
                          </div>
                        </div>
                        <Dialog open={this.state.open_model_dynamic[index]} onClose={this.handleClose_dynamic} aria-labelledby="form-dialog-title">
                          <DialogTitle id="form-dialog-title"><div className="mvx-module-dialog-title">Warning !!</div></DialogTitle>
                            <DialogContent>
                              <DialogContentText>
                                <div className="mvx-module-dialog-content">
                                  Please active required first to use {student.name} module.
                                </div>
                              </DialogContentText>
                            </DialogContent>
                          <DialogActions>
                            <Button onClick={this.handleClose_dynamic} color="primary">Cancel</Button>
                          </DialogActions>
                        </Dialog>
                      </div>
                    </div>
              
                  ))
                }
                </div>
              </div>
            </div>
            )) }
            </div>     
          </div>

                  <Dialog open={this.state.open_model} onClose={this.handleClose} aria-labelledby="form-dialog-title">
                    <DialogTitle id="form-dialog-title"><div className="mvx-module-dialog-title">Upgrade To Pro</div></DialogTitle>
                    <DialogContent>
                      <DialogContentText>
                      <div className="mvx-module-dialog-content">
                        To use this paid module, Please visit <a href="https://wc-marketplace.com/addons/">WC Marketplace</a> Site.
                      </div>
                      </DialogContentText>
                    </DialogContent>
                    <DialogActions>
                      <Button onClick={this.handleClose} color="primary">Cancel</Button>
                    </DialogActions>
                  </Dialog>
              </div>  

              <div className="mvx-adv-image-display">
                <a href="https://www.qries.com/" target="__blank">
                  <img alt="Multivendor X" src={appLocalizer.multivendor_logo}/>
                </a>
              </div>

            </div>


        </div>
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
export default App;