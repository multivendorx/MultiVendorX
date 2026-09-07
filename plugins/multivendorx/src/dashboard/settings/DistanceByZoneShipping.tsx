/* global appLocalizer */
import React, { useState, useEffect } from 'react';
import axios from 'axios';

import {
	PopupComponent,
	SectionComponent,
	FormGroupComponent,
	FormGroupWrapperComponent,
	NoticeComponent,
} from '@zyra/components';
import { TextInput, ButtonInput, ToggleInput } from '@zyra/inputs';
import { getApiLink, useModules } from '@zyra/core';
import { TableCard, TableRow } from '@zyra/table';
import { __ } from '@wordpress/i18n';
import { applyFilters, doAction } from '@wordpress/hooks';

interface ShippingMethod {
	id: string;
	instance_id: number;
	title: string;
	method_id: string;
	enabled?: boolean;
	settings?: Record<string, unknown>;
}

type Zone = {
	id: number;
	zone_name: string;
	formatted_zone_location: string;
	shipping_methods: ShippingMethod[];
	zone_id: number;
};

interface FormData {
	shippingMethod: string;
	localPickupCost: string;
	freeShippingType: string;
	minOrderCost: string;
	flatRateCost: string;
	flatRateClassCost: string;
}

interface DistanceByZoneShippingProps {
	id: string | number;
}

