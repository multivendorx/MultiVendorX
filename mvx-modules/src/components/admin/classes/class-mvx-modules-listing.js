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
import Dialog from '@material-ui/core/Dialog';
import DialogActions from '@material-ui/core/DialogActions';
import DialogContent from '@material-ui/core/DialogContent';
import DialogContentText from '@material-ui/core/DialogContentText';
import DialogTitle from '@material-ui/core/DialogTitle';
import HeaderSection from './class-mvx-page-header';
import BannerSection from './class-mvx-page-banner';

const override = css`
  display: block;
  margin: 0 auto;
  border-color: red;
`;


class MVX_Module_Listing extends Component {
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
      total_number_of_module: 0,
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

    this.handleModuleSearch = this.handleModuleSearch.bind(this);

    this.handleModuleSearchByCategory = this.handleModuleSearchByCategory.bind(this);

    this.mvx_search_different_module_status = this.mvx_search_different_module_status.bind(this);

  }

  mvx_search_different_module_status(e, status) {
    // multiple module status
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/get_as_per_module_status`, {
      params: { module_status: status }
    })
      .then(response => {
        this.setState({
          items: response.data,
        });
      })

  }

  handleModuleSearch(e) {
    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/module_lists?module_id=${e.target.value}`
    })
      .then(response => {
        this.setState({
          items: response.data,
          isLoaded: false
        });
      })
  }

  handleModuleSearchByCategory(e) {
    if (e) {
      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/search_module_lists`, {
        params: { category: e.label }
      })
        .then(response => {
          this.setState({
            items: response.data,
          });
        })
    } else {
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
  }

  setTabIndex(e) {
    console.log(e);

    this.setState({
      tabIndex: e
    });
  }

  // search select module trigger
  handleselectmodule(e) {
    this.setState({ isLoaded: true })
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
    } else if (!is_plugin_active) { } else {
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
        .then((res) => {
          console.log('success');
        });

    }
  }

  componentDidMount() {
    this.setState({ isLoaded: true })
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


    // fetch total number of modules 
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/modules_count`
    )
      .then(response => {
        this.setState({
          total_number_of_module: response.data,
        });
      })

  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {
    return (
      <div className="mvx-general-wrapper mvx-modules">
        <HeaderSection />
        <div className="mvx-container">
          <div className="mvx-middle-container-wrapper">
            <div className="mv-off-white-box">
              <div className="mvx-tab-name-display">Module</div>
              <p>
                Customize your marketplace site by enabling the module that you
                prefer.
              </p>
            </div>

            <div className="mvx-search-and-multistatus-wrap">
              <div className="mvx-multistatus-sec mvx-module-left-sec">
                <ul className="mvx-multistatus-ul mvx-row">
                  <li className="mvx-multistatus-item mvx-totalmodule-text">
                    <div
                      className="mvx-total-module-name-and-count mvx-d-inline-flex"
                      onClick={(e) =>
                        this.mvx_search_different_module_status(e, "all")
                      }
                    >
                      <div className="mvx-total-modules-name">Total Modules :</div>
                      <div className="mvx-total-modules-count">
                        {this.state.total_number_of_module}
                      </div>
                    </div>
                  </li>
                  <li className="mvx-multistatus-item mvx-divider"></li>
                  <li className="mvx-multistatus-item">
                    <Button
                      className="default-text pa-0"
                      onClick={(e) =>
                        this.mvx_search_different_module_status(e, "active")
                      }
                    >
                      Active
                    </Button>
                  </li>
                  <li className="mvx-multistatus-item mvx-divider pa-0"></li>
                  <li className="mvx-multistatus-item">
                    <Button
                      className="default-text"
                      onClick={(e) =>
                        this.mvx_search_different_module_status(e, "inactive")
                      }
                    >
                      Inactive
                    </Button>
                  </li>
                </ul>
              </div>
              <div className="mvx-searchbar-sec mvx-module-right-sec">
                <div className='mvx-header-search-section'>
                  <label><i className='mvx-font icon-search'></i></label>
                  <input
                    type="text"
                    onChange={(e) => this.handleModuleSearch(e)}
                    placeholder="Search modules"
                  />
                </div>
              </div>
            </div>

            <div className="mvx-wrap-bulk-all-date mb-25">
              <div className="mvx-wrap-bulk-action mvx-row mvx-row-sm-8">
                <div className="mvx-col-auto mvx-search-category">
                  <Select
                    placeholder="Search by Category"
                    options={appLocalizer.select_module_category_option}
                    isClearable={true}
                    className="mvx-module-section-list-data"
                    onChange={(e) => this.handleModuleSearchByCategory(e)}
                  />
                </div>
              </div>
            </div>

            <div className="mvx-module-section-ui module-listing dashboard-wrapper">
              <div>
                {this.state.items.length == 0 ? (
                  <PuffLoader
                    css={override}
                    color={"#cd0000"}
                    size={200}
                    loading={true}
                  />
                ) : (
                  this.state.items.map((student1, index1) => (
                    <div className="mvx-module-list-start mb-25">
                      <div className="mvx-module-list-container">
                        <div className="mvx-text-with-line-wrapper">
                          <div className="mvx-report-text w-100 mr-0">
                            <span>{student1.label}</span>
                          </div>
                        </div>

                        <div className="mvx-module-option-row">
                          {student1.options.map((student, index) => (
                            <div className="mvx-module-section-options-list">
                              <div
                                className={`mvx-module-settings-box ${
                                  student.is_active ? "active" : ""
                                }`}
                              >
                                <div className="mvx-module-icon">
                                  <i className={`mvx-font ${student.thumbnail_dir}`}></i>
                                </div>

                                <header>
                                  <div className="mvx-module-list-label-plan-action-swap">
                                    <div className="mvx-module-list-label-text">
                                      {student.name}
                                      {student.plan == "pro" ? (
                                        <span className="mvx-module-section-pro-badge">
                                          {appLocalizer.pro_text}
                                        </span>
                                      ) : (
                                        ""
                                      )}
                                    </div>
                                  </div>
                                  <p>{student.description}</p>
                                </header>
                                {student.required_plugin_list ? (
                                  <div className="mvx-module-require-name">
                                    Requires:
                                  </div>
                                ) : (
                                  ""
                                )}
                                <ul>
                                  {student.required_plugin_list &&
                                    student.required_plugin_list.map(
                                      (company, index_req) => (
                                        <li>
                                          {company.is_active ? (
                                            <div className="mvx-module-active-plugin-class">
                                              <img
                                                src={appLocalizer.right_logo}
                                                width="10"
                                                height="10"
                                                alt="Active"
                                              />
                                            </div>
                                          ) : (
                                            <div className="inactive-plugin-class">
                                              <span className="mvx-font icon-no"></span>
                                            </div>
                                          )}
                                          <a
                                            href={company.plugin_link}
                                            className="mvx-third-party-plugin-link-class"
                                          >
                                            {company.plugin_name}
                                          </a>
                                        </li>
                                      )
                                    )}
                                </ul>
                                <div className="mvx-module-current-status wp-clearfix">
                                  {student.is_active && student.mod_link ? (
                                    <a
                                      href={student.mod_link}
                                      className="module-settings button button-secondary mvx-module-url-button"
                                    >
                                      {appLocalizer.settings_text}
                                    </a>
                                  ) : (
                                    ""
                                  )}
                                  <a
                                    href={student.doc_link}
                                    className="button button-secondary mvx-module-url-button"
                                  >
                                    {appLocalizer.documentation_text}
                                  </a>
                                  <div className="mvx-toggle-checkbox-content">
                                    <input
                                      type="checkbox"
                                      className="mvx-toggle-checkbox"
                                      id={`mvx-toggle-switch-${student.id}`}
                                      name="modules[]"
                                      value={student.id}
                                      checked={student.is_active ? true : false}
                                      onChange={(e) =>
                                        this.handleOnChange(
                                          e,
                                          index,
                                          student.plan,
                                          student.is_required_plugin_active,
                                          student.doc_id,
                                          this.state.items,
                                          index1,
                                          index,
                                          student.id
                                        )
                                      }
                                    />
                                    <label
                                      for={`mvx-toggle-switch-${student.id}`}
                                    ></label>
                                  </div>
                                </div>
                                <Dialog
                                  open={this.state.open_model_dynamic[index]}
                                  onClose={this.handleClose_dynamic}
                                  aria-labelledby="form-dialog-title"
                                >
                                  <DialogTitle id="form-dialog-title">
                                    <div className="mvx-module-dialog-title">
                                      Warning !!
                                    </div>
                                  </DialogTitle>
                                  <DialogContent>
                                    <DialogContentText>
                                      <div className="mvx-module-dialog-content">
                                        Please active required first to use{" "}
                                        {student.name} module.
                                      </div>
                                    </DialogContentText>
                                  </DialogContent>
                                  <DialogActions>
                                    <Button
                                      onClick={this.handleClose_dynamic}
                                      color="primary"
                                    >
                                      Cancel
                                    </Button>
                                  </DialogActions>
                                </Dialog>
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    </div>
                  ))
                )}
              </div>
            </div>

            <Dialog
              open={this.state.open_model}
              onClose={this.handleClose}
              aria-labelledby="form-dialog-title"
            >
              <DialogTitle id="form-dialog-title">
                <div className="mvx-module-dialog-title">Upgrade To Pro</div>
              </DialogTitle>
              <DialogContent>
                <DialogContentText>
                  <div className="mvx-module-dialog-content">
                    To use this paid module, Please visit{" "}
                    <a href="https://multivendorx.com/">MultivendorX</a> Site.
                  </div>
                </DialogContentText>
              </DialogContent>
              <DialogActions>
                <Button onClick={this.handleClose} color="primary">
                  Cancel
                </Button>
              </DialogActions>
            </Dialog>
          </div>

          <BannerSection />
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
export default MVX_Module_Listing;