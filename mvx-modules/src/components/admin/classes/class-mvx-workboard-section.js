/* global appLocalizer */
import React, { Component } from 'react';
import axios from 'axios';
import Select from 'react-select';
import PuffLoader from 'react-spinners/PuffLoader';
import { css } from '@emotion/react';
import { BrowserRouter as Router, Link, useLocation } from 'react-router-dom';
import DynamicForm from '../../../DynamicForm';
import DataTable from 'react-data-table-component';
import TabSection from './class-mvx-page-tab';

const override = css`
	display: block;
	margin: 0 auto;
	border-color: green;
`;

class MVXworkboard extends Component {
	constructor(props) {
		super(props);
		this.state = {
			product_list_option: '',
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
			list_of_all_tabs: [],
			list_of_work_board_content: []
		};

		this.QueryParamsDemo = this.QueryParamsDemo.bind(this);
		this.useQuery = this.useQuery.bind(this);
		this.Child = this.Child.bind(this);
		this.handlePostRetriveStatus = this.handlePostRetriveStatus.bind(this);
		this.handlePostBulkStatus = this.handlePostBulkStatus.bind(this);
		this.onSelectedRowsChange = this.onSelectedRowsChange.bind(this);
		this.handleWorkBoardChenage = this.handleWorkBoardChenage.bind(this);
		this.handlePostDismiss = this.handlePostDismiss.bind(this);
		// pending product todo action
		this.handleProductRequestByVendors =
			this.handleProductRequestByVendors.bind(this);
		// trigger questions
		this.handleQuestionRequestByVendors =
			this.handleQuestionRequestByVendors.bind(this);
		// trigger counpon todo
		this.handleCouponRequestByVendors =
			this.handleCouponRequestByVendors.bind(this);
		//trigger todo user
		this.handleUserRequestByVendors =
			this.handleUserRequestByVendors.bind(this);
		// individual checkbox trigger
		this.handleTodoCheckboxChenage =
			this.handleTodoCheckboxChenage.bind(this);
		this.handleTodoUserChenage = this.handleTodoUserChenage.bind(this);
		this.handleTodoCouponChenage = this.handleTodoCouponChenage.bind(this);
		this.handleTodoTransactionChenage =
			this.handleTodoTransactionChenage.bind(this);
		this.handleTodoQuestionCheckboxChenage =
			this.handleTodoQuestionCheckboxChenage.bind(this);
		this.handleTaskBoardBulkChenage =
			this.handleTaskBoardBulkChenage.bind(this);
		this.handleParentTodoCheckboxChenage =
			this.handleParentTodoCheckboxChenage.bind(this);
		this.handleParentUserTodoCheckboxChenage =
			this.handleParentUserTodoCheckboxChenage.bind(this);
		this.handleParentCouponTodoCheckboxChenage =
			this.handleParentCouponTodoCheckboxChenage.bind(this);
		this.handleParentTransactionTodoCheckboxChenage =
			this.handleParentTransactionTodoCheckboxChenage.bind(this);
		this.handleParentQuestionTodoCheckboxChenage =
			this.handleParentQuestionTodoCheckboxChenage.bind(this);
		this.handleQuestionSearch = this.handleQuestionSearch.bind(this);
		this.handleReviewDismiss = this.handleReviewDismiss.bind(this);
		this.handleReviewBulkStatus = this.handleReviewBulkStatus.bind(this);
		this.handleselectreviews = this.handleselectreviews.bind(this);
		this.handleSearchVendorReview =
			this.handleSearchVendorReview.bind(this);
		this.handleselectabuse = this.handleselectabuse.bind(this);
		this.handleVendorSearchAbuse = this.handleVendorSearchAbuse.bind(this);
		this.handleProductSearchAbuse =
			this.handleProductSearchAbuse.bind(this);
		this.handleAbuseDismiss = this.handleAbuseDismiss.bind(this);
		this.handleQuestionDelete = this.handleQuestionDelete.bind(this);
		this.handleTransactionRequestByVendors =
			this.handleTransactionRequestByVendors.bind(this);
		this.handleQuestionBulkStatusChange =
			this.handleQuestionBulkStatusChange.bind(this);
	}

