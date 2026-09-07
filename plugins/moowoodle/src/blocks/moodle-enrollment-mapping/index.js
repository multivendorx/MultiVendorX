import { render, useEffect, useState } from '@wordpress/element';
import {
	RadioControl,
	SelectControl,
	Spinner,
	Notice,
	Disabled,
	ToggleControl,
	ComboboxControl
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import './tab.scss';

const ProTag = ({ isProVersion }) => {
	return !isProVersion ? (
		<span className="pro-tag">{__('Pro', 'moowoodle')}</span>
	) : null;
};

const ProductTab = () => {
	const [linkType, setLinkType] = useState(
		moowoodleProduct.linkType || ''
	);

	const [linkedItemId, setLinkedItemId] = useState(
		String(moowoodleProduct.linkedItemId || '')
	);

	const [expiryDays, setExpiryDays] = useState(
		moowoodleProduct.expiryDays || ''
	);

	const [expiryType, setExpiryType] = useState(
		moowoodleProduct.expiryType || ''
	);

	// Enrollment Extension fields.
	const [enableEnrollmentExtension, setEnableEnrollmentExtension] =
		useState(Boolean(moowoodleProduct.enableEnrollmentExtension));

	const [extensionDays, setExtensionDays] = useState(
		moowoodleProduct.extensionDays || ''
	);

	const [extensionCost, setExtensionCost] = useState(
		moowoodleProduct.extensionCost || ''
	);

	const [options, setOptions] = useState([]);
	const [loading, setLoading] = useState(false);

	useEffect(() => {
		if (!linkType) {
			setOptions([]);
			return;
		}

		setLoading(true);

		const endpoint = 'course' === linkType ? 'courses' : 'cohorts';

		apiFetch({
			path: `/moowoodle/v1/${endpoint}?unlinked_resources_for=${moowoodleProduct.postId}`,
			method: 'GET',
		})
			.then((response) => {
				const formattedOptions = (response.items || []).map((item) => ({
					label: [item.fullname, item.cohort_name]
						.filter(Boolean)
						.join(' || '),

					value: String(item.id),
				}));

				setOptions(formattedOptions);

				if (response.selected_id) {
					setLinkedItemId(String(response.selected_id));
				}
			})
			.catch((error) => {
				console.error('MooWoodle REST API Error:', error);
			})
			.finally(() => {
				setLoading(false);
			});
	}, [linkType]);

	return (
		<>
			<div className="options_group">
				<div className="form-field components-radio">
					<label htmlFor="linked_item">
						{__('Link Type', 'moowoodle')}
					</label>

					<RadioControl
						selected={linkType}
						options={[
							{
								label: __('Course', 'moowoodle'),
								value: 'course',
							},
						]}
						onChange={(value) => {
							setLinkType(value);
							setLinkedItemId('');
						}}
					/>

					<Disabled isDisabled={!moowoodleProduct.khali_dabba}>
						<ProTag isProVersion={moowoodleProduct.khali_dabba} />
						<RadioControl
							selected={linkType}
							options={[
								{
									label: __('Cohort', 'moowoodle'),
									value: 'cohort',
								},
							]}
							onChange={(value) => {
								setLinkType(value);
								setLinkedItemId('');
							}}
						/>
					</Disabled>
				</div>

				{loading && (
					<p className="form-field">
						<Spinner />
					</p>
				)}

				{!!linkType && !loading && (
					<p className="form-field">
						<label htmlFor="linked_item">
							{__('Select Item', 'moowoodle')}
						</label>
						<div className="select-item">
							<ComboboxControl
								value={linkedItemId}
								options={options}
								onChange={(value) => {
									setLinkedItemId(value || '');
								}}
								placeholder={__('Search or select an item...', 'moowoodle')}
							/>
						</div>
					</p>
				)}

				<Disabled isDisabled={!moowoodleProduct.khali_dabba}>
					<>
						{/* Expiry Days */}
						<p className="form-field input">
							<label htmlFor="course_expiry_days">
								{__('Expire Access After (Days)', 'moowoodle')}
							</label>

							<input
								type="number"
								id="course_expiry_days"
								min="0"
								value={expiryDays}
								onChange={(event) =>
									setExpiryDays(event.target.value)
								}
							/>
							<ProTag isProVersion={moowoodleProduct.khali_dabba} />
						</p>

						{/* Expiry Type */}
						<p className="form-field select">
							<label htmlFor="course_expiry_type">
								{__('On Course Expiration', 'moowoodle')}
							</label>

							<SelectControl
								value={expiryType}
								options={[
									{
										label: __('Select Type', 'moowoodle'),
										value: '',
									},
									{
										label: __('Suspend', 'moowoodle'),
										value: 'suspend',
									},
									{
										label: __('Unenroll', 'moowoodle'),
										value: 'unenroll',
									},
								]}
								onChange={setExpiryType}
							/>
							<ProTag isProVersion={moowoodleProduct.khali_dabba} />
						</p>

						{/* Enrollment Extension */}
						<div className="enrollment-extension">
							<p className="form-field">
								<label htmlFor="course_expiry_type">
									{__('Enable Enrollment Extension', 'moowoodle')}
								</label>
								<ToggleControl
									checked={enableEnrollmentExtension}
									onChange={(value) => {
										setEnableEnrollmentExtension(value);

										if (!value) {
											setExtensionDays('');
											setExtensionCost('');
										}
									}}
								/>
								<ProTag isProVersion={moowoodleProduct.khali_dabba} />
							</p>
							{enableEnrollmentExtension && (
								<>
									{/* Extension Days */}
									<p className="form-field input">
										<label htmlFor="enrollment_extension_days">
											{__(
												'Extension Days',
												'moowoodle'
											)}
										</label>

										<input
											type="number"
											id="enrollment_extension_days"
											min="1"
											value={extensionDays}
											onChange={(event) =>
												setExtensionDays(
													event.target.value
												)
											}
										/>
									</p>

									{/* Extension Cost */}
									<p className="form-field input">
										<label htmlFor="enrollment_extension_cost">
											{__(
												'Extension Cost',
												'moowoodle'
											)}
										</label>

										<input
											type="number"
											id="enrollment_extension_cost"
											min="0"
											step="0.01"
											value={extensionCost}
											onChange={(event) =>
												setExtensionCost(
													event.target.value
												)
											}
										/>
									</p>
								</>
							)}
						</div>

						{!moowoodleProduct.khali_dabba && (
							<span className="description">
								{__(
									'Available in MooWoodle Pro.',
									'moowoodle'
								)}
							</span>
						)}
					</>
				</Disabled>

				<Notice
					status="info"
					isDismissible={false}
					actions={[
						{
							label: __(
								'Synchronize Moodle data',
								'moowoodle'
							),
							url: moowoodleProduct.syncUrl,
							variant: 'link',
						},
					]}
				>
					<p>
						{__(
							"Can't find your course or cohort?",
							'moowoodle'
						)}
					</p>
				</Notice>
			</div>

			<input type="hidden" name="link_type" value={linkType} />
			<input type="hidden" name="linked_item_id" value={linkedItemId} />
			<input type="hidden" name="course_expiry_days" value={expiryDays} />
			<input type="hidden" name="course_expiry_type" value={expiryType} />
			<input type="hidden" name="enable_enrollment_extension" value={enableEnrollmentExtension ? 'yes' : 'no'} />
			<input type="hidden" name="enrollment_extension_days" value={extensionDays} />
			<input type="hidden" name="enrollment_extension_cost" value={extensionCost} />
			<input type="hidden" name="product_meta_nonce" value={moowoodleProduct.productMetaNonce} />

		</>
	);
};

document.addEventListener('DOMContentLoaded', () => {
	const container = document.getElementById(
		'moodle-enrollment-mapping-tab'
	);

	if (container) {
		render(<ProductTab />, container);
	}
});