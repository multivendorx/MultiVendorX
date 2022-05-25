import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import RingLoader from "react-spinners/RingLoader";

import {
  BrowserRouter as Router,
  Link,
  useLocation,
  withRouter,
  useParams,
  NavLink
} from "react-router-dom";

import DynamicForm from "../../../DynamicForm";

import { DateRangePicker } from 'rsuite';

import DataTable from 'react-data-table-component';


import HeaderSection from './class-mvx-page-header';

import BannerSection from './class-mvx-page-banner';
import TabSection from './class-mvx-page-tab';
import {
    LineChart,
    ResponsiveContainer,
    Legend, Tooltip,
    Line,
    XAxis,
    YAxis,
    CartesianGrid,
    BarChart,
    Bar
} from 'recharts';

import { CSVLink } from "react-csv";

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
      report_overview_data: [],
      columns_product: [
        {
            name: <div className="mvx-datatable-header-text">Product Title</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.title}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Admin Earning</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.admin_earning}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Vendor Earning</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.vendor_earning}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Gross Sales</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.gross}}></div>,
            sortable: true,
        }
      ],
      columns_vendor: [
        {
            name: <div className="mvx-datatable-header-text">Vendor Name</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.title}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Admin Earning</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.admin_earning}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Vendor Earning</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.vendor_earning}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Gross Sales</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.gross}}></div>,
            sortable: true,
        }
      ],
      columns_commission: [
        {
            name: <div className="mvx-datatable-header-text">Commission ID</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.commission_id}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Order ID</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.order_id}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Product</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.product}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Vendor</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.vendor}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Amount</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.amount}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Net Earning</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.net_earning}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Status</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.status}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Date</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.date}}></div>,
            sortable: true,
        },
      ],
      datacommission: [

      ],
      details_vendor: [],
      dataproductchart: [],
      details_product: [],
      store_date: '',
      store_product_select: '',
      store_vendor_select: '',
      product_report_chart_data: [],
      vendor_report_chart_data: [],
      columns_transaction: [
        {
            name: <div className="mvx-datatable-header-text">Status</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.status}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Date</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.date}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Type</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.type}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Reference ID</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.reference_id}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Credit</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.Credit}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Debit</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.Debit}}></div>,
            sortable: true,
        },
        {
            name: <div className="mvx-datatable-header-text">Balance</div>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.balance}}></div>,
            sortable: true,
        },
      ],
    };

    this.query = null;
    // when click on checkbox

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);

    this.Child = this.Child.bind(this);

    this.handleupdatereport = this.handleupdatereport.bind(this);

    this.handleChangeproduct_char_list = this.handleChangeproduct_char_list.bind(this);

    this.handlevendorsearch = this.handlevendorsearch.bind(this);

    this.handleproductsearch = this.handleproductsearch.bind(this);

    this.handleChangevendor_char_list = this.handleChangevendor_char_list.bind(this);
    
  }

  handleproductsearch(e) {
    
    this.setState({
      store_product_select: e.value,
    });


    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/get_report_overview_data`,
      data: {
        value: this.state.store_date,
        product: e.value
      }
    })
    .then( ( responce ) => {
      console.log('success');

      this.setState({
        report_overview_data: responce.data,
      });
    } );


  }

  handlevendorsearch(e) {
/*    console.log(e.value);
    return false;*/

    this.setState({
      store_vendor_select: e.value,
    });

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/get_report_overview_data`,
      data: {
        value: this.state.store_date,
        product: this.state.store_product_select,
        vendor: e.value
      }
    })
    .then( ( responce ) => {
      console.log('success');

      this.setState({
        report_overview_data: responce.data,
      });
    } );


  }

  handleChangeproduct_char_list(e) {
    var list_product_chart_list = [];
    e.selectedRows.map((data, index) => {
        list_product_chart_list[index] = data;
    })

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/export_csv_for_report_product_chart`,
      data: {
        product_list: list_product_chart_list
      }
    })
    .then( ( response ) => {
      this.setState({
        product_report_chart_data: response.data,
      });
    } );

  }

  handleChangevendor_char_list(e) {
    var list_vendor_chart_list = [];
    e.selectedRows.map((data, index) => {
        list_vendor_chart_list[index] = data;
    })

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/export_csv_for_report_vendor_chart`,
      data: {
        vendor_list: list_vendor_chart_list
      }
    })
    .then( ( response ) => {
      this.setState({
        vendor_report_chart_data: response.data,
      });
    } );
  }


  handleupdatereport(e) {
    //console.log(e);

    //console.log(this.state.store_product_select);

    this.setState({
      store_date: e,
    });

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/get_report_overview_data`,
      data: {
        value: e,
        product: this.state.store_product_select,
        vendor: this.state.store_vendor_select
      }
    })
    .then( ( responce ) => {
      console.log('success');
      //location.reload();
      
      this.setState({
        report_overview_data: responce.data,
      });
    } );

  }

  componentDidMount() {
    var formatter = (value) => `$${value}`;
    this.setState({
        formatter: formatter
      });

    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/fetch_report_overview_data`
      )
      .then(response => {
        this.setState({
          report_overview_data: response.data,
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
        url: `${appLocalizer.apiUrl}/mvx_module/v1/product_list_option`
      })
      .then(response => {
        this.setState({
          details_product: response.data,
        });
      })

      axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/all_commission`
    })
    .then(response => {
      this.setState({
        datacommission: response.data,
      });
    })
  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {
    let use_query = this.useQuery();
    return (
      <TabSection
        model={appLocalizer.mvx_all_backend_tab_list['marketplace-analytics']}
        query_name={use_query.get("name")}
        funtion_name={this}
        tab_description="no"
        horizontally
      />
    );
  }

Child({ name }) {
  return (
    <div className="mvx-analytics-wrapper">

      {
        name = !name ? appLocalizer.mvx_all_backend_tab_list['marketplace-analytics'][0]['modulename'] : name,

        name == appLocalizer.mvx_all_backend_tab_list['marketplace-analytics'][0]['modulename'] ?
          
            <div className="mvx-report-start-content">
              <div className="mvx-wrapper-date-picker pull-right mb-30">
                <div className="mvx-date-range">Date range:</div>
                <div className="mvx-report-datepicker"><DateRangePicker onChange={(e) => this.handleupdatereport(e)} /></div>
              </div>

              <div className="mvx-report-performance-content w-100">
                {this.state.report_overview_data.admin_overview ? 
                  <div class="mvx-text-with-line-wrapper">
                    <div class="mvx-report-text">
                      <span>{appLocalizer.report_page_string.performance}</span>
                    </div>
                  </div>
                : ''}

                <div className="mvx-wrapper-performance-content">
                {
                this.state.report_overview_data.admin_overview ? Object.entries(this.state.report_overview_data.admin_overview).map((data, index) => (
                  data[0] && data[0] != "sales_data_chart" ?
                    <div className="mvx-performance-wrapper-content">
                      <div className="mvx-labels">{data[1].label}</div>
                      <div className="mvx-wrap-price-and-percent">
                        <div className="mvx-price-display" dangerouslySetInnerHTML={{__html: data[1].value}}>
                        </div>
                        <div className="mvx-percent-show">0%</div>
                      </div>
                    </div>
                  : ''
                ))
                
                : ''
                }
                </div>
              </div>


              {this.state.report_overview_data.admin_overview && this.state.report_overview_data.admin_overview.sales_data_chart ?
              <div className="mvx-charts-graph-content">
                <div className="mvx-chart-text-and-bar-line-wrap">
                  <div class="mvx-text-with-line-wrapper chart-line">
                    <div class="mvx-report-text"><span>{appLocalizer.report_page_string.charts}</span></div>
                    <div className='mvx-select-all-bulk-wrap'>
                      <div className="mvx-analytics-overview-link"><Link to={`?page=mvx#&submenu=analytics&name=admin-overview&type=bar`}><i className="mvx-font icon-chart-bar"></i></Link></div>
                      <div className="mvx-analytics-overview-link"><Link to={`?page=mvx#&submenu=analytics&name=admin-overview&type=line`}><i className="mvx-font icon-chart-line"></i></Link></div>
                    </div>
                  </div>
                  <div className="mvx-bar-and-line-wrap hide"></div>
                </div>

                <div className="mvx-content-two-graph-wrap">
                  <div className="mvx-header-and-graph-wrap">
                    <div className="mvx-commission-order-details-text">First header</div>
                    <div className="mvx-chart-graph-visible">
                        {!this.useQuery().get('type') || this.useQuery().get('type') == 'line' ?
                          <ResponsiveContainer aspect={3}>
                            <LineChart
                              width={500}
                              height={300}
                              data={this.state.report_overview_data.admin_overview.sales_data_chart}
                              margin={{
                                top: 100,
                                right: 30,
                                left: 20,
                                bottom: 5,
                              }}
                              >
                              <CartesianGrid strokeDasharray="3 3" />
                              <XAxis dataKey="name" />
                              <YAxis tickFormatter={this.state.formatter} />
                              <Tooltip />
                              <Legend />
                              <Line type="monotone" dataKey="Net Sales" stroke="red" activeDot={{ r: 8 }} />
                            </LineChart>
                          </ResponsiveContainer>
                          :
                          <ResponsiveContainer aspect={3}>
                            <BarChart
                              width={500}
                              height={300}
                              data={this.state.report_overview_data.admin_overview.sales_data_chart}
                              margin={{
                                top: 5,
                                right: 30,
                                left: 20,
                                bottom: 5,
                              }}
                              >
                              <CartesianGrid strokeDasharray="3 3" />
                              <XAxis dataKey="Date" />
                              <YAxis tickFormatter={this.state.formatter} />
                              <Tooltip />
                              <Legend />
                              <Bar dataKey="Net Sales" fill="red"  />
                            </BarChart>
                          </ResponsiveContainer>
                        }

                    { /*<div className="mvx-pro-image-display"><img src="https://wc-marketplace.com//wp-content//uploads//2021//06//722x415-paypal-300x172.jpg"/></div> */}
                  </div>
                </div>

                <div className="mvx-header-and-graph-wrap">
                  <div className="mvx-commission-order-details-text">second header</div>
                    <div className="mvx-chart-graph-visible">
                      {!this.useQuery().get('type') || this.useQuery().get('type') == 'line' ?
                        <ResponsiveContainer aspect={3}>
                          <LineChart
                            width={500}
                            height={300}
                            data={this.state.report_overview_data.admin_overview.sales_data_chart}
                            margin={{
                              top: 100,
                              right: 30,
                              left: 20,
                              bottom: 5,
                            }}
                            >
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis dataKey="name" />
                            <YAxis tickFormatter={this.state.formatter} />
                            <Tooltip />
                            <Legend />
                            <Line type="monotone" dataKey="Net Sales" stroke="red" activeDot={{ r: 8 }} />
                          </LineChart>
                        </ResponsiveContainer>
                        :
                        <ResponsiveContainer aspect={3}>
                          <BarChart
                            width={500}
                            height={300}
                            data={this.state.report_overview_data.admin_overview.sales_data_chart}
                            margin={{
                              top: 5,
                              right: 30,
                              left: 20,
                              bottom: 5,
                            }}
                            >
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis dataKey="Date" />
                            <YAxis tickFormatter={this.state.formatter} />
                            <Tooltip />
                            <Legend />
                            <Bar dataKey="Net Sales" fill="red"  />
                          </BarChart>
                        </ResponsiveContainer>
                      }

                    { /*<div className="mvx-pro-image-display"><img src="https://wc-marketplace.com//wp-content//uploads//2021//06//722x415-paypal-300x172.jpg"/></div> */}
                  </div>
                </div>
              </div>
            </div>

            : '' }

            <div className="mvx-report-leaderboard-content">
              <div class="mvx-text-with-line-wrapper">
                <div class="mvx-report-text w-100 mr-0"><span>{appLocalizer.report_page_string.leaderboards}</span></div>
              </div>

              <div className="mvx-analytic-details-wrap">
                <div className="mvx-commission-order-details-text">Vendor Details</div>
                <div className="mvx-backend-datatable-wrapper default-table">
                  <DataTable
                    columns={this.state.columns_vendor}
                    data={this.state.report_overview_data.vendor ? this.state.report_overview_data.vendor.vendor_report_datatable : this.state.dataproductchart}
                    selectableRows
                    pagination
                  />
                </div>
              </div>

              <div className="mvx-analytic-details-wrap">
                <div className="mvx-commission-order-details-text">Commission Details</div>
                <div className="mvx-backend-datatable-wrapper default-table">
                  <DataTable
                    columns={this.state.columns_commission}
                    data={this.state.datacommission}
                    selectableRows
                    pagination
                  />
                </div>
              </div>

            </div>
          </div>

            :

            name == appLocalizer.mvx_all_backend_tab_list['marketplace-analytics'][1]['modulename'] ?

            <div className="mvx-report-start-content">

              <div className="mvx-date-and-show-wrapper justify-between mb-25">
                <div className="mvx-wrapper-date-picker">
                  <div className="mvx-date-range">Date range:</div>
                  <div className="mvx-report-datepicker"><DateRangePicker onChange={(e) => this.handleupdatereport(e)} /></div>
                </div>
                <div className="mvx-vendor-wrapper-show-specific">
                  <div className="mvx-date-range">Show:</div>
                  <Select placeholder={appLocalizer.report_page_string.choose_vendor} options={this.state.details_vendor} isClearable={true} className="mvx-module-vendor-section-nav-child-data" onChange={(e) => this.handlevendorsearch(e)} />
                </div>
              </div>

              <div className="mvx-report-performance-content">
                <div class="mvx-text-with-line-wrapper">
                  <div class="mvx-report-text w-100 mr-0"><span>{appLocalizer.report_page_string.performance}</span></div>
                </div>

                <div className="mvx-wrapper-performance-content col-type-3">
                {
                  this.state.report_overview_data.admin_overview ? Object.entries(this.state.report_overview_data.vendor).map((data, index) => (
                    data[0] && data[0] != "sales_data_chart"  ?
                      data[0] != "vendor_report_datatable" ? 
                       <div className="mvx-performance-wrapper-content">
                          <div>{data[1].label}</div>
                            <div className="mvx-wrap-price-and-percent">
                              <div className="mvx-price-display" dangerouslySetInnerHTML={{__html: data[1].value}}>
                              </div>
                              <div className="mvx-percent-show">0%</div>
                            </div>
                        </div>
                      : ''
                    : ''
                  ))
                : ''
                }
              </div>
            </div>

            {this.state.report_overview_data.vendor && this.state.report_overview_data.vendor.sales_data_chart ?
              <div className="mvx-charts-graph-content">
                <div className="mvx-chart-text-and-bar-line-wrap">
                  <div class="mvx-text-with-line-wrapper chart-line">
                    <div class="mvx-report-text"><span>{appLocalizer.report_page_string.charts}</span></div>
                    <div className='mvx-select-all-bulk-wrap'>
                    <div className="mvx-bar-chart"><Link to={`?page=mvx#&submenu=analytics&name=vendor&type=bar`}><i className="mvx-font icon-chart-bar"></i></Link></div>
                    <div className="mvx-line-chart"><Link to={`?page=mvx#&submenu=analytics&name=vendor&type=line`}><i className="mvx-font icon-chart-line"></i></Link></div>
                  </div>
                </div>
              </div>

              <div className="mvx-chart-graph-visible">
                {!this.useQuery().get('type') || this.useQuery().get('type') == 'line' ?
                <ResponsiveContainer width="100%" height="100%" aspect={3}>
                  <LineChart
                    width={500}
                    height={300}
                    data={this.state.report_overview_data.vendor.sales_data_chart}
                    margin={{
                      top: 100,
                      right: 30,
                      left: 20,
                      bottom: 5,
                    }}
                    >
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="Date" />
                    <YAxis tickFormatter={this.state.formatter} />
                    <Tooltip />
                    <Legend />
                    <Line dataKey="Net Sales" stroke="red" activeDot={{ r: 8 }} />

                    <Line dataKey="Order Count"
                          stroke="black" activeDot={{ r: 8 }} />
                      
                    <Line dataKey="Item Sold"
                          stroke="green" activeDot={{ r: 8 }} />

                  </LineChart>
                </ResponsiveContainer>

                :

                <ResponsiveContainer width="100%" height="100%" aspect={3}>
                  <BarChart
                    width={500}
                    height={300}
                    data={this.state.report_overview_data.vendor.sales_data_chart}
                    margin={{
                      top: 5,
                      right: 30,
                      left: 20,
                      bottom: 5,
                    }}
                    >
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="Date" />
                    <YAxis tickFormatter={this.state.formatter} />
                    <Tooltip />
                    <Legend />
                    <Bar dataKey="Net Sales" fill="red"  />

                    <Bar dataKey="Order Count"
                    fill="black"  />

                    <Bar dataKey="Item Sold"
                    fill="green"  />

                  </BarChart>
                </ResponsiveContainer>
                }
              </div>
            </div>

            : '' }

            <div className="mvx-report-csv-and-chart">
              {this.state.report_overview_data.vendor && this.state.report_overview_data.vendor.vendor_report_datatable ? 
                <div className="mvx-text-with-line-wrapper svg-line">
                  <div className="mvx-report-text"><span>Vendor</span></div>
                  <div class="mvx-select-all-bulk-wrap">
                    <CSVLink data={this.state.vendor_report_chart_data} headers={appLocalizer.report_vendor_header} filename={"Report_vendor.csv"} className="button-csv-primary"><i className="mvx-font icon-download"></i>{appLocalizer.report_page_string.download_csv}</CSVLink> 
                  </div>
                </div>
              : '' }
              <div className="mvx-backend-datatable-wrapper default-table">
                <DataTable
                  columns={this.state.columns_vendor}
                  data={this.state.report_overview_data.vendor ? this.state.report_overview_data.vendor.vendor_report_datatable : this.state.dataproductchart}
                  selectableRows
                  onSelectedRowsChange={this.handleChangevendor_char_list}
                  pagination
                />
              </div>
            </div>
          </div>

          :

            name == appLocalizer.mvx_all_backend_tab_list['marketplace-analytics'][2]['modulename'] ?
            
              <div className="mvx-report-start-content">

                <div className="mvx-date-and-show-wrapper justify-between mb-25">
                  <div className="mvx-wrapper-date-picker">
                    <div className="mvx-date-range">Date range:</div>
                    <div className="mvx-report-datepicker"><DateRangePicker onChange={(e) => this.handleupdatereport(e)} /></div>
                  </div>
                
                  <div className="mvx-product-wrapper-show-specific">
                    <div className="mvx-date-range">Show:</div>
                    <Select placeholder={appLocalizer.report_page_string.choose_product} options={this.state.details_product} isClearable={true} className="mvx-module-section-nav-child-data" onChange={(e) => this.handleproductsearch(e)} />
                  </div>
                </div>

                <div className="mvx-report-performance-content">
                  <div class="mvx-text-with-line-wrapper">
                    <div class="mvx-report-text w-100 mr-0"><span>{appLocalizer.report_page_string.performance}</span></div>
                  </div>

                  <div className="mvx-wrapper-performance-content col-type-3">
                  {
                  this.state.report_overview_data.admin_overview ? Object.entries(this.state.report_overview_data.product).map((data, index) => (
                    data && data[1].label ? 
                      <div className="mvx-performance-wrapper-content">
                        <div>{data[1].label}</div>
                          <div className="mvx-wrap-price-and-percent">
                            <div className="mvx-price-display" dangerouslySetInnerHTML={{__html: data[1].value}}>
                            </div>
                            <div className="mvx-percent-show">0%</div>
                          </div>
                      </div>
                    : '' 
                  ))
                  : ''
                  }
                </div>
              </div>

              
              {this.state.report_overview_data.product && this.state.report_overview_data.product.sales_data_chart ?
              <div className="mvx-charts-graph-content">
                <div className="mvx-chart-text-and-bar-line-wrap">
                  <div class="mvx-text-with-line-wrapper chart-line">
                    <div class="mvx-report-text"><span>{appLocalizer.report_page_string.charts}</span></div>
                    <div class="mvx-select-all-bulk-wrap">
                    <div className="mvx-bar-chart"><Link to={`?page=mvx#&submenu=analytics&name=product&type=bar`}><i className="mvx-font icon-chart-bar"></i></Link></div>
                    <div className="mvx-line-chart"><Link to={`?page=mvx#&submenu=analytics&name=product&type=line`}><i className="mvx-font icon-chart-line"></i></Link></div>
                    </div>
                  </div>
                </div>

                <div className="mvx-chart-graph-visible">
                {!this.useQuery().get('type') || this.useQuery().get('type') == 'line' ?
                  <ResponsiveContainer aspect={3}>
                    <LineChart
                      data={this.state.report_overview_data.product.sales_data_chart}
                      margin={{
                        top: 100,
                        right: 30,
                        left: 20,
                        bottom: 5,
                      }}
                      >
                      <CartesianGrid strokeDasharray="3 3" />
                      <XAxis dataKey="Date" />
                      <YAxis tickFormatter={this.state.formatter} />
                      <Tooltip />
                      <Legend />
                      <Line dataKey="Net Sales" stroke="red" activeDot={{ r: 8 }} />

                      <Line dataKey="Order Count"
                            stroke="black" activeDot={{ r: 8 }} />
                        
                      <Line dataKey="Item Sold"
                            stroke="green" activeDot={{ r: 8 }} />

                    </LineChart>
                  </ResponsiveContainer>
                  : 
                  <ResponsiveContainer width="100%" height="100%" aspect={3}>
                    <BarChart
                      width={500}
                      height={300}
                      data={this.state.report_overview_data.product.sales_data_chart}
                      margin={{
                        top: 5,
                        right: 30,
                        left: 20,
                        bottom: 5,
                      }}
                      >
                      <CartesianGrid strokeDasharray="3 3" />
                      <XAxis dataKey="Date" />
                      <YAxis tickFormatter={this.state.formatter} />
                      <Tooltip />
                      <Legend />
                      <Bar dataKey="Net Sales" fill="red"  />
                      <Bar dataKey="Order Count" fill="black"  />
                      <Bar dataKey="Item Sold" fill="green"  />
                    </BarChart>
                  </ResponsiveContainer>
                }
                </div>
              </div> 
              : '' }

              <div className="mvx-report-csv-and-chart">
                {this.state.report_overview_data.product && this.state.report_overview_data.product.product_report_datatable ? 
                  <div class="mvx-text-with-line-wrapper svg-line">
                    <div class="mvx-report-text">
                      <span>Products</span>
                    </div>
                    <div class="mvx-select-all-bulk-wrap">
                      <CSVLink data={this.state.product_report_chart_data} headers={appLocalizer.report_product_header} filename={"Report_product.csv"} className="button-csv-primary"><i className="mvx-font icon-download"></i>{appLocalizer.report_page_string.download_csv}</CSVLink> 
                    </div>
                  </div>
                : '' }
                <div className="mvx-backend-datatable-wrapper default-table">
                  <DataTable
                    columns={this.state.columns_product}
                    data={this.state.report_overview_data.product ? this.state.report_overview_data.product.product_report_datatable : this.state.dataproductchart}
                    selectableRows
                    onSelectedRowsChange={this.handleChangeproduct_char_list}
                    pagination
                  />
                </div>
              </div>
            </div>

            :



            name == appLocalizer.mvx_all_backend_tab_list['marketplace-analytics'][3]['modulename'] ?
              <div className="mvx-report-start-content">
                <div className="mvx-date-and-show-wrapper justify-between mb-25">
                  <div className="mvx-wrapper-date-picker">
                    <div className="mvx-date-range">Date range:</div>
                    <div className="mvx-report-datepicker"><DateRangePicker onChange={(e) => this.handleupdatereport(e)} /></div>
                  </div>
                
                  <div className="mvx-transaction-wrapper-show-specific">
                    <div className="mvx-date-range">{appLocalizer.report_page_string.vendor_select}</div>
                    <Select placeholder={appLocalizer.report_page_string.choose_vendor} options={this.state.details_vendor} isClearable={true} className="mvx-module-section-nav-child-data" onChange={(e) => this.handlevendorsearch(e)} />
                  </div>
                </div>

                <div className="mvx-backend-datatable-wrapper default-table">
                  <DataTable
                    columns={this.state.columns_transaction}
                    data={this.state.report_overview_data.banking_overview ? this.state.report_overview_data.banking_overview : this.state.dataproductchart}
                    selectableRows
                    pagination
                  />
                </div>
              </div>
            : ''
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