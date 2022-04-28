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

// import vendor page
import VendorManage from './class-mvx-vendor-manage';
import WorkBoard from './class-mvx-workboard-section';
import PaymentSettings from './class-mvx-payemnt-section';
import VendorManager from './class-mvx-manager-section';
import CommissionSettings from './class-mvx-commission-section';
import AnalyticsSettings from './class-mvx-analytics-section';
import AdvanceSettings from './class-mvx-advance-section';
import GESettings from './class-mvx-general-settings';
import HeaderSection from './class-mvx-page-header';
import Modules from './class-mvx-page-modules';
import StatusTools from './class-mvx-status-tools';

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

    this.Child = this.Child.bind(this);

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
    var $ = jQuery;
    var cs = 1;
    var cm = 4;
    jQuery(document).on("click", ".p-prev", function (event) {
      event.preventDefault();
      if (cs > 1) {
        $('.mvx-dashboard-slider').hide();
        cs--;
        //alert(cs);
        $('.mvx-dashboard-slider:nth-child(' + cs + ')').show();
        $('.border-block span').html(cs + ' of 4');
      }
    });
    $(document).on("click", ".p-next", function (event) {
      event.preventDefault();
      //alert(cs);
      if (cs < cm) {
        $('.mvx-dashboard-slider').hide();
        cs++;
        //alert(cs);
        $('.mvx-dashboard-slider:nth-child(' + cs + ')').show();
        $('.border-block span').html(cs + ' of 4');
      }
    });
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
  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {


    var $ = jQuery;
    let menuRoot = $('#toplevel_page_' + 'mvx');
    let currentUrl = window.location.href;
    let currentPath = currentUrl.substr(currentUrl.indexOf('admin.php'));

    ///////

    ///////

    menuRoot.on('click', 'a', function () {
      var self = $(this);

      $('ul.wp-submenu li', menuRoot).removeClass('current');

      if (self.hasClass('wp-has-submenu')) {
        $('li.wp-first-item', menuRoot).addClass('current');
      } else {
        self.parents('li').addClass('current');
      }
    });

    $('ul.wp-submenu a', menuRoot).each(function (index, el) {
      if ($(el).attr('href') === currentPath) {
        $(el).parent().addClass('current');

      } else {
        $(el).parent().removeClass('current');

      }
      return;
    });


    const loader_text_display = this.state.isLoaded ? "loading_ongoing" : '';
    let queryt = this.useQuery();

    if (new URLSearchParams(useLocation().hash).get("submenu") && new URLSearchParams(useLocation().hash).get("submenu") == 'vendor') {
      return (
        <VendorManage />
      );
    } else if (new URLSearchParams(useLocation().hash).get("submenu") && new URLSearchParams(useLocation().hash).get("submenu") == 'commission') {
      return (
        <CommissionSettings />
      );
    } else if (new URLSearchParams(useLocation().hash).get("submenu") && new URLSearchParams(useLocation().hash).get("submenu") == 'manager') {
      return (
        <VendorManage />
      );
    } else if (new URLSearchParams(useLocation().hash).get("submenu") && new URLSearchParams(useLocation().hash).get("submenu") == 'settings') {
      return (
        <GESettings />
      );
    } else if (new URLSearchParams(useLocation().hash).get("submenu") && new URLSearchParams(useLocation().hash).get("submenu") == 'payment') {
      return (
        <PaymentSettings />
      );
    } else if (new URLSearchParams(useLocation().hash).get("submenu") && new URLSearchParams(useLocation().hash).get("submenu") == 'advance') {
      return (
        <AdvanceSettings />
      );
    } else if (new URLSearchParams(useLocation().hash).get("submenu") && new URLSearchParams(useLocation().hash).get("submenu") == 'analytics') {
      return (
        <AnalyticsSettings />
      );
    } else if (new URLSearchParams(useLocation().hash).get("submenu") && new URLSearchParams(useLocation().hash).get("submenu") == 'work-board') {
      return (
        <WorkBoard />
      );
    } else if (new URLSearchParams(useLocation().hash).get("submenu") && new URLSearchParams(useLocation().hash).get("submenu") == 'status-tools') {
      return (
        <StatusTools />
      );
    } else if (new URLSearchParams(useLocation().hash).get("submenu") && new URLSearchParams(useLocation().hash).get("submenu") == 'modules') {
      return (
        <Modules />
      );
    } else {
      return (

        <div className="mvx-module-section-before-header">

          <HeaderSection />


          <div className="mvx-child-container">

            <div className="mvx-sub-container w-100">


              <div className="dashboard-tab-area no-link-under">


                <div className="mv-off-white-box mv-justify-content-between mv-align-items-center mb-90">

                  <div className="minw-70-per">
                    <div className="mvx-dashboard-slider">
                      <div className="mv-dashboard-top-icon mr-25">
                        <span>Pro</span>
                      </div>
                      <div className="pro-txt">
                        <h2>Activate MultiVendorX Pro License</h2>
                        <p>
                          Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                          tempor.
                        </p>
                        <a href="#" className="btn red-btn">
                          Active License
                        </a>
                      </div>
                    </div>
                    <div className="mvx-dashboard-slider">
                      <div className="mv-dashboard-top-icon mr-25">
                        <span>Pro</span>
                      </div>
                      <div className="pro-txt">
                        <h2>02 Activate MultiVendorX Pro License</h2>
                        <p>
                          Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                          tempor.
                        </p>
                        <a href="#" className="btn red-btn">
                          Active License
                        </a>
                      </div>
                    </div>
                    <div className="mvx-dashboard-slider">
                      <div className="mv-dashboard-top-icon mr-25">
                        <span>Pro</span>
                      </div>
                      <div className="pro-txt">
                        <h2>03 Activate MultiVendorX Pro License</h2>
                        <p>
                          Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                          tempor.
                        </p>
                        <a href="#" className="btn red-btn">
                          Active License
                        </a>
                      </div>
                    </div>
                    <div className="mvx-dashboard-slider">
                      <div className="mv-dashboard-top-icon mr-25">
                        <span>Pro</span>
                      </div>
                      <div className="pro-txt">
                        <h2>04 Activate MultiVendorX Pro License</h2>
                        <p>
                          Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                          tempor.
                        </p>
                        <a href="#" className="btn red-btn">
                          Active License
                        </a>
                      </div>
                    </div>
                  </div>

                  <div>
                    <div className="border-block">
                      <a href="#" className="p-prev">&lt;</a>
                      <span>1 of 4</span>
                      <a href="#" className="p-next">&gt;</a>
                    </div>
                  </div>
                </div>
                <div className="mv-box-row mb-90">
                  <div className="nv-col nv-col-45">
                    <div className="mv-off-white-box">
                      <h3 className="block-title w-100">This is what you get</h3>
                      <div className="responsive-table w-100">
                        <ul className="table-ul">
                          <li className="mv-align-items-center">
                            <div className="li-txt">
                              <span>
                                <i className="mvx-font icon-tab-products" />
                              </span>{" "}
                              Set up marketing tools
                            </div>
                            <div className="li-action">
                              <a href="#" className="btn color-btn w-100">
                                <i className="mvx-font icon-yes" />
                              </a>
                            </div>
                          </li>
                          <li className="mv-align-items-center">
                            <div className="li-txt">
                              <span>
                                <i className="mvx-font icon-tab-products" />
                              </span>{" "}
                              Set up marketing tools
                            </div>
                            <div className="li-action">
                              <a href="#" className="btn color-btn w-100">
                                <i className="mvx-font icon-yes" />
                              </a>
                            </div>
                          </li>
                          <li className="mv-align-items-center">
                            <div className="li-txt">
                              <span>
                                <i className="mvx-font icon-tab-products" />
                              </span>{" "}
                              Set up marketing tools
                            </div>
                            <div className="li-action">
                              <a href="#" className="btn color-btn w-100">
                                <i className="mvx-font icon-yes" />
                              </a>
                            </div>
                          </li>
                          <li className="mv-align-items-center">
                            <div className="li-txt">
                              <span>
                                <i className="mvx-font icon-tab-products" />
                              </span>{" "}
                              Set up marketing tools
                            </div>
                            <div className="li-action">
                              <a href="#" className="btn color-btn w-100">
                                <i className="mvx-font icon-yes" />
                              </a>
                            </div>
                          </li>
                          <li className="mv-align-items-center border-box">
                            <div className="li-txt">
                              <span>
                                <i className="mvx-font icon-tab-products" />
                              </span>{" "}
                              Set up marketing tools
                            </div>
                            <div className="li-action">
                              <a href="#" className="btn border-btn w-100">
                                Setup
                              </a>
                            </div>
                          </li>
                          <li className="mv-align-items-center">
                            <div className="li-txt">
                              <span>
                                <i className="mvx-font icon-tab-products" />
                              </span>{" "}
                              Set up marketing tools
                            </div>
                            <div className="li-action">
                              <a href="#" className="btn color-btn w-100">
                                <i className="mvx-font icon-yes" />
                              </a>
                            </div>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <div className="nv-col nv-col-55">
                    <div className="mv-box-row mv-box-row-sm">
                      <div className="nv-col nv-col-50">
                        <div className="mv-off-white-box">
                          <div className="call-block text-center">
                            <figure>
                              <i className="mvx-font icon-vendor-application" />
                            </figure>
                            <figcaption>
                              <h2>Documentation Forum</h2>
                              <p>
                                Further Clarification Visit Our <br />
                                Document Forum
                              </p>
                              <a href="#">
                                Visit Documentation Forum{" "}
                                <span className="mvx-font icon-down-arrow" />
                              </a>
                            </figcaption>
                          </div>
                        </div>
                      </div>
                      <div className="nv-col nv-col-50">
                        <div className="mv-off-white-box">
                          <div className="call-block text-center">
                            <figure>
                              <i className="mvx-font icon-vendor-application" />
                            </figure>
                            <figcaption>
                              <h2>Support Forum</h2>
                              <p>
                                Further Clarification Visit Our <br />
                                Document Forum
                              </p>
                              <a href="#">
                                Join Support Forum{" "}
                                <span className="mvx-font icon-down-arrow" />
                              </a>
                            </figcaption>
                          </div>
                        </div>
                      </div>
                      <div className="nv-col nv-col-100 mt-10">
                        <div className="mv-off-white-box">
                          <h3 className="block-title w-100">Quick Link</h3>
                          <div className="w-100 minh-130">
                            <ul className="row-link">
                              <li>
                                <a href="#">
                                  <figure>
                                    <i className="mvx-font icon-vendor-personal" />
                                  </figure>
                                  Add Vendor
                                </a>
                              </li>
                              <li>
                                <a href="#">
                                  <figure>
                                    <i className="mvx-font icon-vendor-application" />
                                  </figure>
                                  Commission
                                </a>
                              </li>
                              <li>
                                <a href="#">
                                  <figure>
                                    <i className="mvx-font icon-vendor-application" />
                                  </figure>
                                  Add Product
                                </a>
                              </li>
                              <li>
                                <a href="#">
                                  <figure>
                                    <i className="mvx-font icon-vendor-application" />
                                  </figure>
                                  Payment
                                </a>
                              </li>
                              <li>
                                <a href="#" className="border-box">
                                  <figure>
                                    <i className="mvx-font icon-vendor-application" />
                                  </figure>
                                  Add New
                                </a>
                              </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="mv-box-row mb-90 switch-section">
                  <div className="nv-col-100 text-center">
                    <div className="w-100 mb-45">
                      <div className="mv-dashboard-top-icon float-none gra-por">
                        <span>Pro</span>
                      </div>
                    </div>
                    <h2>Get more by Switching to Pro</h2>
                    <p>
                      Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                      tempor incididunt.
                    </p>
                    <a href="#" className="btn red-btn">
                      Upgrade to Pro
                    </a>
                  </div>
                </div>
                <div className="mv-box-row mb-90 compare-section text-center">
                  <div className="nv-col-100 text-center">
                    <div className="w-100 mb-45">
                      <h2>Here Is What You Get In Pro Compared to Free</h2>
                      <div className="compare-table-holder">
                        <ul>
                          <li>
                            <ul>
                              <li />
                              <li>Support</li>
                              <li>2 Premium Modules</li>
                              <li>Store Widgets</li>
                              <li>Premium</li>
                              <li>Modules</li>
                              <li>Support</li>
                              <li>
                                <a href="#">
                                  <span>
                                    <i className="mvx-font icon-down-arrow-02" />
                                  </span>{" "}
                                  Show More
                                </a>
                              </li>
                            </ul>
                          </li>
                          <li>
                            <ul>
                              <li>Free</li>
                              <li>Ticket Based Support</li>
                              <li>
                                <i className="mvx-font icon-no red" />
                              </li>
                              <li>
                                <i className="mvx-font icon-no red" />
                              </li>
                              <li>Five Venders</li>
                              <li>
                                <i className="mvx-font icon-yes blue" />
                              </li>
                              <li>
                                <i className="mvx-font icon-no red" />
                              </li>
                              <li />
                            </ul>
                          </li>
                          <li>
                            <span className="recommend-tag">Recommend</span>
                            <ul>
                              <li>Pro</li>
                              <li>Ticket Based Support</li>
                              <li>
                                <i className="mvx-font icon-no red" />
                              </li>
                              <li>
                                <i className="mvx-font icon-no red" />
                              </li>
                              <li>Five Venders</li>
                              <li>
                                <i className="mvx-font icon-yes blue" />
                              </li>
                              <li>
                                <i className="mvx-font icon-no red" />
                              </li>
                              <li />
                            </ul>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="mv-box-row mb-90 money-section text-center">
                  <div className="nv-col-100 text-center">
                    <div className="w-100 mb-45">
                      <h2>
                        <span className="gra-txt">30 Days</span> Money-Back Guarantee
                      </h2>
                      <p>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
                        eiusmod tempor incididunt.
                      </p>
                      <div className="money-table-holder">
                        <ul>
                          <li>
                            <ul>
                              <li>Yearly</li>
                              <li>
                                <div className="m-price">
                                  $399 <sub>/ $599</sub>
                                </div>
                              </li>
                              <li>
                                <a href="#" className="btn border-btn w-100">
                                  Buy Now
                                </a>
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> 10 Sites
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> 50+ Modules
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> Unlimited
                                Support{" "}
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> Lifetime Updates
                              </li>
                              <li>
                                <a href="#" className="show-link">
                                  <span>
                                    <i className="mvx-font icon-down-arrow-02" />
                                  </span>{" "}
                                  Show More
                                </a>
                              </li>
                            </ul>
                          </li>
                          <li>
                            <span className="recommend-tag saver">Super saver</span>
                            <ul>
                              <li>Lifetime</li>
                              <li>
                                <div className="m-price">
                                  $499 <sub>/ $599</sub>
                                </div>
                              </li>
                              <li>
                                <a href="#" className="btn red-btn w-100">
                                  Buy Now
                                </a>
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> 10 Sites
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> 50+ Modules
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> Unlimited
                                Support{" "}
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> Lifetime Updates
                              </li>
                              <li>
                                <a href="#" className="show-link">
                                  <span>
                                    <i className="mvx-font icon-down-arrow-02" />
                                  </span>{" "}
                                  Show More
                                </a>
                              </li>
                            </ul>
                          </li>
                          <li>
                            <ul>
                              <li>Monthly</li>
                              <li>
                                <div className="m-price">
                                  $299 <sub>/ $599</sub>
                                </div>
                              </li>
                              <li>
                                <a href="#" className="btn border-btn w-100">
                                  Buy Now
                                </a>
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> 10 Sites
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> 50+ Modules
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> Unlimited
                                Support{" "}
                              </li>
                              <li>
                                <span className="mvx-font icon-form-radio" /> Lifetime Updates
                              </li>
                              <li>
                                <a href="#" className="show-link">
                                  <span>
                                    <i className="mvx-font icon-down-arrow-02" />
                                  </span>{" "}
                                  Show More
                                </a>
                              </li>
                            </ul>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
                <div className="mv-box-row mb-90 switch-section">
                  <div className="nv-col-100 text-center">
                    <div className="w-100 mb-45">
                      <div className="mv-dashboard-top-icon float-none gra-por">
                        <span>Pro</span>
                      </div>
                    </div>
                    <h2>Get to Go?</h2>
                    <p>
                      Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                      tempor incididunt.
                    </p>
                    <a href="#" className="btn red-btn">
                      Upgrade to Pro
                    </a>
                  </div>
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



          </div>


        </div>
      );
    }
  }

  Child({ name }) {
    const loader_text_display = this.state.isLoaded ? "loading_ongoing" : '';

    if (this.state.isLoaded) {
      return (<PuffLoader css={override} color={"#cd0000"} size={200} loading={this.state.loading} />);
    }
    return (
      <div>
        {!name || name == 'modules' ?

          <div className="mvx-module-section-ui module-listing dashboard-wrapper">
            <div className="mvx-module-grid">

              {this.state.items.map((student1, index1) => (

                <div className="mvx-module-list-start">
                  <div className="mvx-module-list-container">
                    <div className="mvx-module-category-label">{student1.label}</div>
                    <div className="mvx-module-option-row">

                      {student1.options.map((student, index) => (
                        <div className="mvx-module-section-options-list">
                          <div className={`mvx-module-settings-box ${student.is_active ? 'active' : ''}`}>
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
                                    {company.is_active ? <div className="mvx-module-active-plugin-class"><img src={appLocalizer.right_logo} width="10" height="10" alt="Active" /></div> : <div className="inactive-plugin-class"><img src={appLocalizer.cross_logo} width="10" height="10" alt="Inactive" /></div>}
                                    <a href={company.plugin_link} className="mvx-third-party-plugin-link-class">{company.plugin_name}</a>
                                  </li>
                                )}
                            </ul>
                            <div className="mvx-module-current-status wp-clearfix">
                              {student.is_active ? <a href={student.mod_link} className="module-settings button button-secondary mvx-module-url-button">{appLocalizer.settings_text}</a> : ''}
                              <a href={student.doc_link} className="button button-secondary mvx-module-url-button">{appLocalizer.documentation_text}</a>
                              <div class="mvx-toggle-checkbox-content">
                                <input type="checkbox" className="mvx-toggle-checkbox" id={`mvx-toggle-switch-${student.id}`} name="modules[]" value={student.id} checked={student.is_active ? true : false} onChange={(e) => this.handleOnChange(e, index, student.plan, student.is_required_plugin_active, student.doc_id, this.state.items, index1, index, student.id)} />
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
              ))}
            </div>
          </div>

          : name == 'help'

            ?

            <div className="mvx-module-grid mvx-module-help-grid">
              <div className="mvx-module-settings-box">
                <header>
                  <h3>Next steps</h3>
                </header>
                <div className="mvx-module-settings-box-content">
                  <ul className="mvx-module-section-list-icon">
                    <li>
                      <a href="https://wc-marketplace.com/addons/" target="_blank">
                        <i className="dashicons dashicons-star-filled"></i>
                        <div className="mvx-module-help-content">
                          <strong>Upgrade to Pro</strong>
                          <p>Advanced Schema, Analytics and much more...</p>
                        </div>
                      </a>
                    </li>

                    <li>
                      <a href={appLocalizer.multivendor_migration_link} target="_blank">
                        <i className="dashicons dashicons-star-filled"></i>
                        <div className="mvx-module-help-content">
                          <strong>Migration</strong>
                          <p>How to Migrate Data from Your Previous Multivendor Plugin</p>
                        </div>
                      </a>
                    </li>

                  </ul>
                </div>
              </div>

              <div className="mvx-module-settings-box">
                <header>
                  <h3>Product Support</h3>
                </header>

                <div className="mvx-module-settings-box-content">
                  <ul className="mvx-module-section-list-icon">

                    <li>
                      <a href="https://wc-marketplace.com/knowledgebase/" target="_blank">
                        <i className="dashicons dashicons-star-filled"></i>
                        <div className="mvx-module-help-content">
                          <strong>Online Documentation</strong>
                          <p>Understand all the capabilities of MVX</p>
                        </div>
                      </a>
                    </li>

                    <li>
                      <a href="https://wc-marketplace.com/support-forum/" target="_blank">
                        <i className="dashicons dashicons-star-filled"></i>
                        <div className="mvx-module-help-content">
                          <strong>Support Forum</strong>
                          <p>Direct help from our qualified support team</p>
                        </div>
                      </a>
                    </li>

                  </ul>

                </div>

              </div>

            </div>


            :

            (<h3>There is nothing</h3>)

        }
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