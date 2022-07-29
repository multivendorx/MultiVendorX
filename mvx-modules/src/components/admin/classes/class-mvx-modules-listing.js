/* global appLocalizer */
import React, { Component } from 'react';
import axios from 'axios';
import Select from 'react-select';
import PuffLoader from 'react-spinners/PuffLoader';
import { css } from '@emotion/react';
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
			items: [],
			open_model: false,
			open_model_dynamic: [],
			total_number_of_module: 0,
		};
		// when click on checkbox
		this.handleOnChange = this.handleOnChange.bind(this);
		// popup close for paid module
		this.handleClose = this.handleClose.bind(this);
		// popup close for required plugin inactive popup
		this.handleClose_dynamic = this.handleClose_dynamic.bind(this);
		// search select module trigger
		this.handleModuleSearch = this.handleModuleSearch.bind(this);
		this.handleModuleSearchByCategory =
			this.handleModuleSearchByCategory.bind(this);
		this.mvx_search_different_module_status =
			this.mvx_search_different_module_status.bind(this);
	}

	mvx_search_different_module_status(status) {
		// multiple module status
		axios
			.get(
				`${appLocalizer.apiUrl}/mvx_module/v1/get_as_per_module_status`,
				{
					params: { module_status: status },
				}
			)
			.then((response) => {
				this.setState({
					items: response.data,
				});
			});
	}

	handleModuleSearch(e) {
		axios({
			url: `${appLocalizer.apiUrl}/mvx_module/v1/module_lists?module_id=${e.target.value}`,
		}).then((response) => {
			this.setState({
				items: response.data,
			});
		});
	}

	handleModuleSearchByCategory(e) {
		if (e) {
			axios
				.get(
					`${appLocalizer.apiUrl}/mvx_module/v1/search_module_lists`,
					{
						params: { category: e.label },
					}
				)
				.then((response) => {
					this.setState({
						items: response.data,
					});
				});
		} else {
			Promise.all([
				fetch(
					`${appLocalizer.apiUrl}/mvx_module/v1/module_lists?module_id=all`
				).then((res) => res.json()),
			])
				.then(([product]) => {
					this.setState({
						items: product,
					});
				})
				.catch((error) => {});
		}
	}

	// popup close for paid module
	handleClose() {
		this.setState({
			open_model: false,
		});
	}
	// popup close for required plugin inactive popup
	handleClose_dynamic() {
		const add_module_false = new Array(this.state.items.length).fill(false);
		this.setState({
			open_model_dynamic: add_module_false,
		});
	}
	// when click on checkbox
	handleOnChange(
		event,
		tab,
		plan,
		is_plugin_active,
		doc_id,
		items,
		parent_index,
		sub_index,
		module_id
	) {
		if (plan === 'pro') {
			this.setState({
				open_model: true,
			});
		} else if (!is_plugin_active) {
		} else {
			// If everything works fine then checkbox trigger
			items[parent_index].options[sub_index].is_active =
				event.target.checked;

			this.setState({
				items,
			});

			axios({
				method: 'post',
				url: `${appLocalizer.apiUrl}/mvx_module/v1/checkbox_update`,
				data: {
					module_id,
					is_checked: event.target.checked,
				},
			}).then((res) => {});
		}
	}

	componentDidMount() {
		Promise.all([
			fetch(
				new URLSearchParams(window.location.hash).get('name') ? `${appLocalizer.apiUrl}/mvx_module/v1/module_lists?module_id=${new URLSearchParams(window.location.hash).get('name')}` : `${appLocalizer.apiUrl}/mvx_module/v1/module_lists?module_id=all`
			).then((res) => res.json()),
		])
			.then(([product]) => {
				this.setState({
					items: product,
				});
			})
			.catch((error) => {});
		// fetch total number of modules
		axios
			.get(`${appLocalizer.apiUrl}/mvx_module/v1/modules_count`)
			.then((response) => {
				this.setState({
					total_number_of_module: response.data,
				});
			});
	}

	render() {
		return (
			<div className="mvx-general-wrapper mvx-modules">
				<HeaderSection />
				<div className="mvx-container">
					<div className="mvx-middle-container-wrapper">
						<div className="mvx-tab-description-start">
							<div className="mvx-tab-name">
								{appLocalizer.module_page_string.module1}
							</div>
							<p>{appLocalizer.module_page_string.module2}</p>
						</div>

						<div className="mvx-search-and-multistatus-wrap">
							<ul className="mvx-multistatus-ul">
								<li className="mvx-multistatus-item">
									<div
										className="mvx-total-module-name-and-count"
										onClick={(e) =>
											this.mvx_search_different_module_status(
												'all'
											)
										}
									>
										<span className="mvx-total-modules-name">
											{
												appLocalizer.module_page_string
													.module3
											}
										</span>
										<span className="mvx-total-modules-count">
											{this.state.total_number_of_module}
										</span>
									</div>
								</li>
								<li className="mvx-multistatus-item mvx-divider"></li>
								<li className="mvx-multistatus-item">
									<Button
										onClick={(e) =>
											this.mvx_search_different_module_status(
												'active'
											)
										}
									>
										{
											appLocalizer.module_page_string
												.module4
										}
									</Button>
								</li>
								<li className="mvx-multistatus-item mvx-divider"></li>
								<li className="mvx-multistatus-item">
									<Button
										onClick={(e) =>
											this.mvx_search_different_module_status(
												'inactive'
											)
										}
									>
										{
											appLocalizer.module_page_string
												.module5
										}
									</Button>
								</li>
							</ul>
							<div className="mvx-header-search-section">
								<label>
									<i className="mvx-font icon-search"></i>
								</label>
								<input
									type="text"
									onChange={(e) => this.handleModuleSearch(e)}
									placeholder={
										appLocalizer.module_page_string.module6
									}
								/>
							</div>
						</div>

						<div className="mvx-wrap-bulk-all-date">
							<Select
								placeholder={
									appLocalizer.module_page_string.module7
								}
								options={
									appLocalizer.select_module_category_option
								}
								isClearable={true}
								className="mvx-wrap-bulk-action"
								onChange={(e) =>
									this.handleModuleSearchByCategory(e)
								}
							/>
						</div>

						{this.state.items.length === 0 ? (
							<PuffLoader
								css={override}
								color={'#cd0000'}
								size={200}
								loading={true}
							/>
						) : (
							this.state.items.map((student1, index1) => (
								<div className="mvx-module-list-start">
									<div className="mvx-module-list-container">
									<div className="mvx-text-with-right-side-line-wrapper">

										<div className="mvx-text-with-right-side-line">
											{student1.label}
										</div>
										
										<hr role="presentation"></hr>
									</div>
									
										<div className="mvx-module-option-row">
											{student1.options.map(
												(student, index) => (
													<div className="mvx-module-section-options-list">
														<div
															className={`mvx-module-settings-box ${
																student.is_active
																	? 'active'
																	: ''
															}`}
														>
															<div className="mvx-module-icon">
																<i
																	className={`mvx-font ${student.thumbnail_dir}`}
																></i>
															</div>

															<header>
																<div className="mvx-module-list-label-text">
																	{
																		student.name
																	}
																	{student.plan ===
																	'pro' ? (
																		<span className="mvx-module-section-pro-badge">
																			{
																				appLocalizer.pro_text
																			}
																		</span>
																	) : (
																		''
																	)}
																</div>
																<p>
																	{
																		student.description
																	}
																</p>
															</header>
															{student.required_plugin_list ? (
																<div className="mvx-module-require-name">
																	{
																		appLocalizer
																			.module_page_string
																			.module8
																	}
																</div>
															) : (
																''
															)}
															<ul>
																{student.required_plugin_list &&
																	student.required_plugin_list.map(
																		(
																			company,
																			index_req
																		) => (
																			<li>
																				{company.is_active ? (
																					<div className="mvx-module-active-plugin-class">
																						<img
																							src={
																								appLocalizer.right_logo
																							}
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
																					href={
																						company.plugin_link
																					}
																					className="mvx-third-party-plugin-link-class"
																				>
																					{
																						company.plugin_name
																					}
																				</a>
																			</li>
																		)
																	)}
															</ul>
															<div className="mvx-module-current-status">
																{student.is_active &&
																student.mod_link ? (
																	<a
																		href={
																			student.mod_link
																		}
																		className="mvx-btn btn-border"
																	>
																		{
																			appLocalizer.settings_text
																		}
																	</a>
																) : (
																	''
																)}
																<a
																	href={
																		student.doc_link
																	}
																	className="mvx-btn btn-border"
																>
																	{
																		appLocalizer.documentation_text
																	}
																</a>
																<div className="mvx-toggle-checkbox-content">
																	<input
																		type="checkbox"
																		className="mvx-toggle-checkbox"
																		id={`mvx-toggle-switch-${student.id}`}
																		name="modules[]"
																		value={
																			student.id
																		}
																		checked={
																			student.is_active
																				? true
																				: false
																		}
																		onChange={(
																			e
																		) =>
																			this.handleOnChange(
																				e,
																				index,
																				student.plan,
																				student.is_required_plugin_active,
																				student.doc_id,
																				this
																					.state
																					.items,
																				index1,
																				index,
																				student.id
																			)
																		}
																	/>
																	<label
																		htmlFor={`mvx-toggle-switch-${student.id}`}
																	></label>
																</div>
															</div>
															<Dialog
																open={
																	this.state
																		.open_model_dynamic[
																		index
																	]
																}
																onClose={
																	this
																		.handleClose_dynamic
																}
																aria-labelledby="form-dialog-title"
															>
																<DialogTitle id="form-dialog-title">
																	<div className="mvx-module-dialog-title">
																		{
																			appLocalizer
																				.module_page_string
																				.module9
																		}
																	</div>
																</DialogTitle>
																<DialogContent>
																	<DialogContentText>
																		<div className="mvx-module-dialog-content">
																			{
																				appLocalizer
																					.module_page_string
																					.module10
																			}{' '}
																			{
																				student.name
																			}{' '}
																			module.
																		</div>
																	</DialogContentText>
																</DialogContent>
																<DialogActions>
																	<Button
																		onClick={
																			this
																				.handleClose_dynamic
																		}
																		color="primary"
																	>
																		{
																			appLocalizer
																				.module_page_string
																				.module12
																		}
																	</Button>
																</DialogActions>
															</Dialog>
														</div>
													</div>
												)
											)}
										</div>
									</div>
								</div>
							))
						)}

						<Dialog
							open={this.state.open_model}
							onClose={this.handleClose}
							aria-labelledby="form-dialog-title"
						>
							<DialogTitle id="form-dialog-title">
								<div className="mvx-module-dialog-title">
									{appLocalizer.module_page_string.module13}
								</div>
							</DialogTitle>
							<DialogContent>
								<DialogContentText>
									<div className="mvx-module-dialog-content">
										{
											appLocalizer.module_page_string
												.module14
										}{' '}
										<a
											href={
												appLocalizer.global_string
													.multivendorx_url
											}
										>
											{
												appLocalizer.global_string
													.multivendorx_text
											}
										</a>{' '}
										{
											appLocalizer.module_page_string
												.module15
										}
										.
									</div>
								</DialogContentText>
							</DialogContent>
							<DialogActions>
								<Button
									onClick={this.handleClose}
									color="primary"
								>
									{appLocalizer.module_page_string.module12}
								</Button>
							</DialogActions>
						</Dialog>
					</div>

					<BannerSection />
				</div>
			</div>
		);
	}
}
export default MVX_Module_Listing;
