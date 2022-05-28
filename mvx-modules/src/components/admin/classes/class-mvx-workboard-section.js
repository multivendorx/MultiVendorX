import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';
import Select from 'react-select';
import PuffLoader from "react-spinners/PuffLoader";
import { css } from "@emotion/react";

import { ReactSortable } from "react-sortablejs";


//import DynamicDataTable from "@langleyfoxall/react-dynamic-data-table";

import styled from 'styled-components'

import {
  BrowserRouter as Router,
  Link,
  useLocation,
  withRouter,
  useParams,
  NavLink
} from "react-router-dom";


import DynamicForm from "../../../DynamicForm";
import DataTable from 'react-data-table-component';

import HeaderSection from './class-mvx-page-header';
import TabSection from './class-mvx-page-tab';

const override = css`
  display: block;
  margin: 0 auto;
  border-color: green;
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
      product_list_option: '',
      current: {},
      bulkselectlist: [],
      bulkselectreviewlist: [],
      bulkselectabuselist: [],
      show_vendor_name: '',

      display_announcement: [],
      display_pending_announcement: [],
      display_published_announcement: [],
      display_all_announcement: [],


      display_all_knowladgebase: [],
      display_publish_knowladgebase: [],
      display_pending_knowladgebase: [],

      knowledge_data_fileds: [],
      edit_announcement_fileds: [],
      edit_knowledgebase_fileds: [],
      display_list_knowladgebase: [],
      list_of_pending_vendor_product: [],
      list_of_pending_vendor: [],
      list_of_pending_vendor_coupon: [],
      list_of_pending_transaction: [],
      list_of_pending_question: [],


      list_of_store_review: [],
      list_of_report_abuse: [],

      columns_announcement_new: [],

      columns_knowledgebase_new: [],
      columns_questions_new: [],
      columns_store_review: [],
      columns_report_abuse: [],

      pending_product_check: [],
      pending_user_check: [],
      pending_coupon_check: [],
      pending_transaction_check: [],
      pending_question_check: [],
      list_of_publish_question: [],

      pending_parent_product_check: false,
      pending_parent_user_check: false,
      pending_parent_coupon_check: false,
      pending_parent_transaction_check: false,
      pending_parent_question_check: false,


      pending_transaction_loding_end: false,
      pending_product_loding_end: false,
      pending_user_loding_end: false,
      pending_coupon_loding_end: false,
      pending_question_loding_end: false,


      pending_tranaction: [
        {
          name: <div className="mvx-datatable-header-text">Vendor Name</div>,
          selector: row => <div dangerouslySetInnerHTML={{ __html: row.vendor }}></div>,
          sortable: true,
        },
        {
          name: <div className="mvx-datatable-header-text">Commission</div>,
          selector: row => <div dangerouslySetInnerHTML={{ __html: row.commission }}></div>,
          sortable: true,
        },
        {
          name: <div className="mvx-datatable-header-text">Amount</div>,
          selector: row => <div dangerouslySetInnerHTML={{ __html: row.amount }}></div>,
          sortable: true,
        },
        {
          name: <div className="mvx-datatable-header-text">Account Detail</div>,
          selector: row => <div dangerouslySetInnerHTML={{ __html: row.account_details }}></div>,
          sortable: true,
        },
      ],

    };

    this.query = null;
    // when click on checkbox

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);

    this.Child = this.Child.bind(this);

    this.handle_post_retrive_status = this.handle_post_retrive_status.bind(this);

    this.handle_post_bulk_status = this.handle_post_bulk_status.bind(this);

    this.onSelectedRowsChange = this.onSelectedRowsChange.bind(this);

    this.handle_work_board_chenage = this.handle_work_board_chenage.bind(this);


    this.handlePostDismiss = this.handlePostDismiss.bind(this);

    // pending product todo action
    this.handle_product_request_by_vendors = this.handle_product_request_by_vendors.bind(this);

    // trigger questions
    this.handle_question_request_by_vendors = this.handle_question_request_by_vendors.bind(this);

    // trigger counpon todo
    this.handle_coupon_request_by_vendors = this.handle_coupon_request_by_vendors.bind(this);

    //trigger todo user
    this.handle_user_request_by_vendors = this.handle_user_request_by_vendors.bind(this);


    // individual checkbox trigger
    this.handle_todo_checkbox_chenage = this.handle_todo_checkbox_chenage.bind(this);
    this.handle_todo_user_chenage = this.handle_todo_user_chenage.bind(this);
    this.handle_todo_coupon_chenage = this.handle_todo_coupon_chenage.bind(this);
    this.handle_todo_transaction_chenage = this.handle_todo_transaction_chenage.bind(this);
    this.handle_todo_question_checkbox_chenage = this.handle_todo_question_checkbox_chenage.bind(this);



    this.handle_task_board_bulk_chenage = this.handle_task_board_bulk_chenage.bind(this);

    this.handle_parent_todo_checkbox_chenage = this.handle_parent_todo_checkbox_chenage.bind(this);



    this.handle_parent_user_todo_checkbox_chenage = this.handle_parent_user_todo_checkbox_chenage.bind(this);
    this.handle_parent_coupon_todo_checkbox_chenage = this.handle_parent_coupon_todo_checkbox_chenage.bind(this);
    this.handle_parent_transaction_todo_checkbox_chenage = this.handle_parent_transaction_todo_checkbox_chenage.bind(this);
    this.handle_parent_question_todo_checkbox_chenage = this.handle_parent_question_todo_checkbox_chenage.bind(this);


    this.handle_question_search = this.handle_question_search.bind(this);

    this.handleReviewDismiss = this.handleReviewDismiss.bind(this);

    this.handle_review_bulk_status = this.handle_review_bulk_status.bind(this);

    this.handleselectreviews = this.handleselectreviews.bind(this);

    this.handle_search_vendor_review = this.handle_search_vendor_review.bind(this);

    this.handleselectabuse = this.handleselectabuse.bind(this);

    this.handle_vendor_search_abuse = this.handle_vendor_search_abuse.bind(this);
    this.handle_product_search_abuse = this.handle_product_search_abuse.bind(this);

    this.handleAbuseDismiss = this.handleAbuseDismiss.bind(this);

  }

  handleAbuseDismiss(reason, product, vendor) {
    if (confirm("Are you sure to delete?")) {
      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_delete`,
        data: {
          reason: reason,
          product: product,
          vendor: vendor,
        }
      })
        .then((responce) => {
          this.setState({
            list_of_report_abuse: response.data,
          });
        });
    }
  }

  handle_vendor_search_abuse(e) {
    if (e) {
      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_details`, {
        params: { vendor_id: e.value }
      })
        .then(response => {
          this.setState({
            list_of_report_abuse: response.data,
          });
        })
    } else {
      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_details`
      )
        .then(response => {
          this.setState({
            list_of_report_abuse: response.data,
          });
        })
    }
  }

  handle_product_search_abuse(e) {
    if (e) {
      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_details`, {
        params: { product_id: e.value }
      })
        .then(response => {
          this.setState({
            list_of_report_abuse: response.data,
          });
        })
    } else {
      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_details`
      )
        .then(response => {
          this.setState({
            list_of_report_abuse: response.data,
          });
        })
    }
  }

  handleselectabuse(e) {
    this.setState({
      bulkselectabuselist: e.selectedRows,
    })
  }

  handle_search_vendor_review(e) {
    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/search_review`,
      data: {
        value: e.target.value,
      }
    })
      .then((responce) => {
        this.setState({
          list_of_store_review: responce.data,
        });
      });
  }

  handle_review_bulk_status(e) {
    if (confirm("Are you sure to delete?")) {
      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/delete_review`,
        data: {
          id: this.state.bulkselectreviewlist,
        }
      })
        .then((responce) => {

          this.setState({
            list_of_store_review: responce.data,
          });

        });
    }

  }

  handleselectreviews(e) {
    this.setState({
      bulkselectreviewlist: e.selectedRows,
    })

  }

  handleReviewDismiss(id) {
    if (confirm("Are you sure to delete?")) {
      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/delete_review`,
        data: {
          id: id,
        }
      })
        .then((responce) => {

          this.setState({
            list_of_store_review: responce.data,
          });

        });
    }
  }


  handle_question_search(e) {
    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/search_question_ans`,
      data: {
        value: e.target.value,
      }
    })
      .then((responce) => {
        this.setState({
          list_of_publish_question: responce.data,
        });
      });
  }

  handle_task_board_bulk_chenage(e, type) {
    if (type == 'product_approval') {

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/bulk_todo_pending_product`,
        data: {
          product_list: this.state.pending_product_check,
          value: e.value,
          type: type
        }
      })
        .then((responce) => {
          this.setState({
            list_of_pending_vendor_product: responce.data,
          });
        });

    } else if (type == 'user_approval') {

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/bulk_todo_pending_product`,
        data: {
          user_list: this.state.pending_user_check,
          value: e.value,
          type: type
        }
      })
        .then((responce) => {
          this.setState({
            list_of_pending_vendor_product: responce.data,
          });
        });

    } else if (type == 'coupon_approval') {

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/bulk_todo_pending_product`,
        data: {
          coupon_list: this.state.pending_coupon_check,
          value: e.value,
          type: type
        }
      })
        .then((responce) => {
          this.setState({
            list_of_pending_vendor_product: responce.data,
          });
        });

    } else if (type == 'transaction_approval') {

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/bulk_todo_pending_product`,
        data: {
          transaction_list: this.state.pending_transaction_check,
          value: e.value,
          type: type
        }
      })
        .then((responce) => {
          this.setState({
            list_of_pending_vendor_product: responce.data,
          });
        });

    } else if (type == 'question_approval') {

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/bulk_todo_pending_product`,
        data: {
          product_list: this.state.pending_question_check,
          value: e.value,
          type: type
        }
      })
        .then((responce) => {
          this.setState({
            list_of_pending_question: responce.data,
          });
        });

    }

  }


  /**************************  Parent checkbox trigger **************************************/
  handle_parent_todo_checkbox_chenage(e) {
    if (e.target.checked) {
      this.setState({
        pending_parent_product_check: true,
        pending_product_check: new Array(this.state.pending_product_check.length).fill(true)
      });
    } else {
      this.setState({
        pending_parent_product_check: false,
        pending_product_check: new Array(this.state.pending_product_check.length).fill(false)
      });
    }
  }

  handle_parent_user_todo_checkbox_chenage(e) {
    if (e.target.checked) {
      this.setState({
        pending_parent_user_check: true,
        pending_user_check: new Array(this.state.pending_user_check.length).fill(true)
      });
    } else {
      this.setState({
        pending_parent_user_check: false,
        pending_user_check: new Array(this.state.pending_user_check.length).fill(false)
      });
    }
  }

  handle_parent_coupon_todo_checkbox_chenage(e) {
    if (e.target.checked) {
      this.setState({
        pending_parent_coupon_check: true,
        pending_coupon_check: new Array(this.state.pending_coupon_check.length).fill(true)
      });
    } else {
      this.setState({
        pending_parent_coupon_check: false,
        pending_coupon_check: new Array(this.state.pending_coupon_check.length).fill(false)
      });
    }
  }

  handle_parent_transaction_todo_checkbox_chenage(e) {
    if (e.target.checked) {
      this.setState({
        pending_parent_transaction_check: true,
        pending_transaction_check: new Array(this.state.pending_transaction_check.length).fill(true)
      });
    } else {
      this.setState({
        pending_parent_transaction_check: false,
        pending_transaction_check: new Array(this.state.pending_transaction_check.length).fill(false)
      });
    }
  }

  handle_parent_question_todo_checkbox_chenage(e) {
    if (e.target.checked) {
      this.setState({
        pending_parent_question_check: true,
        pending_question_check: new Array(this.state.pending_question_check.length).fill(true)
      });
    } else {
      this.setState({
        pending_parent_question_check: false,
        pending_question_check: new Array(this.state.pending_question_check.length).fill(false)
      });
    }
  }



  /**************************  Parent checkbox trigger end **************************************/


  // individual checkbox trigger
  handle_todo_checkbox_chenage(e, id, position) {

    var updatedCheckedState = this.state.pending_product_check.map((item, index) =>
      index === position ? !item : item
    );

    this.setState({
      pending_product_check: updatedCheckedState,
    });
  }

  handle_todo_user_chenage(e, id, position) {

    var updatedCheckedState = this.state.pending_user_check.map((item, index) =>
      index === position ? !item : item
    );

    this.setState({
      pending_user_check: updatedCheckedState,
    });
  }

  handle_todo_coupon_chenage(e, id, position) {

    var updatedCheckedState = this.state.pending_coupon_check.map((item, index) =>
      index === position ? !item : item
    );

    this.setState({
      pending_coupon_check: updatedCheckedState,
    });
  }

  handle_todo_transaction_chenage(e, id, position) {

    var updatedCheckedState = this.state.pending_transaction_check.map((item, index) =>
      index === position ? !item : item
    );

    this.setState({
      pending_transaction_check: updatedCheckedState,
    });
  }

  handle_todo_question_checkbox_chenage(e, id, position) {

    var updatedCheckedState = this.state.pending_question_check.map((item, index) =>
      index === position ? !item : item
    );

    this.setState({
      pending_question_check: updatedCheckedState,
    });
  }















  handle_question_request_by_vendors(e, question_id, product_id, type) {
    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/approve_dismiss_pending_question`,
      data: {
        question_id: question_id,
        product_id: product_id,
        type: type
      }
    })
      .then((responce) => {
        this.setState({
          list_of_pending_question: responce.data,
        });
      });
  }

  handle_coupon_request_by_vendors(e, id, type) {

    axios({
      method: 'post',
      url: `${appLocalizer.apiUrl}/mvx_module/v1/dismiss_and_approve_vendor_coupon`,
      data: {
        coupon_id: id,
        type: type
      }
    })
      .then((responce) => {
        this.setState({
          list_of_pending_vendor_coupon: responce.data,
        });
      });

  }

  handle_user_request_by_vendors(e, id, type) {
    if (type == 'dismiss') {

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/dismiss_vendor`,
        data: {
          vendor_id: id
        }
      })
        .then((responce) => {
          this.setState({
            list_of_pending_vendor: responce.data,
          });
        });

    } else if (type == 'approve') {

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/approve_vendor`,
        data: {
          vendor_id: id
        }
      })
        .then((responce) => {
          this.setState({
            list_of_pending_vendor: responce.data,
          });
        });

    }
  }


  handle_product_request_by_vendors(e, product_id, vendor_id, type) {
    if (type == 'dismiss') {
      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/dismiss_requested_vendors_query`,
        data: {
          product_id: product_id,
          type: type,
          vendor_id: vendor_id
        }
      })
        .then((responce) => {

          this.setState({
            list_of_pending_vendor_product: responce.data,
          });

        });
    } else if (type == 'approve') {

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/approve_product`,
        data: {
          product_id: product_id,
          type: type,
          vendor_id: vendor_id
        }
      })
        .then((responce) => {
          this.setState({
            list_of_pending_vendor_product: responce.data,
          });
        });

    }
  }


  handlePostDismiss(e, title) {

    if (confirm("Are you sure to delete?")) {
      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/delete_post_details`,
        data: {
          ids: e,
          title: title
        }
      })
        .then((responce) => {

          this.setState({
            display_announcement: responce.data,
          });

        });
    }

  }

  handle_work_board_chenage(e, type) {
    if (type == 'announcement' && e) {
      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/update_custom_post_status`,
        data: {
          ids: this.state.bulkselectlist,
          value: e.value
        }
      })
        .then((responce) => {
        });



    }
  }


  onSelectedRowsChange(e) {
    this.setState({
      bulkselectlist: e.selectedRows,
    });
  }

  handle_post_retrive_status(e, status, type) {

    if (type == 'announcement') {
      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`, {
        params: { status: status }
      })
        .then(response => {
          this.setState({
            display_announcement: response.data,
          });
        })
    } else if (type == 'knowladgebase') {

      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`, {
        params: { status: status }
      })
        .then(response => {
          this.setState({
            display_list_knowladgebase: response.data,
          });
        })

    }

  }


  handle_post_bulk_status(e, type) {

    if (type == 'announcement') {
      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/search_announcement`,
        data: {
          ids: this.state.bulkselectlist,
          value: e.target.value
        }
      })
        .then((responce) => {
          this.setState({
            display_announcement: responce.data,
          });

        });
    } else if (type == 'knowladgebase') {

      axios({
        method: 'post',
        url: `${appLocalizer.apiUrl}/mvx_module/v1/search_knowledgebase`,
        data: {
          value: e.target.value
        }
      })
        .then((responce) => {
          this.setState({
            display_list_knowladgebase: responce.data,
          });

        });

    }
  }

  componentDidMount() {

    /***********  Announcement  ******************/
    // all announcement
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`
    )
      .then(response => {
        this.setState({
          display_announcement: response.data,
        });
      })

    // pending announcement
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`, {
      params: { status: 'pending' }
    })
      .then(response => {
        this.setState({
          display_pending_announcement: response.data,
        });
      })

    // published announcement
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`, {
      params: { status: 'publish' }
    })
      .then(response => {
        this.setState({
          display_published_announcement: response.data,
        });
      })

    // all announcement count
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`, {
      params: { status: 'all' }
    })
      .then(response => {
        this.setState({
          display_all_announcement: response.data,
        });
      })
    /***********  Announcement  ******************/


    /***********  Knowledgebase  **************/

    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`
    )
      .then(response => {
        this.setState({
          display_list_knowladgebase: response.data,
        });
      })


    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`, {
      params: { status: 'all' }
    })
      .then(response => {
        this.setState({
          display_all_knowladgebase: response.data,
        });
      })


    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`, {
      params: { status: 'publish' }
    })
      .then(response => {
        this.setState({
          display_publish_knowladgebase: response.data,
        });
      })


    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`, {
      params: { status: 'pending' }
    })
      .then(response => {
        this.setState({
          display_pending_knowladgebase: response.data,
        });
      })




    /******** Knowledgebase end  ************/


    // pending details
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_question`
    )
      .then(response => {
        var all_pending_data_checkbox = new Array(response.data.length).fill(false);
        this.setState({
          list_of_pending_question: response.data,
          pending_question_check: all_pending_data_checkbox,
          pending_question_loding_end: true
        });
      })


    // publish details
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_question`, {
      params: { status: 'publish' }
    })
      .then(response => {
        this.setState({
          list_of_publish_question: response.data,
        });
      })


    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_transaction`
    )
      .then(response => {
        this.setState({
          list_of_pending_transaction: response.data,
          pending_transaction_loding_end: true,
          pending_transaction_check: new Array(response.data.length).fill(false)
        });
      })


    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_vendor_coupon`
    )
      .then(response => {
        this.setState({
          list_of_pending_vendor_coupon: response.data,
          pending_coupon_loding_end: true,
          pending_coupon_check: new Array(response.data.length).fill(false)
        });
      })


    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_vendor`
    )
      .then(response => {
        var all_pending_data_checkbox = new Array(response.data.length).fill(false);
        this.setState({
          list_of_pending_vendor: response.data,
          pending_user_loding_end: true,
          pending_user_check: all_pending_data_checkbox,
        });
      })

    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_vendor_product`
    )
      .then(response => {
        var all_pending_product_checkbox = new Array(response.data.length).fill(false);

        this.setState({
          list_of_pending_vendor_product: response.data,
          pending_product_loding_end: true,
          pending_product_check: all_pending_product_checkbox
        });
      })





    // fetch review
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/list_of_store_review`
    )
      .then(response => {
        this.setState({
          list_of_store_review: response.data,
        });
      })


    // fetch review
    axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_details`
    )
      .then(response => {
        this.setState({
          list_of_report_abuse: response.data,
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

    // product list
    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/product_list_option`
    })
      .then(response => {
        this.setState({
          product_list_option: response.data,
        });
      })


  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {

    // update announcement table when clock on announcement tab
    if (new URLSearchParams(window.location.hash).get("name") == 'announcement') {
      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`
      )
        .then(response => {
          this.state.display_announcement = response.data;
        })

    }

    // update announcement table end


    let use_query = this.useQuery();
    return (
      <TabSection
        model={appLocalizer.mvx_all_backend_tab_list['marketplace-workboard']}
        query_name={use_query.get("name")}
        funtion_name={this}
        horizontally
        no_banner
      />
    );
  }

  Child({ name }) {















    var get_current_name = this.useQuery();


    if (!get_current_name.get("AnnouncementID")) {
      this.state.edit_announcement_fileds = [];
    }

    if (!get_current_name.get("knowladgebaseID")) {
      this.state.edit_knowledgebase_fileds = [];
    }

    if (get_current_name.get("AnnouncementID")) {
      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/update_announcement_display`, {
        params: { announcement_id: get_current_name.get("AnnouncementID") }
      })
        .then(response => {
          if (response.data && this.state.edit_announcement_fileds.length == 0) {
            this.setState({
              edit_announcement_fileds: response.data,
            });
          }
        })
    }

    if (get_current_name.get("knowladgebaseID")) {
      axios.get(
        `${appLocalizer.apiUrl}/mvx_module/v1/update_knowladgebase_display`, {
        params: { knowladgebase_id: get_current_name.get("knowladgebaseID") }
      })
        .then(response => {
          if (response.data && this.state.edit_knowledgebase_fileds.length == 0) {
            this.setState({
              edit_knowledgebase_fileds: response.data,
            });
          }
        })
    }








    // Display table column and row slection
    if (this.state.columns_announcement_new.length == 0 && new URLSearchParams(window.location.hash).get("name") == 'announcement') {
      appLocalizer.columns_announcement.map((data_ann, index_ann) => {
        var data_selector = '';
        var set_for_dynamic_column = '';
        data_selector = data_ann['selector_choice'];
        data_ann.selector = row => <div dangerouslySetInnerHTML={{ __html: row[data_selector] }}></div>;


        data_ann.cell ? data_ann.cell = (row) => <div className="mvx-vendor-action-icon">

          <a href={row.link}><i className="mvx-font icon-edit"></i></a>
          <div onClick={() => this.handlePostDismiss(row.id, row.type)} id={row.id}><i className="mvx-font icon-no"></i></div>

        </div> : '';


        this.state.columns_announcement_new[index_ann] = data_ann
        set_for_dynamic_column = this.state.columns_announcement_new;
        this.setState({
          columns_announcement_new: set_for_dynamic_column,
        });
      }
      )
    }
    // Display table column and row slection end


    // Display table column and row slection for questions
    if (this.state.columns_questions_new.length == 0 && new URLSearchParams(window.location.hash).get("name") == 'question-ans') {
      appLocalizer.columns_questions.map((data_ques, index_ques) => {
        var data_selector_question = '';
        var set_for_dynamic_column_question = '';
        data_selector_question = data_ques['selector_choice'];
        data_ques.selector = row => <div dangerouslySetInnerHTML={{ __html: row[data_selector_question] }}></div>;


        data_ques.cell ? data_ques.cell = (row) => <div className="mvx-vendor-action-icon">

          <a href={row.link}><i className="mvx-font icon-edit"></i></a>
          <div onClick={() => this.handlePostDismiss(row.id, row.type)} id={row.id}><i className="mvx-font icon-no"></i></div>

        </div> : '';


        this.state.columns_questions_new[index_ques] = data_ques
        set_for_dynamic_column_question = this.state.columns_questions_new;
        this.setState({
          columns_questions_new: set_for_dynamic_column_question,
        });
      }
      )
    }
    // Display table column and row slection for questions

    // Display table column and row slection
    if (this.state.columns_knowledgebase_new.length == 0 && new URLSearchParams(window.location.hash).get("name") == 'knowladgebase') {
      appLocalizer.columns_knowledgebase.map((data_anno_knowl, index_knowledge) => {
        var data_knowledgebase_selector = '';
        var set_for_dynamic_column_know = '';
        data_knowledgebase_selector = data_anno_knowl['selector_choice'];
        data_anno_knowl.selector = row => <div dangerouslySetInnerHTML={{ __html: row[data_knowledgebase_selector] }}></div>;


        data_anno_knowl.cell ? data_anno_knowl.cell = (row) => <div className="mvx-vendor-action-icon">

          <a href={row.link}><i className="mvx-font icon-edit"></i></a>
          <div onClick={() => this.handlePostDismiss(row.id, row.type)} id={row.id}><i className="mvx-font icon-no"></i></div>

        </div> : '';


        this.state.columns_knowledgebase_new[index_knowledge] = data_anno_knowl
        set_for_dynamic_column_know = this.state.columns_knowledgebase_new;
        this.setState({
          columns_knowledgebase_new: set_for_dynamic_column_know,
        });
      }
      )
    }


    // Display table column and row slection
    if (this.state.columns_store_review.length == 0 && new URLSearchParams(window.location.hash).get("name") == 'store-review') {
      appLocalizer.columns_store_review.map((data_store_review_content, index_store_review) => {
        var data_store_review_selector = '';
        var set_for_dynamic_column_store_review = '';
        data_store_review_selector = data_store_review_content['selector_choice'];
        data_store_review_content.selector = row => <div dangerouslySetInnerHTML={{ __html: row[data_store_review_selector] }}></div>;


        data_store_review_content.cell ? data_store_review_content.cell = (row) => <div className="mvx-vendor-action-icon">

          <a href={row.link}><i className="mvx-font icon-edit"></i></a>
          <div onClick={() => this.handleReviewDismiss(row.id)} id={row.id}><i className="mvx-font icon-no"></i></div>

        </div> : '';

        this.state.columns_store_review[index_store_review] = data_store_review_content
        set_for_dynamic_column_store_review = this.state.columns_store_review;
        this.setState({
          columns_store_review: set_for_dynamic_column_store_review,
        });
      }
      )
    }


    // Display table column and row slection
    if (this.state.columns_report_abuse.length == 0 && new URLSearchParams(window.location.hash).get("name") == 'report-abuse') {
      appLocalizer.columns_report_abuse.map((data_store_report_abuse_content, index_store_abuse) => {
        var data_report_abuse_selector = '';
        var set_for_dynamic_column_store_review = '';
        data_report_abuse_selector = data_store_report_abuse_content['selector_choice'];
        data_store_report_abuse_content.selector = row => <div dangerouslySetInnerHTML={{ __html: row[data_report_abuse_selector] }}></div>;


        data_store_report_abuse_content.cell ? data_store_report_abuse_content.cell = (row) => <div className="mvx-vendor-action-icon">
          <div onClick={() => this.handleAbuseDismiss(row.reason, row.product, row.vendor)} id={row.reason}><i className="mvx-font icon-no"></i></div>
        </div> : '';

        this.state.columns_report_abuse[index_store_abuse] = data_store_report_abuse_content
        set_for_dynamic_column_store_review = this.state.columns_report_abuse;
        this.setState({
          columns_report_abuse: set_for_dynamic_column_store_review,
        });
      }
      )
    }

    return (
      <div className='mvx-workboard-tab-wrapper'>
        {
          name == 'activity-reminder' ?

            <div className="mvx-module-grid">



              {/* Pending Vendor's product approval work done */}
              <div className="mvx-todo-status-check">

                <div className="mvx-text-with-line-wrapper">
                  <div className="mvx-report-text">
                    <span>Pending Product Approval</span>
                  </div>
                  <div className="mvx-select-all-bulk-wrap">
                    <div className="mvx-select-all-checkbox">
                      <input type="checkbox" className="mvx-select-all" checked={this.state.pending_parent_product_check} onChange={(e) => this.handle_parent_todo_checkbox_chenage(e)} />
                      <span className="mvx-select-all-text">Select All</span>
                    </div>
                    <Select placeholder="Bulk Action" options={appLocalizer.task_board_bulk_status} isClearable={true} className="mvx-module-vendor-section-nav-child-data" onChange={(e) => this.handle_task_board_bulk_chenage(e, 'product_approval')} />
                  </div>
                </div>

                <div className="mvx-row">

                  {
                    this.state.list_of_pending_vendor_product.length > 0 ? this.state.list_of_pending_vendor_product.map((pending_data, pending_index) => (

                      <div className='mvx-col-33'>
                        <div className='mv-off-white-box'>
                          <div className='mv-white-box-header'>
                            Pending Vendor Product
                            <div className='pull-right'>
                              <input type="checkbox" className="mvx-workboard-checkbox" checked={this.state.pending_product_check[pending_index]} onChange={(e) => this.handle_todo_checkbox_chenage(e, pending_data.id, pending_index)} />
                            </div>
                          </div>
                          <div className='mv-white-box-body'>
                            <div className='mvx-row mvx-align-items-center'>
                              <div className='mvx-col-75 pl-0'>
                                <p dangerouslySetInnerHTML={{ __html: pending_data.product_src }}></p>
                              </div>
                            </div>
                            <div className='mvx-row mvx-align-items-center name-txt'>
                              <div className='mvx-col-33'>Vendor Name:</div>
                              <div className='mvx-col-67'><a href={pending_data.vendor_link}>{pending_data.vendor}</a></div>
                            </div>
                            <div className='mvx-row mvx-align-items-center name-txt'>
                              <div className='mvx-col-33'>Product Name:</div>
                              <div className='mvx-col-67'><a href={pending_data.product_url}>{pending_data.product}</a></div>
                            </div>
                          </div>
                          <div className='mv-white-box-footer'>
                            <div className='pull-left'>
                              <a href={pending_data.coupon_url} className='link-icon'><i className="mvx-font icon-tick"></i></a>
                              <a href={pending_data.product_url} className="link-icon"><i className="mvx-font icon-edit"></i></a>
                              <div className="link-icon"><i className="mvx-font icon-approve" onClick={(e) => this.handle_product_request_by_vendors(e, pending_data.id, pending_data.vendor_id, 'approve')}></i></div>
                              <div className="link-icon"><i className="mvx-font icon-close" onClick={(e) => this.handle_product_request_by_vendors(e, pending_data.id, pending_data.vendor_id, 'dismiss')}></i></div>
                            </div>

                            <div className="pull-right">
                              <div className='link-icon'><i className="mvx-font icon-download"></i></div>
                            </div>

                          </div>
                        </div>
                      </div>

                    )
                    )
                      : this.state.pending_product_loding_end ? <div className='mvx-col-100 mb-30'>No Product Found</div> : <PuffLoader css={override} color={"#cd0000"} size={100} loading={true} />
                  }

                </div>
              </div>
              {/* Pending Vendor's product approval end */}







              {/* Pending Vendor approval work done */}
              <div className="mvx-todo-status-check">

                <div className="mvx-text-with-line-wrapper">
                  <div className="mvx-report-text">
                    <span>Pending Vendor Approval</span>
                  </div>
                  <div className="mvx-select-all-bulk-wrap">
                    <div className="mvx-select-all-checkbox">
                      <input type="checkbox" className="mvx-select-all" checked={this.state.pending_parent_product_check} onChange={(e) => this.handle_parent_todo_checkbox_chenage(e)} />
                      <span className="mvx-select-all-text">Select All</span>
                    </div>
                    <Select placeholder="Bulk Action" options={appLocalizer.task_board_bulk_status} isClearable={true} className="mvx-module-vendor-section-nav-child-data" onChange={(e) => this.handle_task_board_bulk_chenage(e, 'product_approval')} />
                  </div>
                </div>


                <div className="mvx-row">

                  {
                    this.state.list_of_pending_vendor.length > 0 ? this.state.list_of_pending_vendor.map((pending_data, pending_index) => (

                      <div className='mvx-col-33'>
                        <div className='mv-off-white-box'>
                          <div className='mv-white-box-header'>
                            Pending User
                            <div className='pull-right'>
                              <input type="checkbox" className="mvx-workboard-checkbox" checked={this.state.pending_user_check[pending_index]} onChange={(e) => this.handle_todo_user_chenage(e, pending_data.id, pending_index)} />
                            </div>
                          </div>
                          <div className='mv-white-box-body'>
                            <div className='mvx-row mvx-align-items-center'>
                              <div className='mvx-col-75 pl-0'>
                                <p dangerouslySetInnerHTML={{ __html: pending_data.vendor }}></p>
                              </div>
                            </div>
                            <div className='mvx-row mvx-align-items-center name-txt'>
                              <div className='mvx-col-33'>Vendor Name:</div>
                              <div className='mvx-col-67'><a href={pending_data.vendor_link}>{pending_data.vendor_name}</a></div>
                            </div>


                          </div>
                          <div className='mv-white-box-footer'>
                            <div className='pull-left'>
                              <a href={pending_data.vendor_link} className='link-icon'><i className="mvx-font icon-edit"></i></a>
                              <div className='link-icon'><i className="mvx-font icon-approve" onClick={(e) => this.handle_user_request_by_vendors(e, pending_data.id, 'approve')}></i></div>
                              <div className="mvx-left-icon link-icon"><i className="mvx-font icon-close" onClick={(e) => this.handle_user_request_by_vendors(e, pending_data.id, 'dismiss')}></i></div>
                            </div>

                            <div className="pull-right">
                              <div className='link-icon'><i className="mvx-font icon-download"></i></div>
                            </div>

                          </div>
                        </div>
                      </div>

                    )
                    )
                      : this.state.pending_user_loding_end ? <div className='mvx-col-100 mb-30'>No Vendor Found</div> : <PuffLoader css={override} color={"#cd0000"} size={100} loading={true} />
                  }

                </div>
              </div>
              {/* Pending Vendor approval end */}







              {/* Pending Vendor's coupon approval work done */}
              <div className="mvx-todo-status-check">

                <div className="mvx-text-with-line-wrapper">
                  <div className="mvx-report-text">
                    <span>Pending Coupon</span>
                  </div>
                  <div className="mvx-select-all-bulk-wrap">
                    <div className="mvx-select-all-checkbox">
                      <input type="checkbox" className="mvx-select-all" checked={this.state.pending_parent_product_check} onChange={(e) => this.handle_parent_todo_checkbox_chenage(e)} />
                      <span className="mvx-select-all-text">Select All</span>
                    </div>
                    <Select placeholder="Bulk Action" options={appLocalizer.task_board_bulk_status} isClearable={true} className="mvx-module-vendor-section-nav-child-data" onChange={(e) => this.handle_task_board_bulk_chenage(e, 'product_approval')} />
                  </div>
                </div>



                <div className="mvx-row">

                  {
                    this.state.list_of_pending_vendor_coupon.length > 0 ? this.state.list_of_pending_vendor_coupon.map((pending_data, pending_index) => (

                      <div className='mvx-col-33'>
                        <div className='mv-off-white-box'>
                          <div className='mv-white-box-header'>
                            Pending coupon
                            <div className='pull-right'>
                              <input type="checkbox" className="mvx-workboard-checkbox" checked={this.state.pending_coupon_check[pending_index]} onChange={(e) => this.handle_todo_coupon_chenage(e, pending_data.id, pending_index)} />
                            </div>
                          </div>
                          <div className='mv-white-box-body'>
                            <div className='mvx-row mvx-align-items-center'>
                              <div className='mvx-col-75 pl-0'>
                                <span className='blue-txt'>{pending_data.coupon}</span>
                              </div>
                            </div>
                            <div className='mvx-row mvx-align-items-center name-txt'>
                              <div className='mvx-col-33'>Vendor Name:</div>
                              <div className='mvx-col-67'><a href={pending_data.vendor_link}>{pending_data.vendor}</a></div>
                            </div>
                            <div className='mvx-row mvx-align-items-center name-txt'>
                              <div className='mvx-col-33'>Coupon Name:</div>
                              <div className='mvx-col-67'><a href={pending_data.coupon_url}>{pending_data.coupon}</a></div>
                            </div>

                          </div>
                          <div className='mv-white-box-footer'>
                            <div className='pull-left'>

                              <a href={pending_data.coupon_url} className='link-icon'><i className="mvx-font icon-edit"></i></a>
                              <div className="mvx-left-icon link-icon"><i className="mvx-font icon-approve" onClick={(e) => this.handle_coupon_request_by_vendors(e, pending_data.id, 'approve')}></i></div>
                              <div className="mvx-left-icon link-icon"><i className="mvx-font icon-close" onClick={(e) => this.handle_coupon_request_by_vendors(e, pending_data.id, 'dismiss')}></i></div>
                            </div>
                            <div className="pull-right">
                              <div className='link-icon'><i className="mvx-font icon-download"></i></div>
                            </div>
                          </div>
                        </div>
                      </div>



                    )
                    )
                      : this.state.pending_coupon_loding_end ? <div className='mvx-col-100 mb-30'>No Coupon Found</div> : <PuffLoader css={override} color={"#cd0000"} size={100} loading={true} />
                  }

                </div>
              </div>
              {/* Pending Vendor's coupon approval end */}






              {/* Pending tranasction approval work done */}
              <div className="mvx-todo-status-check">

                <div className="mvx-text-with-line-wrapper">
                  <div className="mvx-report-text">
                    <span>Pending Transaction</span>
                  </div>
                  <div className="mvx-select-all-bulk-wrap">
                    <div className="mvx-select-all-checkbox">
                      <input type="checkbox" className="mvx-select-all" checked={this.state.pending_parent_product_check} onChange={(e) => this.handle_parent_todo_checkbox_chenage(e)} />
                      <span className="mvx-select-all-text">Select All</span>
                    </div>
                    <Select placeholder="Bulk Action" options={appLocalizer.task_board_bulk_status} isClearable={true} className="mvx-module-vendor-section-nav-child-data" onChange={(e) => this.handle_task_board_bulk_chenage(e, 'product_approval')} />
                  </div>
                </div>


                <div className="mvx-row">

                  {
                    this.state.list_of_pending_transaction.length > 0 ? this.state.list_of_pending_transaction.map((pending_data, pending_index) => (

                      <div className='mvx-col-33'>
                        <div className='mv-off-white-box'>
                          <div className='mv-white-box-header'>
                            Pending coupon
                            <div className='pull-right'>
                              <input type="checkbox" className="mvx-workboard-checkbox" checked={this.state.pending_transaction_check[pending_index]} onChange={(e) => this.handle_todo_transaction_chenage(e, pending_data.id, pending_index)} />
                            </div>
                          </div>
                          <div className='mv-white-box-body'>
                            <div className='mvx-row mvx-align-items-center'>
                              <div className='mvx-col-75 pl-0'>
                                <span className='blue-txt'>{pending_data.coupon}</span>
                              </div>
                            </div>
                            <div className='mvx-row mvx-align-items-center name-txt'>
                              <div className='mvx-col-33'>Name:</div>
                              <div className='mvx-col-67'><a href="">{pending_data.coupon}</a></div>
                            </div>


                          </div>
                          <div className='mv-white-box-footer'>
                            <div className='pull-left'>

                              <div className="mvx-left-icon link-icon"><i className="mvx-font icon-edit"></i></div>
                              <div className="mvx-left-icon link-icon"><i className="mvx-font icon-approve"></i></div>
                              <div className="mvx-left-icon link-icon"><i className="mvx-font icon-close"></i></div>
                            </div>

                            <div className="pull-right">
                              <div className='link-icon'><i className="mvx-font icon-download"></i></div>
                            </div>
                          </div>
                        </div>
                      </div>




                    )
                    )
                      : this.state.pending_transaction_loding_end ? <div className='mvx-col-100 mb-30'>No Transaction Found</div> : <PuffLoader css={override} color={"#cd0000"} size={100} loading={true} />
                  }

                </div>
              </div>
              {/* Pending tranasction approval end */}








              {/* Pending question approval work done */}
              <div className="mvx-todo-status-check">

                <div className="mvx-text-with-line-wrapper">
                  <div className="mvx-report-text">
                    <span>Pending Question Approval</span>
                  </div>
                  <div className="mvx-select-all-bulk-wrap">
                    <div className="mvx-select-all-checkbox">
                      <input type="checkbox" className="mvx-select-all" checked={this.state.pending_parent_product_check} onChange={(e) => this.handle_parent_todo_checkbox_chenage(e)} />
                      <span className="mvx-select-all-text">Select All</span>
                    </div>
                    <Select placeholder="Bulk Action" options={appLocalizer.task_board_bulk_status} isClearable={true} className="mvx-module-vendor-section-nav-child-data" onChange={(e) => this.handle_task_board_bulk_chenage(e, 'product_approval')} />
                  </div>
                </div>



                <div className="mvx-row">

                  {
                    this.state.list_of_pending_question.length > 0 ? this.state.list_of_pending_question.map((pending_data, pending_index) => (

                      <div className='mvx-col-33'>
                        <div className='mv-off-white-box'>
                          <div className='mv-white-box-header'>
                            Pending Question
                            <div className='pull-right'>
                              <input type="checkbox" className="mvx-workboard-checkbox" checked={this.state.pending_question_check[pending_index]} onChange={(e) => this.handle_todo_question_checkbox_chenage(e, pending_data.id, pending_index)} />
                            </div>
                          </div>
                          <div className='mv-white-box-body'>
                            <div className='mvx-row mvx-align-items-center'>
                              <div className='mvx-col-75 pl-0'>
                                <p dangerouslySetInnerHTML={{ __html: pending_data.question_by }}></p>
                              </div>
                            </div>
                            <div className='mvx-row mvx-align-items-center name-txt'>
                              <div className='mvx-col-40'>Question by:</div>
                              <div className='mvx-col-60'><a href={pending_data.vendor_link}>{pending_data.question_by_name}</a></div>
                            </div>
                            <div className='mvx-row mvx-align-items-center name-txt'>
                              <div className='mvx-col-40'>Product Name:</div>
                              <div className='mvx-col-66'><a href={pending_data.product_url}>{pending_data.product_name}</a></div>
                            </div>

                            <div className='mvx-row mvx-align-items-center name-txt'>
                              <div className='mvx-col-40'>Question details:</div>
                              <div className='mvx-col-60'><a href={pending_data.product_url}>{pending_data.question_details}</a></div>
                            </div>

                          </div>
                          <div className='mv-white-box-footer'>
                            <div className='pull-left'>
                              <a href={pending_data.product_url} className='link-icon'><i className="mvx-font icon-edit"></i></a>
                              <div className="mvx-left-icon link-icon"><i className="mvx-font icon-approve" onClick={(e) => this.handle_question_request_by_vendors(e, pending_data.id, pending_data.question_product_id, 'verified')}></i></div>
                              <div className="mvx-left-icon link-icon"><i className="mvx-font icon-close" onClick={(e) => this.handle_question_request_by_vendors(e, pending_data.id, pending_data.question_product_id, 'rejected')}></i></div>
                            </div>

                            <div className="pull-right">
                              <div className='link-icon'><i className="mvx-font icon-download"></i></div>
                            </div>

                          </div>
                        </div>
                      </div>


                    )
                    )
                      : this.state.pending_question_loding_end ? <div className='mvx-col-100 mb-30'>No Question Found</div> : <PuffLoader css={override} color={"#cd0000"} size={100} loading={true} />
                  }

                </div>
              </div>
              {/* Pending question approval end */}






            </div>

            :

            name == 'announcement' ?
              <div className="mvx-module-grid">

                <div className="mvx-table-text-and-add-wrap mvx-addbtn">
                  <Link to={`?page=mvx#&submenu=work-board&name=announcement&create=announcement`}><i className="mvx-font icon-add"></i>Add Announcement</Link>
                </div>



                {get_current_name && get_current_name.get("create") == 'announcement' ?

                  <DynamicForm
                    key={`dynamic-form-announcement-add-new`}
                    className="mvx-announcement-add-new"
                    title="Add new Announcement"
                    model={appLocalizer.settings_fields['create_announcement']}
                    method="post"
                    modulename="create_announcement"
                    url="mvx_module/v1/create_announcement"
                    submit_title="Publish"
                  />
                  :

                  get_current_name.get("AnnouncementID") ?

                    this.state.edit_announcement_fileds && Object.keys(this.state.edit_announcement_fileds).length > 0 ?
                      <DynamicForm
                        key={`dynamic-form-announcement-add-new`}
                        className="mvx-announcement-add-new"
                        title="Update Announcement"
                        model={this.state.edit_announcement_fileds['update_announcement_display']}
                        method="post"
                        announcement_id={get_current_name.get("AnnouncementID")}
                        modulename="update_announcement"
                        url="mvx_module/v1/update_announcement"
                        submitbutton="false"
                      />
                      : <PuffLoader css={override} color={"#3f1473"} size={100} loading={true} />

                    :


                    <div>




                      <div className="mvx-search-and-multistatus-wrap mvx-row mvx-align-items-center mvx-justify-content-between mb-15">

                        <div className='mvx-col-55'>
                          <ul className='mvx-ul-auto mvx-row'>
                            <li className='mvx-col-auto'>
                              <div className="mvx-multistatus-check-all" onClick={(e) => this.handle_post_retrive_status(e, 'all', 'announcement')}>All ({this.state.display_all_announcement.length})</div>
                            </li>
                            <li class="mvx-col-auto mvx-divider"></li>
                            <li className='mvx-col-auto'>
                              <div className="mvx-multistatus-check-approve" onClick={(e) => this.handle_post_retrive_status(e, 'publish', 'announcement')}>| Published ({this.state.display_published_announcement.length})</div>
                            </li>
                            <li class="mvx-col-auto mvx-divider"></li>
                            <li className='mvx-col-auto'>
                              <div className="mvx-multistatus-check-pending status-active" onClick={(e) => this.handle_post_retrive_status(e, 'pending', 'announcement')}>| Pending ({this.state.display_pending_announcement.length})</div>
                            </li>
                          </ul>
                        </div>

                        <div className='mvx-col-auto'>
                          <div className='mvx-search-holder'><input type="text" placeholder="Search Announcement" onChange={(e) => this.handle_post_bulk_status(e, 'announcement')} /></div>
                        </div>




                      </div>

                      <div className='mvx-wrap-bulk-all-date mb-25'>
                        <div className="mvx-wrap-bulk-all-date mvx-row mvx-row-sm-8">
                          <div className='mvx-col-auto'>
                            <Select placeholder="Bulk actions" options={appLocalizer.post_bulk_status} isClearable={true} className="mvx-module-section-list-data" onChange={(e) => this.handle_work_board_chenage(e, 'announcement')} />
                          </div>
                          <div className='mvx-col-auto'>
                            <Select placeholder="All Dates" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_work_board_chenage} />
                          </div>
                        </div>
                      </div>




                      <div className="mvx-backend-datatable-wrapper">
                        {this.state.columns_announcement_new && this.state.columns_announcement_new.length > 0 ?
                          <DataTable
                            columns={this.state.columns_announcement_new}
                            data={this.state.display_announcement}
                            selectableRows
                            onSelectedRowsChange={this.onSelectedRowsChange}
                            pagination
                          />
                          : ''}
                      </div>





                    </div>
                }
              </div>

              :

              name == 'knowladgebase' ?

                <div className="mvx-module-grid">


                  <div className="mvx-table-text-and-add-wrap mvx-addbtn">
                    <Link to={`?page=mvx#&submenu=work-board&name=knowladgebase&create=knowladgebase`}><i className="mvx-font icon-add"></i>Add Knowladgebase</Link>
                  </div>

                  {get_current_name && get_current_name.get("create") == 'knowladgebase' ?

                    <DynamicForm
                      key={`dynamic-form-knowladgebase-add-new`}
                      className="mvx-knowladgebase-add-new"
                      title="Add new knowladgebase"
                      model={appLocalizer.settings_fields['create_knowladgebase']}
                      method="post"
                      modulename="create_knowladgebase"
                      url="mvx_module/v1/create_knowladgebase"
                      submit_title="Publish"
                    />
                    :

                    get_current_name.get("knowladgebaseID") ?

                      this.state.edit_knowledgebase_fileds && Object.keys(this.state.edit_knowledgebase_fileds).length > 0 ?
                        <DynamicForm
                          key={`dynamic-form-knowladgebase-add-new`}
                          className="mvx-knowladgebase-add-new"
                          title="Update Announcement"
                          model={this.state.edit_knowledgebase_fileds['update_knowladgebase_display']}
                          method="post"
                          knowladgebase_id={get_current_name.get("knowladgebaseID")}
                          modulename="update_knowladgebase"
                          url="mvx_module/v1/update_knowladgebase"
                          submitbutton="false"
                        />
                        : <PuffLoader css={override} color={"#cd0000"} size={100} loading={true} />

                      :

                      <div>
                        <div className="mvx-search-and-multistatus-wrap mvx-row mvx-align-items-center mvx-justify-content-between mb-15">

                          <div className='mvx-col-55'>
                            <ul className='mvx-ul-auto mvx-row'>
                              <li className='mvx-col-auto'>
                                <div className="mvx-multistatus-check-all" onClick={(e) => this.handle_post_retrive_status(e, 'all', 'knowladgebase')}>All ({this.state.display_all_knowladgebase.length})</div>
                              </li>
                              <li class="mvx-col-auto mvx-divider"></li>
                              <li class="mvx-col-auto">
                                <div className="mvx-multistatus-check-approve" onClick={(e) => this.handle_post_retrive_status(e, 'publish', 'knowladgebase')}>| Published ({this.state.display_publish_knowladgebase.length})</div>
                              </li>
                              <li class="mvx-col-auto mvx-divider"></li>
                              <li class="mvx-col-auto">
                                <div className="mvx-multistatus-check-pending status-active" onClick={(e) => this.handle_post_retrive_status(e, 'pending', 'knowladgebase')}>| Pending ({this.state.display_pending_knowladgebase.length})</div>
                              </li>
                            </ul>
                          </div>

                          <div className='mvx-col-auto'>
                            <div className='mvx-search-holder'>
                              <input type="text" placeholder="Search Knowledgebase" onChange={(e) => this.handle_post_bulk_status(e, 'knowladgebase')} />
                            </div>
                          </div>
                        </div>

                        <div className='mvx-wrap-bulk-all-date mb-25'>
                          <div className="mvx-wrap-bulk-action mvx-row mvx-row-sm-8">
                            <div className='mvx-col-auto'>
                              <Select placeholder="Bulk actions" options={appLocalizer.post_bulk_status} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_post_bulk_status} />
                            </div>
                            <div className='mvx-col-auto'>
                              <Select placeholder="All Dates" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_work_board_chenage} />
                            </div>
                          </div>
                        </div>


                        <div className="mvx-backend-datatable-wrapper">
                          {this.state.columns_knowledgebase_new && this.state.columns_knowledgebase_new.length > 0 ?
                            <DataTable
                              columns={this.state.columns_knowledgebase_new}
                              data={this.state.display_list_knowladgebase}
                              selectableRows
                              onSelectedRowsChange={this.onSelectedRowsChange}
                              pagination
                            />
                            : ''}
                        </div>
                      </div>

                  }

                </div>

                :

                name == 'store-review' ?

                  <div className="mvx-module-grid">
                    <div className="mvx-search-and-multistatus-wrap mvx-row mvx-align-items-center mvx-justify-content-between mb-15">

                      <div className="mvx-col-55">
                        <ul>
                          <li className='mvx-col-auto'>
                            <div class="mvx-multistatus-check-all">All ({this.state.list_of_store_review.length})</div>
                          </li>
                        </ul>
                        {/* <div className="mvx-multistatus-check-all">All ({this.state.list_of_store_review.length})</div> */}
                        {/*<div className="mvx-multistatus-check-approve" onClick={this.handle_post_retrive_status}>| Approve (10)</div>
                            <div className="mvx-multistatus-check-pending status-active">| Pending (10)</div>*/}
                      </div>


                      <div className="mvx-col-auto">
                        <div className='mvx-search-holder'>
                          <input type="text" placeholder="Search Review" name="search" onChange={(e) => this.handle_search_vendor_review(e)} />
                        </div>

                      </div>
                    </div>


                    <div className="mvx-wrap-bulk-all-date mb-25">
                      <div className="mvx-wrap-bulk-action mvx-row mvx-row-sm-8">
                        <div className='mvx-col-auto'>
                          <Select placeholder="Bulk actions" options={appLocalizer.store_review_bulk} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_review_bulk_status} />
                        </div>
                      </div>

                      {/*<div className="mvx-wrap-date-action">
              <Select placeholder="All Dates" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_work_board_chenage} />
            </div>*/}
                    </div>

                    <div className="mvx-backend-datatable-wrapper">
                      {this.state.columns_store_review && this.state.columns_store_review.length > 0 ?
                        <DataTable
                          columns={this.state.columns_store_review}
                          data={this.state.list_of_store_review}
                          selectableRows
                          onSelectedRowsChange={this.handleselectreviews}
                          pagination
                        />
                        : ''}
                    </div>
                  </div>

                  :

                  name == 'report-abuse' ?

                    <div className="mvx-module-grid">

                      <div className="mvx-wrap-bulk-all-date">
                        {/*<div className="mvx-wrap-bulk-action">
            <Select placeholder="Bulk actions" options={appLocalizer.store_review_bulk} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_review_bulk_status} />
          </div>*/}

                        <div className="mvx-wrap-bulk-action">
                          <Select placeholder="Filter by vendor" options={this.state.show_vendor_name} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_vendor_search_abuse} />
                        </div>

                        <div className="mvx-wrap-bulk-action">
                          <Select placeholder="Filter by product" options={this.state.product_list_option} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_product_search_abuse} />
                        </div>
                      </div>

                      <div className="mvx-backend-datatable-wrapper">
                        {this.state.columns_report_abuse && this.state.columns_report_abuse.length > 0 ?
                          <DataTable
                            columns={this.state.columns_report_abuse}
                            data={this.state.list_of_report_abuse}
                            selectableRows
                            onSelectedRowsChange={this.handleselectabuse}
                            pagination
                          />
                          : ''}
                      </div>
                    </div>

                    :

                    name == 'question-ans' ?

                      <div className="mvx-module-grid">

                        <div class="mvx-row mvx-align-items-center mvx-justify-content-between mb-15">
                          <div class="mvx-col-55">
                            <ul class="mvx-ul-auto mvx-row">
                              <li class="mvx-col-auto">
                                <div class="mvx-multistatus-check-all">All ({this.state.list_of_publish_question.length})</div>
                              </li>

                              <li class="mvx-col-auto mvx-divider"></li>
                              <li class="mvx-col-auto">
                                <div class="mvx-multistatus-check-pending status-active">Pending ({this.state.list_of_pending_question.length})</div>
                              </li>

                            </ul>
                          </div>
                          <div class="mvx-col-auto">
                            <div class="mvx-search-holder">
                              <input type="text" placeholder="Search Question" name="search" onChange={(e) => this.handle_question_search(e)} />
                            </div>
                          </div>
                        </div>




                        {/*<div className="mvx-wrap-bulk-all-date">
                          <div className="mvx-wrap-bulk-action">
                            <Select placeholder="Bulk actions" options={appLocalizer.post_bulk_status} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_post_bulk_status} />
                          </div>
                          <div className="mvx-wrap-date-action">
                            <Select placeholder="All Dates" options={this.state.details_vendor} isClearable={true} className="mvx-module-section-list-data" onChange={this.handle_work_board_chenage} />
                          </div>
                        </div>*/}

                        <div className="mvx-backend-datatable-wrapper">
                          {this.state.columns_questions_new && this.state.columns_questions_new.length > 0 ?
                            <DataTable
                              columns={this.state.columns_questions_new}
                              data={this.state.list_of_publish_question}
                              selectableRows
                              pagination
                            />
                            : ''}
                        </div>
                      </div>

                      :

                      ''

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