const DistanceByZoneShipping: React.FC<DistanceByZoneShippingProps> = ({
	id,
}) => {
	let store_id = appLocalizer.store_id ? appLocalizer.store_id : id;
	const { modules } = useModules();
	const [data, setData] = useState<Zone[]>([]);
	const [addShipping, setAddShipping] = useState<boolean>(false);
	const [selectedZone, setSelectedZone] = useState<Zone | null>(null);
	const [isEditing, setIsEditing] = useState<boolean>(false);
	const [editingMethod, setEditingMethod] = useState<ShippingMethod | null>(
		null
	);
	const [isLoadingShipping, setIsLoadingShipping] = useState(false);
	const [formData, setFormData] = useState<FormData>({
		shippingMethod: '',
		localPickupCost: '',
		freeShippingType: '',
		minOrderCost: '',
		flatRateCost: '',
		flatRateClassCost: '',
	});

	useEffect(() => {
		fetchZones();
	}, []);

	const fetchZones = async () => {
		try {
			const res = await axios({
				method: 'GET',
				url: getApiLink(appLocalizer, 'zone-shipping'),
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
				params: { store_id: store_id },
			});

			const zonesObject: Record<string, Zone> = res?.data || {};
			const zonesArray = Object.values(zonesObject);

			setData(zonesArray);
		} catch (err) {
			console.error('Error loading zones:', err);
		}
	};

	const handleAdd = (zone: Zone) => {
		setSelectedZone(zone);
		setAddShipping(true);
		setIsEditing(false);
		setEditingMethod(null);
		setFormData({
			shippingMethod: '',
			localPickupCost: '',
			freeShippingType: '',
			minOrderCost: '',
			flatRateCost: '',
			flatRateClassCost: '',
		});
	};

	const handleEdit = async (method: ShippingMethod) => {
		setIsEditing(true);
		setEditingMethod(method);

		const zoneWithMethod = data.find((zone) => {
			const methods = Object.values(zone.shipping_methods || {});
			return methods.some((m: ShippingMethod) => m.id === method.id);
		});

		setSelectedZone(zoneWithMethod || null);
		setIsLoadingShipping(true);

		if (!zoneWithMethod) {
			return;
		}

		try {
			const response = await axios({
				method: 'GET',
				url: getApiLink(
					appLocalizer,
					`zone-shipping/${zoneWithMethod.zone_id}`
				),
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
				params: {
					store_id: store_id,
					method_id: method.id,
					zone_id: zoneWithMethod.zone_id,
				},
			});
			if (response.data && response.data.settings) {
				const data = response.data;
				const methodConfig = data.settings;

				const form = {
					shippingMethod: data.method_id,
					localPickupCost: '',
					freeShippingType: '',
					minOrderCost: '',
					flatRateCost: '',
					flatRateClassCost: '',
				};

				if (data.method_id === 'local_pickup') {
					form.localPickupCost = methodConfig.cost || '';
				} else if (data.method_id === 'free_shipping') {
					form.freeShippingType = methodConfig.requires;
					form.minOrderCost = methodConfig.min_amount || '';
				} else if (data.method_id === 'flat_rate') {
					form.flatRateCost = methodConfig.cost || '';
					form.flatRateClassCost = methodConfig.class_cost || '';
				}

				setFormData(form);
				setAddShipping(true);
			}
		} catch (err) {
			console.error('Error loading shipping method:', err);
			alert('Error loading shipping method');
		} finally {
			setIsLoadingShipping(false);
		}
	};
	const handleClosePopup = () => {
		setAddShipping(false);

		setFormData({
			shippingMethod: '',
			localPickupCost: '',
			freeShippingType: '',
			minOrderCost: '',
			flatRateCost: '',
			flatRateClassCost: '',
		});
	};
	const handleDelete = async (method: ShippingMethod, zone: Zone) => {
		if (!confirm(`Are you sure you want to delete "${method.title}"?`)) {
			return;
		}

		try {
			const response = await axios({
				method: 'DELETE',
				url: getApiLink(appLocalizer, `zone-shipping/${zone.zone_id}`),
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
				data: {
					store_id: store_id,
					zone_id: zone.zone_id,
					method_id: method.id,
				},
			});

			if (response.data.success) {
				fetchZones();
			} else {
				alert(`Failed to delete "${method.title}".`);
			}
		} catch (err) {
			console.error('Error deleting shipping method:', err);
			alert(`Error deleting "${method.title}".`);
		}
	};

	const handleChange = (key: string, value: string) => {
		setFormData((prev) => ({ ...prev, [key]: value }));
	};

	const handleSave = async () => {
		if (!selectedZone) {
			return;
		}
		try {
			const shippingData = { settings: {} };

			if (formData.shippingMethod === 'local_pickup') {
				shippingData.settings = {
					title: 'Local Pickup',
					cost: formData.localPickupCost || '0',
					description: 'Local pickup shipping method',
				};
			} else if (formData.shippingMethod === 'free_shipping') {
				shippingData.settings = {
					title: 'Free Shipping',
					requires: formData.freeShippingType || '',
					min_amount: formData.minOrderCost || '0',
					description: 'Free shipping method',
				};
			} else if (formData.shippingMethod === 'flat_rate') {
				shippingData.settings = {
					title: 'Flat Rate',
					cost: formData.flatRateCost || '0',
					class_cost: formData.flatRateClassCost || '',
					description: 'Flat rate shipping method',
				};
			}

			shippingData.settings = applyFilters(
				'multivendorx_zone_shipping_settings',
				shippingData.settings
			);

			const isUpdate = isEditing && editingMethod;
			const url = isUpdate
				? getApiLink(
					appLocalizer,
					`zone-shipping/${selectedZone.zone_id}`
				)
				: getApiLink(appLocalizer, 'zone-shipping');

			const requestData = {
				store_id: store_id,
				zone_id: selectedZone.zone_id,
				method_id: isUpdate
					? editingMethod.id
					: formData.shippingMethod,
				settings: shippingData.settings,
			};

			if (isUpdate) {
				requestData.instance_id = editingMethod.instance_id;
			}
			doAction('multivendorx_zone_shipping_after_save');

			const response = await axios({
				method: 'POST',
				url: url,
				headers: {
					'X-WP-Nonce': appLocalizer.nonce,
					'Content-Type': 'application/json',
				},
				data: requestData,
			});

			if (response.data.success) {
				await fetchZones();
				setAddShipping(false);
				setFormData({
					shippingMethod: '',
					localPickupCost: '',
					freeShippingType: '',
					minOrderCost: '',
					flatRateCost: '',
					flatRateClassCost: '',
				});
				setEditingMethod(null);
			}
			setIsEditing(false);
		} catch (err) {
			console.error(
				'Error ' +
				(isEditing ? 'updating' : 'adding') +
				' shipping method:',
				err
			);
			alert(
				__(
					'Error ' +
					(isEditing ? 'updating' : 'adding') +
					' shipping method',
					'multivendorx'
				)
			);
		}
	};

	const headers = {
		zone_name: {
			label: __('Zone Name', 'multivendorx'),
			render: (row: TableRow) => row.zone_name || '—',
		},
		formatted_zone_location: {
			label: __('Region(s)', 'multivendorx'),
			render: (row: TableRow) => row.formatted_zone_location || '—',
		},
		shipping_methods: {
			label: __('Shipping Method(s)', 'multivendorx'),
			render: (row: TableRow) => {
				const zone = row as Zone;
				const methodsObj = zone.shipping_methods || {};
				const methodsArray = Object.values(methodsObj);

				if (methodsArray.length === 0) {
					return (
						<div>
							<div>No shipping methods</div>
							<button
								className="admin-btn btn-purple"
								onClick={() => handleAdd(zone)}
							>
								<i className="adminfont-plus"></i>{' '}
								{__('Add Shipping Method', 'multivendorx')}
							</button>
						</div>
					);
				}
				return (
					<div className="shipping-method-wrapper">
						{methodsArray.map((method: ShippingMethod) => (
							<div
								key={method.instance_id}
								className="shipping-method"
							>
								<div className="admin-badge yellow">
									{method.title}
								</div>
								<i
									onClick={() => handleEdit(method)}
									className="admin-badge blue adminfont-edit"
								></i>
								<i
									onClick={() => handleDelete(method, zone)}
									className="admin-badge red adminfont-delete"
								></i>
							</div>
						))}

						<button
							className="admin-btn btn-purple"
							onClick={() => handleAdd(zone)}
							style={{ marginTop: '10px' }}
						>
							<i className="adminfont-plus"></i>{' '}
							{__('Add New Method', 'multivendorx')}
						</button>
					</div>
				);
			},
		},
	};

	const rows: TableRow[] = data.map((zone) => ({
		...zone,
	}));

	return (
		<>
			<SectionComponent
				title={__('Zone-wise Shipping Configuration', 'multivendorx')}
			/>
			<FormGroupComponent>
				<TableCard
					headers={headers}
					rows={rows}
					isLoading={false}
					showMenu={false}
					onQueryUpdate={() => { }}
					emptyMessage={__('No shipping zones found', 'multivendorx')}
				/>
			</FormGroupComponent>
			{addShipping && selectedZone && (
				<PopupComponent
					open={addShipping}
					width='80%'
					height="90%"
					onClose={handleClosePopup}
					header={{
						icon: 'shipping',
						title: `${isEditing
							? __('Edit Shipping', 'multivendorx')
							: __('Add Shipping', 'multivendorx')
							} - ${selectedZone.zone_name}`,
					}}
					footer={
						<ButtonInput
							buttons={[
								{
									icon: 'close',
									text: __('Cancel', 'multivendorx'),
									color: 'red',
									onClick: () => setAddShipping(false),
								},
								{
									icon: 'save',
									text: isEditing
										? __('Update', 'multivendorx')
										: __('Save', 'multivendorx'),
									onClick: handleSave,
								},
							]}
						/>
					}
				>
					<FormGroupWrapperComponent>
						<FormGroupComponent
							label={__('Shipping Method', 'multivendorx')}
						>
							<ToggleInput
								value={formData.shippingMethod}
								onChange={(val: string) => {
									if (!isEditing) {
										handleChange('shippingMethod', val);
									}
								}}
								options={
									isEditing
										? [
											{
												key: formData.shippingMethod,
												value: formData.shippingMethod,
												label: __(
													formData.shippingMethod
														.replace('_', ' ')
														.replace(
															/\b\w/g,
															(c) =>
																c.toUpperCase()
														),
													'multivendorx'
												),
											},
										]
										: applyFilters(
											'multivendorx_zone_shipping_methods',
											[
												{
													key: 'local_pickup',
													value: 'local_pickup',
													label: __(
														'Local pickup',
														'multivendorx'
													),
												},
												{
													key: 'free_shipping',
													value: 'free_shipping',
													label: __(
														'Free shipping',
														'multivendorx'
													),
												},
												{
													key: 'flat_rate',
													value: 'flat_rate',
													label: __(
														'Flat Rate',
														'multivendorx'
													),
												},
											],
											modules
										)
								}
								disabled={isEditing}
							/>
						</FormGroupComponent>

						{/* Local Pickup */}
						{formData.shippingMethod === 'local_pickup' && (
							<FormGroupComponent label={__('Cost', 'multivendorx')}>
								<TextInput
									type="number"
									name="localPickupCost"
									placeholder={__("Enter cost", 'multivendorx')}
									value={formData.localPickupCost}
									onChange={(val: string) =>
										handleChange('localPickupCost', val)
									}
								/>
								<NoticeComponent
									type="info"
									displayPosition="inline-notice"
									message={__(
										'Set the fee customers need to pay when choosing Local Pickup.',
										'multivendorx'
									)}
								/>
							</FormGroupComponent>
						)}

						{/* Free Shipping */}
						{formData.shippingMethod === 'free_shipping' && (
							<>
								<div className="form-group">
									<ToggleInput
										value={formData.freeShippingType}
										onChange={(val: string) =>
											handleChange(
												'freeShippingType',
												val
											)
										}
										options={[
											{
												key: 'min_order',
												value: 'min_order',
												label: __(
													'Min Order',
													'multivendorx'
												),
											},
											{
												key: 'coupon',
												value: 'coupon',
												label: __(
													'Coupon',
													'multivendorx'
												),
											},
										]}
									/>
								</div>
								{formData.freeShippingType === 'min_order' && (
									<div className="form-group">
										<label className="font-medium">
											{__(
												'Minimum Order Cost',
												'multivendorx'
											)}
										</label>
										<TextInput
											type="number"
											name="minOrderCost"
											placeholder="Enter minimum order cost"
											value={formData.minOrderCost}
											onChange={(value) =>
												handleChange(
													'minOrderCost',
													value
												)
											}
										/>
									</div>
								)}
								<NoticeComponent
									type="info"
									displayPosition="inline-notice"
									message={__(
										'Free shipping is applied when the order reaches a minimum amount. Enter that value in the Minimum Order Amount field.',
										'multivendorx'
									)}
								/>
							</>
						)}

						{/* Flat Rate */}
						{formData.shippingMethod === 'flat_rate' && (
							<>
								<FormGroupComponent label={__('Cost', 'multivendorx')}>
									<TextInput
										type="number"
										name="flatRateCost"
										placeholder={__("Enter cost", 'multivendorx')}
										value={formData.flatRateCost}
										onChange={(value) =>
											handleChange('flatRateCost', value)
										}
									/>
									<div className="desc">
										{__('Enter a ', 'multivendorx')}
										<b>
											{__('fixed amount', 'multivendorx')}
										</b>
										{__(' or use a ', 'multivendorx')}
										<b>{__('formula', 'multivendorx')}</b>
										{__(
											' if you want shipping to change based on the number of items or order value.',
											'multivendorx'
										)}
									</div>
									<NoticeComponent
										type="info"
										displayPosition="inline-notice"
										message={__(
											"Base shipping cost: <b>$10</b> + Heavy item adjustment: <b>$20</b>  Total shipping: <b>$30</b> <br> Leave empty if no additional cost is needed for this shipping class.",
											'multivendorx'
										)}
									/>
								</FormGroupComponent>

								<FormGroupComponent
									label={__(
										'Cost of Shipping Class',
										'multivendorx'
									)}
								>
									<TextInput
										type="text"
										name="flatRateClassCost"
										placeholder={__("Enter class cost", 'multivendorx')}
										value={formData.flatRateClassCost}
										onChange={(value) =>
											handleChange(
												'flatRateClassCost',
												value
											)
										}
									/>
									<div className="desc">
										{__('This amount is ', 'multivendorx')}
										<b>
											{__(
												'added to the base shipping cost.',
												'multivendorx'
											)}
										</b>
									</div>
									<NoticeComponent
										type="info"
										displayPosition="inline-notice"
										message={__(
											"<b>10 </b> Fixed base shipping cost <br><b> 10 + (2 × [qty]) </b> Base cost + $2 per item <br><b>10 + [fee percent='10'] - </b>  Base cost + 10% of order value <br>  Use <b>[qty]</b> for item count and <b>[fee percent='x']</b> for percentage-based fees. You can also set <b>min_fee or max_fee.</b> <br> If you're unsure, simply enter a <b>fixed amount like 10.</b>",
											'multivendorx'
										)}
									/>
								</FormGroupComponent>
							</>
						)}

						{applyFilters(
							'multivendorx_after_zone_shipping_fields',
							null,
							{
								zone: selectedZone,
								shippingMethod: formData.shippingMethod,
								storeId: id,
							}
						)}
					</FormGroupWrapperComponent>
				</PopupComponent>
			)}
		</>
	);
};

export default DistanceByZoneShipping;