	handleQuestionBulkStatusChange(e) {
		if (e) {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/list_of_bulk_change_status_question`,
				data: {
					value: e.value,
					product_ids: Array.isArray(e) ? e : '',
				},
			}).then((responce) => {
				this.setState({
					list_of_publish_question: responce.data,
				});
			});
		} else {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_question`,
					{
						params: { status: 'publish' },
					}
				)
				.then((response) => {
					this.setState({
						list_of_publish_question: response.data,
					});
				});
		}
	}

	handleTransactionRequestByVendors(e, transactionId, vendorId, status) {
		if (status === 'dismiss') {
			if (confirm(appLocalizer.global_string.confirm_dismiss)) {
				axios({
					method: 'post',
					url: `${appLocalizer.apiUrl}/mvx_module/v1/approve_dismiss_pending_transaction`,
					data: {
						transactionId,
						vendorId,
						status,
					},
				}).then((responce) => {
					this.setState({
						list_of_pending_transaction: responce.data,
					});
				});
			}
		} else {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/approve_dismiss_pending_transaction`,
				data: {
					transactionId,
					vendorId,
					status,
				},
			}).then((responce) => {
				this.setState({
					list_of_pending_transaction: responce.data,
				});
			});
		}
	}

	handleQuestionDelete(e, questionId, productId, type) {
		if (type === 'rejected') {
			if (confirm(appLocalizer.global_string.confirm_dismiss)) {
				axios({
					method: 'post',
					url: `${appLocalizer.apiUrl}/mvx_module/v1/approve_dismiss_pending_question`,
					data: {
						questionId,
						productId,
						type,
					},
				}).then((responce) => {
					this.setState({
						list_of_publish_question: responce.data,
					});
				});
			}
		} else {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/approve_dismiss_pending_question`,
				data: {
					questionId,
					productId,
					type,
				},
			}).then((responce) => {
				this.setState({
					list_of_publish_question: responce.data,
				});
			});
		}
	}

	handleAbuseDismiss(reason, product, vendor) {
		if (confirm(appLocalizer.global_string.confirm_delete)) {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_delete`,
				data: {
					reason,
					product,
					vendor,
				},
			}).then((responce) => {
				this.setState({
					list_of_report_abuse: responce.data,
				});
			});
		}
	}

	handleVendorSearchAbuse(e) {
		if (e) {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_details`,
					{
						params: { vendor_id: e.value },
					}
				)
				.then((response) => {
					this.setState({
						list_of_report_abuse: response.data,
					});
				});
		} else {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_details`
				)
				.then((response) => {
					this.setState({
						list_of_report_abuse: response.data,
					});
				});
		}
	}

	handleProductSearchAbuse(e) {
		if (e) {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_details`,
					{
						params: { product_id: e.value },
					}
				)
				.then((response) => {
					this.setState({
						list_of_report_abuse: response.data,
					});
				});
		} else {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_details`
				)
				.then((response) => {
					this.setState({
						list_of_report_abuse: response.data,
					});
				});
		}
	}

	handleselectabuse(e) {
		this.setState({
			bulkselectabuselist: e.selectedRows,
		});
	}

	handleSearchVendorReview(e) {
		axios({
			method: 'post',
			url: `${appLocalizer.apiUrl}/mvx_module/v1/search_review`,
			data: {
				value: e.target.value,
			},
		}).then((responce) => {
			this.setState({
				list_of_store_review: responce.data,
			});
		});
	}

	handleReviewBulkStatus() {
		if (confirm(appLocalizer.global_string.confirm_delete)) {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/delete_review`,
				data: {
					id: this.state.bulkselectreviewlist,
				},
			}).then((responce) => {
				this.setState({
					list_of_store_review: responce.data,
				});
			});
		}
	}

	handleselectreviews(e) {
		this.setState({
			bulkselectreviewlist: e.selectedRows,
		});
	}

	handleReviewDismiss(id) {
		if (confirm(appLocalizer.global_string.confirm_delete)) {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/delete_review`,
				data: {
					id,
				},
			}).then((responce) => {
				this.setState({
					list_of_store_review: responce.data,
				});
			});
		}
	}

	handleQuestionSearch(e) {
		axios({
			method: 'post',
			url: `${appLocalizer.apiUrl}/mvx_module/v1/search_question_ans`,
			data: {
				value: e.target.value,
			},
		}).then((responce) => {
			this.setState({
				list_of_publish_question: responce.data,
			});
		});
	}

	handleTaskBoardBulkChenage(e, type) {
		if (type === 'product_approval') {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/bulk_todo_pending_product`,
				data: {
					product_list: this.state.pending_product_check,
					value: e.value,
					type,
				},
			}).then((responce) => {
				this.setState({
					list_of_pending_vendor_product: responce.data,
				});
			});
		} else if (type === 'user_approval') {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/bulk_todo_pending_product`,
				data: {
					user_list: this.state.pending_user_check,
					value: e.value,
					type,
				},
			}).then((responce) => {
				this.setState({
					list_of_pending_vendor_product: responce.data,
				});
			});
		} else if (type === 'coupon_approval') {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/bulk_todo_pending_product`,
				data: {
					coupon_list: this.state.pending_coupon_check,
					value: e.value,
					type,
				},
			}).then((responce) => {
				this.setState({
					list_of_pending_vendor_product: responce.data,
				});
			});
		} else if (type === 'transaction_approval') {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/bulk_todo_pending_product`,
				data: {
					transaction_list: this.state.pending_transaction_check,
					value: e.value,
					type,
				},
			}).then((responce) => {
				this.setState({
					list_of_pending_transaction: responce.data,
				});
			});
		} else if (type === 'question_approval') {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/bulk_todo_pending_product`,
				data: {
					product_list: this.state.pending_question_check,
					value: e.value,
					type,
				},
			}).then((responce) => {
				this.setState({
					list_of_pending_question: responce.data,
				});
			});
		}
	}

	handleParentTodoCheckboxChenage(e) {
		if (e.target.checked) {
			this.setState({
				pending_parent_product_check: true,
				pending_product_check: new Array(
					this.state.pending_product_check.length
				).fill(true),
			});
		} else {
			this.setState({
				pending_parent_product_check: false,
				pending_product_check: new Array(
					this.state.pending_product_check.length
				).fill(false),
			});
		}
	}

	handleParentUserTodoCheckboxChenage(e) {
		if (e.target.checked) {
			this.setState({
				pending_parent_user_check: true,
				pending_user_check: new Array(
					this.state.pending_user_check.length
				).fill(true),
			});
		} else {
			this.setState({
				pending_parent_user_check: false,
				pending_user_check: new Array(
					this.state.pending_user_check.length
				).fill(false),
			});
		}
	}

	handleParentCouponTodoCheckboxChenage(e) {
		if (e.target.checked) {
			this.setState({
				pending_parent_coupon_check: true,
				pending_coupon_check: new Array(
					this.state.pending_coupon_check.length
				).fill(true),
			});
		} else {
			this.setState({
				pending_parent_coupon_check: false,
				pending_coupon_check: new Array(
					this.state.pending_coupon_check.length
				).fill(false),
			});
		}
	}

	handleParentTransactionTodoCheckboxChenage(e) {
		if (e.target.checked) {
			this.setState({
				pending_parent_transaction_check: true,
				pending_transaction_check: new Array(
					this.state.pending_transaction_check.length
				).fill(true),
			});
		} else {
			this.setState({
				pending_parent_transaction_check: false,
				pending_transaction_check: new Array(
					this.state.pending_transaction_check.length
				).fill(false),
			});
		}
	}

	handleParentQuestionTodoCheckboxChenage(e) {
		if (e.target.checked) {
			this.setState({
				pending_parent_question_check: true,
				pending_question_check: new Array(
					this.state.pending_question_check.length
				).fill(true),
			});
		} else {
			this.setState({
				pending_parent_question_check: false,
				pending_question_check: new Array(
					this.state.pending_question_check.length
				).fill(false),
			});
		}
	}

	// individual checkbox trigger
	handleTodoCheckboxChenage(e, id, position) {
		const updatedCheckedState = this.state.pending_product_check.map(
			(item, index) => (index === position ? !item : item)
		);

		this.setState({
			pending_product_check: updatedCheckedState,
		});
	}

	handleTodoUserChenage(e, id, position) {
		const updatedCheckedState = this.state.pending_user_check.map(
			(item, index) => (index === position ? !item : item)
		);

		this.setState({
			pending_user_check: updatedCheckedState,
		});
	}

	handleTodoCouponChenage(e, id, position) {
		const updatedCheckedState = this.state.pending_coupon_check.map(
			(item, index) => (index === position ? !item : item)
		);

		this.setState({
			pending_coupon_check: updatedCheckedState,
		});
	}

	handleTodoTransactionChenage(e, id, position) {
		const updatedCheckedState = this.state.pending_transaction_check.map(
			(item, index) => (index === position ? !item : item)
		);

		this.setState({
			pending_transaction_check: updatedCheckedState,
		});
	}

	handleTodoQuestionCheckboxChenage(e, id, position) {
		const updatedCheckedState = this.state.pending_question_check.map(
			(item, index) => (index === position ? !item : item)
		);

		this.setState({
			pending_question_check: updatedCheckedState,
		});
	}

	handleQuestionRequestByVendors(e, questionId, productId, type) {
		axios({
			method: 'post',
			url: `${appLocalizer.apiUrl}/mvx_module/v1/approve_dismiss_pending_question`,
			data: {
				questionId,
				productId,
				type,
			},
		}).then((responce) => {
			this.setState({
				list_of_pending_question: responce.data,
			});
		});
	}

	handleCouponRequestByVendors(e, id, type) {
		axios({
			method: 'post',
			url: `${appLocalizer.apiUrl}/mvx_module/v1/dismiss_and_approve_vendor_coupon`,
			data: {
				coupon_id: id,
				type,
			},
		}).then((responce) => {
			this.setState({
				list_of_pending_vendor_coupon: responce.data,
			});
		});
	}

	handleUserRequestByVendors(e, id, type) {
		if (type === 'dismiss') {
			if (confirm(appLocalizer.global_string.confirm_dismiss)) {
				axios({
					method: 'post',
					url: `${appLocalizer.apiUrl}/mvx_module/v1/dismiss_vendor`,
					data: {
						vendor_id: id,
					},
				}).then((responce) => {
					this.setState({
						list_of_pending_vendor: responce.data,
					});
				});
			}
		} else if (type === 'approve') {
			if (confirm(appLocalizer.global_string.confirm_approve)) {
				axios({
					method: 'post',
					url: `${appLocalizer.apiUrl}/mvx_module/v1/approve_vendor`,
					data: {
						vendor_id: id,
					},
				}).then((responce) => {
					this.setState({
						list_of_pending_vendor: responce.data,
					});
				});
			}
		}
	}

	handleProductRequestByVendors(e, productId, vendorId, type) {
		if (type === 'dismiss') {
			if (confirm(appLocalizer.global_string.confirm_dismiss)) {
				axios({
					method: 'post',
					url: `${appLocalizer.apiUrl}/mvx_module/v1/dismiss_requested_vendors_query`,
					data: {
						productId,
						type,
						vendorId,
					},
				}).then((responce) => {
					this.setState({
						list_of_pending_vendor_product: responce.data,
					});
				});
			}
		} else if (type === 'approve') {
			if (confirm(appLocalizer.global_string.confirm_approve)) {
				axios({
					method: 'post',
					url: `${appLocalizer.apiUrl}/mvx_module/v1/approve_product`,
					data: {
						productId,
						type,
						vendorId,
					},
				}).then((responce) => {
					this.setState({
						list_of_pending_vendor_product: responce.data,
					});
				});
			}
		}
	}

	handlePostDismiss(e, title) {
		if (confirm(appLocalizer.global_string.confirm_delete)) {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/delete_post_details`,
				data: {
					ids: e,
					title,
				},
			}).then((responce) => {
				this.setState({
					display_announcement: responce.data,
				});
			});
		}
	}

	handleWorkBoardChenage(e, type) {
		if (type === 'announcement' && e) {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/update_custom_post_status`,
				data: {
					ids: this.state.bulkselectlist,
					value: e.value,
				},
			}).then(() => {});
		}
	}

	onSelectedRowsChange(e) {
		this.setState({
			bulkselectlist: e.selectedRows,
		});
	}

	handlePostRetriveStatus(e, status, type) {
		if (type === 'announcement') {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`,
					{
						params: { status },
					}
				)
				.then((response) => {
					this.setState({
						display_announcement: response.data,
					});
				});
		} else if (type === 'knowladgebase') {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`,
					{
						params: { status },
					}
				)
				.then((response) => {
					this.setState({
						display_list_knowladgebase: response.data,
					});
				});
		}
	}

	handlePostBulkStatus(e, type) {
		if (type === 'announcement') {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/search_announcement`,
				data: {
					ids: this.state.bulkselectlist,
					value: e.target.value,
				},
			}).then((responce) => {
				this.setState({
					display_announcement: responce.data,
				});
			});
		} else if (type === 'knowladgebase') {
			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/search_knowledgebase`,
				data: {
					value: e.target.value,
				},
			}).then((responce) => {
				this.setState({
					display_list_knowladgebase: responce.data,
				});
			});
		}
	}

	componentDidMount() {
		/***********  Announcement  ******************/
		// all announcement
		axios
			.get(`${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`)
			.then((response) => {
				this.setState({
					display_announcement: response.data,
				});
			});

		// pending announcement
		axios
			.get(`${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`, {
				params: { status: 'pending' },
			})
			.then((response) => {
				this.setState({
					display_pending_announcement: response.data,
				});
			});

		// published announcement
		axios
			.get(`${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`, {
				params: { status: 'publish' },
			})
			.then((response) => {
				this.setState({
					display_published_announcement: response.data,
				});
			});

		// all announcement count
		axios
			.get(`${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`, {
				params: { status: 'all' },
			})
			.then((response) => {
				this.setState({
					display_all_announcement: response.data,
				});
			});
		/***********  Announcement  ******************/

		/***********  Knowledgebase  **************/
		axios
			.get(
				`${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`
			)
			.then((response) => {
				this.setState({
					display_list_knowladgebase: response.data,
				});
			});

		axios
			.get(
				`${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`,
				{
					params: { status: 'all' },
				}
			)
			.then((response) => {
				this.setState({
					display_all_knowladgebase: response.data,
				});
			});

		axios
			.get(
				`${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`,
				{
					params: { status: 'publish' },
				}
			)
			.then((response) => {
				this.setState({
					display_publish_knowladgebase: response.data,
				});
			});

		axios
			.get(
				`${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`,
				{
					params: { status: 'pending' },
				}
			)
			.then((response) => {
				this.setState({
					display_pending_knowladgebase: response.data,
				});
			});

		/******** Knowledgebase end  ************/
		// pending details
		axios
			.get(
				`${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_question`
			)
			.then((response) => {
				const allPendingDataCheckbox = new Array(
					response.data.length
				).fill(false);
				this.setState({
					list_of_pending_question: response.data,
					pending_question_check: allPendingDataCheckbox,
					pending_question_loding_end: true,
				});
			});

		// publish details
		axios
			.get(
				`${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_question`,
				{
					params: { status: 'publish' },
				}
			)
			.then((response) => {
				this.setState({
					list_of_publish_question: response.data,
				});
			});

		axios
			.get(
				`${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_transaction`
			)
			.then((response) => {
				this.setState({
					list_of_pending_transaction: response.data,
					pending_transaction_loding_end: true,
					pending_transaction_check: new Array(
						response.data.length
					).fill(false),
				});
			});

		axios
			.get(
				`${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_vendor_coupon`
			)
			.then((response) => {
				this.setState({
					list_of_pending_vendor_coupon: response.data,
					pending_coupon_loding_end: true,
					pending_coupon_check: new Array(response.data.length).fill(
						false
					),
				});
			});

		axios
			.get(`${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_vendor`)
			.then((response) => {
				const allPendingDataCheckbox = new Array(
					response.data.length
				).fill(false);
				this.setState({
					list_of_pending_vendor: response.data,
					pending_user_loding_end: true,
					pending_user_check: allPendingDataCheckbox,
				});
			});

		axios
			.get(
				`${appLocalizer.apiUrl}/mvx_module/v1/list_of_pending_vendor_product`
			)
			.then((response) => {
				const allPendingProductCheckbox = new Array(
					response.data.length
				).fill(false);

				this.setState({
					list_of_pending_vendor_product: response.data,
					pending_product_loding_end: true,
					pending_product_check: allPendingProductCheckbox,
				});
			});

		// fetch review
		axios
			.get(`${appLocalizer.apiUrl}/mvx_module/v1/list_of_store_review`)
			.then((response) => {
				this.setState({
					list_of_store_review: response.data,
				});
			});

		// fetch review
		axios
			.get(`${appLocalizer.apiUrl}/mvx_module/v1/report_abuse_details`)
			.then((response) => {
				this.setState({
					list_of_report_abuse: response.data,
				});
			});

		// get vendor name on select
		axios({
			url: `${appLocalizer.apiUrl}/mvx_module/v1/show_vendor_name`,
		}).then((response) => {
			this.setState({
				show_vendor_name: response.data,
			});
		});

		// product list
		axios({
			url: `${appLocalizer.apiUrl}/mvx_module/v1/product_list_option`,
		}).then((response) => {
			this.setState({
				product_list_option: response.data,
			});
		});

		// tab list
		axios({
			url: `${appLocalizer.apiUrl}/mvx_module/v1/list_of_all_tabs`,
		}).then((response) => {
			this.setState({
				list_of_all_tabs: response.data,
			});
		});

		// list of workboard data
		axios({
			url: `${appLocalizer.apiUrl}/mvx_module/v1/list_of_work_board_content`,
		}).then((response) => {
			this.setState({
				list_of_work_board_content: response.data,
			});
		});
	}

	useQuery() {
		return new URLSearchParams(useLocation().hash);
	}

	QueryParamsDemo() {
		// update announcement table when clock on announcement tab
		if (
			new URLSearchParams(window.location.hash).get('name') ===
			'announcement'
		) {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/display_announcement`
				)
				.then((response) => {
					this.state.display_announcement = response.data;
				});
		}
		// update announcement table end

		// update knowledgebase table when clock on knowledgebase tab
		if (
			new URLSearchParams(window.location.hash).get('name') ===
			'knowladgebase'
		) {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/display_list_knowladgebase`
				)
				.then((response) => {
					//this.setState({
					this.state.display_list_knowladgebase = response.data;
					//});
				});
		}
		// update knowledgebase table end

		const use_query = this.useQuery();
		return Object.keys(this.state.list_of_all_tabs).length > 0 ? (
			<TabSection
				model={this.state.list_of_all_tabs['marketplace-workboard']}
				query_name={use_query.get('name')}
				funtion_name={this}
				horizontally
				no_banner
			/>
		) : (
			<PuffLoader
				css={override}
				color={'#cd0000'}
				size={200}
				loading={true}
			/>
		);
	}

	Child({ name }) {

		//console.log(this.state.list_of_work_board_content);

		const get_current_name = this.useQuery();

		if (!get_current_name.get('AnnouncementID')) {
			this.state.edit_announcement_fileds = [];
		}

		if (!get_current_name.get('knowladgebaseID')) {
			this.state.edit_knowledgebase_fileds = [];
		}

		if (get_current_name.get('AnnouncementID')) {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/update_announcement_display`,
					{
						params: {
							announcement_id:
								get_current_name.get('AnnouncementID'),
						},
					}
				)
				.then((response) => {
					if (
						response.data &&
						this.state.edit_announcement_fileds.length === 0
					) {
						this.setState({
							edit_announcement_fileds: response.data,
						});
					}
				});
		}

		if (get_current_name.get('knowladgebaseID')) {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/update_knowladgebase_display`,
					{
						params: {
							knowladgebase_id:
								get_current_name.get('knowladgebaseID'),
						},
					}
				)
				.then((response) => {
					if (
						response.data &&
						this.state.edit_knowledgebase_fileds.length === 0
					) {
						this.setState({
							edit_knowledgebase_fileds: response.data,
						});
					}
				});
		}

		// Display table column and row slection
		if (
			this.state.columns_announcement_new.length === 0 &&
			new URLSearchParams(window.location.hash).get('name') ===
				'announcement'
		) {
			appLocalizer.columns_announcement.map((data_ann, index_ann) => {
				let data_selector = '';
				let set_for_dynamic_column = '';
				data_selector = data_ann.selector_choice;
				data_ann.selector = (row) => (
					<div
						dangerouslySetInnerHTML={{ __html: row[data_selector] }}
					></div>
				);

				data_ann.cell
					? (data_ann.cell = (row) => (
							<div className="mvx-vendor-action-icon">
								<a href={row.link}>
									<i className="mvx-font icon-edit"></i>
								</a>
								<div
									onClick={() =>
										this.handlePostDismiss(row.id, row.type)
									}
									id={row.id}
								>
									<i className="mvx-font icon-no"></i>
								</div>
							</div>
					  ))
					: '';

				this.state.columns_announcement_new[index_ann] = data_ann;
				set_for_dynamic_column = this.state.columns_announcement_new;
				this.setState({
					columns_announcement_new: set_for_dynamic_column,
				});
			});
		}
		// Display table column and row slection end

		// Display table column and row slection for questions
		if (
			this.state.columns_questions_new.length === 0 &&
			new URLSearchParams(window.location.hash).get('name') ===
				'question-ans'
		) {
			appLocalizer.columns_questions.map((data_ques, index_ques) => {
				let data_selector_question = '';
				let set_for_dynamic_column_question = '';
				data_selector_question = data_ques.selector_choice;
				data_ques.selector = (row) => (
					<div
						dangerouslySetInnerHTML={{
							__html: row[data_selector_question],
						}}
					></div>
				);

				data_ques.cell
					? (data_ques.cell = (row) => (
							<div className="mvx-vendor-action-icon">
								<div
									onClick={(e) =>
										this.handleQuestionDelete(
											e,
											row.id,
											row.question_product_id,
											'verified'
										)
									}
									id={row.id}
								>
									<i className="mvx-font icon-approve"></i>
								</div>
								<div
									onClick={(e) =>
										this.handleQuestionDelete(
											e,
											row.id,
											row.question_product_id,
											'rejected'
										)
									}
									id={row.id}
								>
									<i className="mvx-font icon-no"></i>
								</div>
							</div>
					  ))
					: '';

				this.state.columns_questions_new[index_ques] = data_ques;
				set_for_dynamic_column_question =
					this.state.columns_questions_new;
				this.setState({
					columns_questions_new: set_for_dynamic_column_question,
				});
			});
		}
		// Display table column and row slection for questions

		// Display table column and row slection
		if (
			this.state.columns_knowledgebase_new.length === 0 &&
			new URLSearchParams(window.location.hash).get('name') ===
				'knowladgebase'
		) {
			appLocalizer.columns_knowledgebase.map(
				(data_anno_knowl, index_knowledge) => {
					let data_knowledgebase_selector = '';
					let set_for_dynamic_column_know = '';
					data_knowledgebase_selector =
						data_anno_knowl.selector_choice;
					data_anno_knowl.selector = (row) => (
						<div
							dangerouslySetInnerHTML={{
								__html: row[data_knowledgebase_selector],
							}}
						></div>
					);

					data_anno_knowl.cell
						? (data_anno_knowl.cell = (row) => (
								<div className="mvx-vendor-action-icon">
									<a href={row.link}>
										<i className="mvx-font icon-edit"></i>
									</a>
									<div
										onClick={() =>
											this.handlePostDismiss(
												row.id,
												row.type
											)
										}
										id={row.id}
									>
										<i className="mvx-font icon-no"></i>
									</div>
								</div>
						  ))
						: '';

					this.state.columns_knowledgebase_new[index_knowledge] =
						data_anno_knowl;
					set_for_dynamic_column_know =
						this.state.columns_knowledgebase_new;
					this.setState({
						columns_knowledgebase_new: set_for_dynamic_column_know,
					});
				}
			);
		}

		// Display table column and row slection
		if (
			this.state.columns_store_review.length === 0 &&
			new URLSearchParams(window.location.hash).get('name') ===
				'store-review'
		) {
			appLocalizer.columns_store_review.map(
				(data_store_review_content, index_store_review) => {
					let data_store_review_selector = '';
					let set_for_dynamic_column_store_review = '';
					data_store_review_selector =
						data_store_review_content.selector_choice;
					data_store_review_content.selector = (row) => (
						<div
							dangerouslySetInnerHTML={{
								__html: row[data_store_review_selector],
							}}
						></div>
					);

					data_store_review_content.cell
						? (data_store_review_content.cell = (row) => (
								<div className="mvx-vendor-action-icon">
									<a href={row.link}>
										<i className="mvx-font icon-edit"></i>
									</a>
									<div
										onClick={() =>
											this.handleReviewDismiss(row.id)
										}
										id={row.id}
									>
										<i className="mvx-font icon-no"></i>
									</div>
								</div>
						  ))
						: '';

					this.state.columns_store_review[index_store_review] =
						data_store_review_content;
					set_for_dynamic_column_store_review =
						this.state.columns_store_review;
					this.setState({
						columns_store_review:
							set_for_dynamic_column_store_review,
					});
				}
			);
		}

		// Display table column and row slection
		if (
			this.state.columns_report_abuse.length === 0 &&
			new URLSearchParams(window.location.hash).get('name') ===
				'report-abuse'
		) {
			appLocalizer.columns_report_abuse.map(
				(data_store_report_abuse_content, index_store_abuse) => {
					let data_report_abuse_selector = '';
					let set_for_dynamic_column_store_review = '';
					data_report_abuse_selector =
						data_store_report_abuse_content.selector_choice;
					data_store_report_abuse_content.selector = (row) => (
						<div
							dangerouslySetInnerHTML={{
								__html: row[data_report_abuse_selector],
							}}
						></div>
					);

					data_store_report_abuse_content.cell
						? (data_store_report_abuse_content.cell = (row) => (
								<div className="mvx-vendor-action-icon">
									<div
										onClick={() =>
											this.handleAbuseDismiss(
												row.reason,
												row.product,
												row.vendor
											)
										}
										id={row.reason}
									>
										<i className="mvx-font icon-no"></i>
									</div>
								</div>
						  ))
						: '';

					this.state.columns_report_abuse[index_store_abuse] =
						data_store_report_abuse_content;
					set_for_dynamic_column_store_review =
						this.state.columns_report_abuse;
					this.setState({
						columns_report_abuse:
							set_for_dynamic_column_store_review,
					});
				}
			);
		}

		return name === 'activity-reminder' ? (

			this.state.list_of_work_board_content.map(
				(taskboard_data, taskboard_index) => (
					<div className="mvx-todo-status-check">
						<div className="mvx-text-with-line-wrapper">
							<div className="mvx-text-with-right-side-line">
								{taskboard_data.header}
							</div>
							<div className="mvx-select-all-bulk-wrap">
								<div className="mvx-select-all-checkbox">
									<input
										type="checkbox"
										className="mvx-select-all"
									/>
									<span className="mvx-select-all-text">
										{appLocalizer.global_string.select_all}
									</span>
								</div>
								<Select
									placeholder={
										appLocalizer.global_string.bulk_action
									}
									options={appLocalizer.task_board_bulk_status}
									isClearable={true}
									className="mvx-wrap-bulk-action"
								/>
							</div>
						</div>
						<div className="mvx-product-box-sec">
							{taskboard_data.content.map(
								(task_lists_data, task_lists_index) => (
									<div className="mvx-all-product-box">
										<div className="mvx-white-box-header">
											{taskboard_data.header}
											<div className="pull-right">
												<input
													type="checkbox"
													className="mvx-workboard-checkbox"
													
												/>

											</div>
										</div>

										<div className="mvx-white-box-body">
										{
											task_lists_data.list_datas.map((task_child_data, task_child_index) => (
												<div className="mvx-box-content">
													<div className="mvx-product-title">
														{
															task_child_data.label
														}
														:
													</div>

													<div className="mvx-product-name">
														<p
															dangerouslySetInnerHTML={{
																__html: task_child_data.value,
															}}
														></p>
													</div>
												</div>
											))
										}
										</div>
										<div className="mvx-white-box-footer">
											<div className="pull-left">
												{
													task_lists_data.left_icons ? task_lists_data.left_icons.map((icons_data, icons_index) => (
														icons_data.key == 'edit' ?
															<a
																href={
																	icons_data.link
																}
																className="link-icon"
															>
																<i className="mvx-font icon-edit"></i>
															</a>
														: 

														<div className="link-icon">
															<i
																className={`mvx-font ${icons_data.icon}`}
																onClick={(e) =>
																	(
																		axios({
																			method: 'post',
																			url: `${appLocalizer.apiUrl}/mvx_module/v1/task_board_icons_triggers`,
																			data: {
																				value: icons_data.value,
																				key: icons_data.key
																			},
																		}).then((responce) => {
																			this.setState({
																				list_of_work_board_content: responce.data,
																			});
																		})
																	)
																}
															></i>
														</div>

													)) : ''
												}
											</div>
										</div>

									</div>	
								))
							}
						</div>
					</div>
				)
			)

		) : name === 'announcement' ? (
			<div className="mvx-module-grid">
				{(get_current_name &&
					get_current_name.get('create') === 'announcement') ||
				get_current_name.get('AnnouncementID') ? (
					<Link
						className="mvx-back-btn"
						to={`?page=mvx#&submenu=work-board&name=announcement`}
					>
						<i className="mvx-font icon-back"></i>
						{appLocalizer.global_string.back}
					</Link>
				) : (
					<Link
						className="mvx-btn btn-purple"
						to={`?page=mvx#&submenu=work-board&name=announcement&create=announcement`}
					>
						<i className="mvx-font icon-add"></i>
						{appLocalizer.workboard_string.workboard25}
					</Link>
				)}

				{get_current_name &&
				get_current_name.get('create') === 'announcement' ? (
					<DynamicForm
						key={`dynamic-form-announcement-add-new`}
						className="mvx-announcement-add-new"
						title="Add new Announcement"
						model={appLocalizer.settings_fields.create_announcement}
						method="post"
						modulename="create_announcement"
						url="mvx_module/v1/create_announcement"
						submit_title={appLocalizer.global_string.publish}
					/>
				) : get_current_name.get('AnnouncementID') ? (
					this.state.edit_announcement_fileds &&
					Object.keys(this.state.edit_announcement_fileds).length >
						0 ? (
						<DynamicForm
							key={`dynamic-form-announcement-add-new`}
							className="mvx-announcement-add-new"
							title="Update Announcement"
							model={
								this.state.edit_announcement_fileds
									.update_announcement_display
							}
							method="post"
							announcement_id={get_current_name.get(
								'AnnouncementID'
							)}
							modulename="update_announcement"
							url="mvx_module/v1/update_announcement"
							submitbutton="false"
						/>
					) : (
						<PuffLoader
							css={override}
							color={'#3f1473'}
							size={100}
							loading={true}
						/>
					)
				) : (
					<div className="mvx-knowladgebase-different-funtionality">
						<div className="mvx-search-and-multistatus-wrap">
							<ul className="mvx-multistatus-ul">
								<li className="mvx-multistatus-item">
									<div
										className="mvx-multistatus-check-all"
										onClick={(e) =>
											this.handlePostRetriveStatus(
												e,
												'all',
												'announcement'
											)
										}
									>
										{appLocalizer.global_string.all}(
										{
											this.state.display_all_announcement
												.length
										}
										)
									</div>
								</li>
								<li className="mvx-multistatus-item mvx-divider"></li>
								<li className="mvx-multistatus-item">
									<div
										className="mvx-multistatus-check-approve"
										onClick={(e) =>
											this.handlePostRetriveStatus(
												e,
												'publish',
												'announcement'
											)
										}
									>
										{appLocalizer.global_string.published} (
										{
											this.state
												.display_published_announcement
												.length
										}
										)
									</div>
								</li>
								<li className="mvx-multistatus-item mvx-divider"></li>
								<li className="mvx-multistatus-item">
									<div
										className="mvx-multistatus-check-pending status-active"
										onClick={(e) =>
											this.handlePostRetriveStatus(
												e,
												'pending',
												'announcement'
											)
										}
									>
										{appLocalizer.global_string.pending} (
										{
											this.state
												.display_pending_announcement
												.length
										}
										)
									</div>
								</li>
							</ul>

							<div className="mvx-header-search-section">
								<label>
									<i className="mvx-font icon-search"></i>
								</label>
								<input
									type="text"
									placeholder={
										appLocalizer.workboard_string
											.workboard26
									}
									onChange={(e) =>
										this.handlePostBulkStatus(
											e,
											'announcement'
										)
									}
								/>
							</div>
						</div>

						<div className="mvx-wrap-bulk-all-date">
							<Select
								placeholder={
									appLocalizer.global_string.bulk_action
								}
								options={appLocalizer.post_bulk_status}
								isClearable={true}
								className="mvx-wrap-bulk-action"
								onChange={(e) =>
									this.handleWorkBoardChenage(
										e,
										'announcement'
									)
								}
							/>
							<Select
								placeholder={
									appLocalizer.global_string.all_dates
								}
								className="mvx-wrap-bulk-action"
								options={this.state.details_vendor}
								isClearable={true}
								onChange={this.handleWorkBoardChenage}
							/>
						</div>

						{this.state.columns_announcement_new &&
						this.state.columns_announcement_new.length > 0 ? (
							<div className="mvx-backend-datatable-wrapper">
								<DataTable
									columns={
										this.state.columns_announcement_new
									}
									data={this.state.display_announcement}
									selectableRows
									onSelectedRowsChange={
										this.onSelectedRowsChange
									}
									pagination
								/>
							</div>
						) : (
							''
						)}
					</div>
				)}
			</div>
		) : name === 'knowladgebase' ? (
			<div className="mvx-module-grid">
				{(get_current_name &&
					get_current_name.get('create') === 'knowladgebase') ||
				get_current_name.get('knowladgebaseID') ? (
					<Link
						className="mvx-back-btn"
						to={`?page=mvx#&submenu=work-board&name=knowladgebase`}
					>
						<i className="mvx-font icon-back"></i>
						{appLocalizer.global_string.back}
					</Link>
				) : (
					<Link
						className="mvx-btn btn-purple"
						to={`?page=mvx#&submenu=work-board&name=knowladgebase&create=knowladgebase`}
					>
						<i className="mvx-font icon-add"></i>
						{appLocalizer.workboard_string.workboard27}
					</Link>
				)}
				{get_current_name &&
				get_current_name.get('create') === 'knowladgebase' ? (
					<DynamicForm
						key={`dynamic-form-knowladgebase-add-new`}
						className="mvx-knowladgebase-add-new"
						title="Add new knowladgebase"
						model={
							appLocalizer.settings_fields.create_knowladgebase
						}
						method="post"
						modulename="create_knowladgebase"
						url="mvx_module/v1/create_knowladgebase"
						submit_title={appLocalizer.global_string.publish}
					/>
				) : get_current_name.get('knowladgebaseID') ? (
					this.state.edit_knowledgebase_fileds &&
					Object.keys(this.state.edit_knowledgebase_fileds).length >
						0 ? (
						<DynamicForm
							key={`dynamic-form-knowladgebase-add-new`}
							className="mvx-knowladgebase-add-new"
							title="Update Announcement"
							model={
								this.state.edit_knowledgebase_fileds
									.update_knowladgebase_display
							}
							method="post"
							knowladgebase_id={get_current_name.get(
								'knowladgebaseID'
							)}
							modulename="update_knowladgebase"
							url="mvx_module/v1/update_knowladgebase"
							submitbutton="false"
						/>
					) : (
						<PuffLoader
							css={override}
							color={'#cd0000'}
							size={100}
							loading={true}
						/>
					)
				) : (
					<div className="mvx-knowladgebase-different-funtionality">
						<div className="mvx-search-and-multistatus-wrap">
							<ul className="mvx-multistatus-ul">
								<li className="mvx-multistatus-item">
									<div
										className="mvx-multistatus-check-all"
										onClick={(e) =>
											this.handlePostRetriveStatus(
												e,
												'all',
												'knowladgebase'
											)
										}
									>
										{appLocalizer.global_string.all}(
										{
											this.state.display_all_knowladgebase
												.length
										}
										)
									</div>
								</li>
								<li className="mvx-multistatus-item mvx-divider"></li>
								<li className="mvx-multistatus-item">
									<div
										className="mvx-multistatus-check-approve"
										onClick={(e) =>
											this.handlePostRetriveStatus(
												e,
												'publish',
												'knowladgebase'
											)
										}
									>
										{appLocalizer.global_string.published}(
										{
											this.state
												.display_publish_knowladgebase
												.length
										}
										)
									</div>
								</li>
								<li className="mvx-multistatus-item mvx-divider"></li>
								<li className="mvx-multistatus-item">
									<div
										className="mvx-multistatus-check-pending status-active"
										onClick={(e) =>
											this.handlePostRetriveStatus(
												e,
												'pending',
												'knowladgebase'
											)
										}
									>
										{appLocalizer.global_string.pending} (
										{
											this.state
												.display_pending_knowladgebase
												.length
										}
										)
									</div>
								</li>
							</ul>

							<div className="mvx-header-search-section">
								<label>
									<i className="mvx-font icon-search"></i>
								</label>
								<input
									type="text"
									placeholder={
										appLocalizer.workboard_string
											.workboard28
									}
									onChange={(e) =>
										this.handlePostBulkStatus(
											e,
											'knowladgebase'
										)
									}
								/>
							</div>
						</div>

						<div className="mvx-wrap-bulk-all-date">
							<Select
								placeholder={
									appLocalizer.global_string.bulk_action
								}
								options={appLocalizer.post_bulk_status}
								isClearable={true}
								className="mvx-wrap-bulk-action"
								onChange={this.handlePostBulkStatus}
							/>
							<Select
								placeholder={
									appLocalizer.global_string.all_dates
								}
								options={this.state.details_vendor}
								isClearable={true}
								className="mvx-wrap-bulk-action"
								onChange={this.handleWorkBoardChenage}
							/>
						</div>

						{this.state.columns_knowledgebase_new &&
						this.state.columns_knowledgebase_new.length > 0 ? (
							<div className="mvx-backend-datatable-wrapper">
								<DataTable
									columns={
										this.state.columns_knowledgebase_new
									}
									data={this.state.display_list_knowladgebase}
									selectableRows
									onSelectedRowsChange={
										this.onSelectedRowsChange
									}
									pagination
								/>
							</div>
						) : (
							''
						)}
					</div>
				)}
			</div>
		) : name === 'store-review' ? (
			<div className="mvx-module-grid">
				<div className="mvx-search-and-multistatus-wrap">
					<ul className="mvx-multistatus-ul">
						<li className="mvx-multistatus-item">
							<div className="mvx-multistatus-check-all">
								{appLocalizer.global_string.all} (
								{this.state.list_of_store_review.length})
							</div>
						</li>
						<li clasName="mvx-multistatus-item mvx-divider"></li>
					</ul>
					<div className="mvx-header-search-section">
						<label>
							<i className="mvx-font icon-search"></i>
						</label>
						<input
							type="text"
							placeholder={
								appLocalizer.workboard_string.workboard8
							}
							onChange={(e) => this.handleSearchVendorReview(e)}
						/>
					</div>
				</div>
				<div className="mvx-wrap-bulk-all-date">
					<Select
						placeholder={appLocalizer.global_string.bulk_action}
						options={appLocalizer.select_option_delete}
						isClearable={true}
						className="mvx-wrap-bulk-action"
						onChange={this.handleReviewBulkStatus}
					/>
				</div>

				{this.state.columns_store_review &&
				this.state.columns_store_review.length > 0 ? (
					<div className="mvx-backend-datatable-wrapper">
						<DataTable
							columns={this.state.columns_store_review}
							data={this.state.list_of_store_review}
							selectableRows
							onSelectedRowsChange={this.handleselectreviews}
							pagination
						/>
					</div>
				) : (
					''
				)}
			</div>
		) : name === 'report-abuse' ? (
			<div className="mvx-module-grid">
				<div className="mvx-wrap-bulk-all-date">
					<Select
						placeholder={appLocalizer.workboard_string.workboard30}
						options={this.state.show_vendor_name}
						isClearable={true}
						className="mvx-wrap-bulk-action"
						onChange={this.handleVendorSearchAbuse}
					/>
					<Select
						placeholder={appLocalizer.workboard_string.workboard31}
						options={this.state.product_list_option}
						isClearable={true}
						className="mvx-wrap-bulk-action"
						onChange={this.handleProductSearchAbuse}
					/>
				</div>

				{this.state.columns_report_abuse &&
				this.state.columns_report_abuse.length > 0 ? (
					<div className="mvx-backend-datatable-wrapper">
						<DataTable
							columns={this.state.columns_report_abuse}
							data={this.state.list_of_report_abuse}
							selectableRows
							onSelectedRowsChange={this.handleselectabuse}
							pagination
						/>
					</div>
				) : (
					''
				)}
			</div>
		) : name === 'question-ans' ? (
			<div className="mvx-module-grid">
				<div className="mvx-search-and-multistatus-wrap">
					<ul className="mvx-multistatus-ul">
						<li className="mvx-multistatus-item">
							<div className="mvx-multistatus-check-all">
								{appLocalizer.global_string.all} (
								{this.state.list_of_publish_question.length})
							</div>
						</li>
						<li className="mvx-multistatus-item mvx-divider"></li>
						<li className="mvx-multistatus-item">
							<div className="mvx-multistatus-check-pending status-active">
								{appLocalizer.global_string.pending} (
								{this.state.list_of_pending_question.length})
							</div>
						</li>
					</ul>

					<div className="mvx-header-search-section">
						<label>
							<i className="mvx-font icon-search"></i>
						</label>
						<input
							type="text"
							placeholder={
								appLocalizer.workboard_string.workboard32
							}
							onChange={(e) => this.handleQuestionSearch(e)}
						/>
					</div>
				</div>

				<div className="mvx-wrap-bulk-all-date">
					<Select
						placeholder={appLocalizer.workboard_string.workboard33}
						options={appLocalizer.question_selection_wordpboard}
						isClearable={true}
						className="mvx-wrap-bulk-action"
						onChange={this.handleQuestionBulkStatusChange}
					/>
					<Select
						placeholder={appLocalizer.workboard_string.workboard34}
						isMulti
						options={
							appLocalizer.question_product_selection_wordpboard
						}
						isClearable={true}
						className="mvx-wrap-bulk-action"
						onChange={this.handleQuestionBulkStatusChange}
					/>
				</div>

				{this.state.columns_questions_new &&
				this.state.columns_questions_new.length > 0 ? (
					<div className="mvx-backend-datatable-wrapper">
						<DataTable
							columns={this.state.columns_questions_new}
							data={this.state.list_of_publish_question}
							selectableRows
							pagination
						/>
					</div>
				) : (
					''
				)}
			</div>
		) : (
			''
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
export default MVXworkboard;
