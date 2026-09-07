/* global appLocalizer */
import React, { useEffect, useRef, useState } from 'react';
import { getApiLink, useOutsideClick } from '@zyra/core';

import {
	ButtonInput,
	TextInput,
	SelectInput,
	TextAreaInput,
	EmailInput,
} from '@zyra/inputs';
import {
	CardComponent,
	ColumnComponent,
	ContainerComponent,
	FormGroupComponent,
	FormGroupWrapperComponent,
	InformationItemComponent,
	NavigatorHeaderComponent,
} from '@zyra/components';
import { TableRow, TableCard } from '@zyra/table';
import axios from 'axios';
import { formatCurrency, dashNavigate } from '@/services/commonFunction';
import { __ } from '@wordpress/i18n';
import { useNavigate } from 'react-router-dom';
interface AddressData {
	address_1?: string;
	address_2?: string;
	city?: string;
	postcode?: string;
	state?: string;
	country?: string;
	[key: string]: string | undefined;
}

// Strips empty-string values before sending an address to WooCommerce's REST
// API - a field that's simply absent from the request is accepted, but an
// empty string fails schema validation for fields like `email` (see
// createOrder()'s use of this on billingAddress/shippingAddress).
const omitEmptyValues = (address: AddressData): AddressData =>
	Object.fromEntries(Object.entries(address).filter(([, value]) => value !== '' && value !== undefined));

const AddOrder = () => {
	const [rowIds, setRowIds] = useState<number[]>([]);
	const [showAddProduct, setShowAddProduct] = useState(false);
	const [allProducts, setAllProducts] = useState([]);
	const [customers, setCustomers] = useState([]);
	const [selectedCustomer, setSelectedCustomer] = useState(null);
	const [shippingAddress, setShippingAddress] = useState({});
	const [billingAddress, setBillingAddress] = useState({});
	const [paymentMethods, setPaymentMethods] = useState([]);
	const [selectedPayment, setSelectedPayment] = useState(null);
	const [addedProducts, setAddedProducts] = useState([]);
	const [showAddressEdit, setShowAddressEdit] = useState(false);
	const [showShippingAddressEdit, setShowShippingAddressEdit] =
		useState(false);
	const [showCreateCustomer, setShowCreateCustomer] = useState(false);
	const [orderNote, setOrderNote] = useState('');
	const addressEditRef = useRef(null);
	const shippingAddressEditRef = useRef(null);
	const [shippingLines, setShippingLines] = useState([]);
	const [availableShippingMethods, setAvailableShippingMethods] = useState(
		[]
	);
	const navigate = useNavigate();

	useOutsideClick(addressEditRef, () => {
		if (!showAddressEdit || !selectedCustomer) return;

		const payload = {
			billing: {
				first_name: selectedCustomer?.first_name,
				last_name: selectedCustomer?.last_name,
				address_1: billingAddress.address_1,
				address_2: billingAddress.address_2 || '',
				city: billingAddress.city,
				state: billingAddress.state,
				postcode: billingAddress.postcode,
				country: billingAddress.country,
			},
		};
		axios
			.post(
				`${appLocalizer.apiUrl}/wc/v3/customers/${selectedCustomer?.id}`,
				payload,
				{
					headers: { 'X-WP-Nonce': appLocalizer.nonce },
				}
			)
			.then((res) => {
				setBillingAddress(res.data.billing);
			});

		setShowAddressEdit(false);
	});

	useOutsideClick(shippingAddressEditRef, () => {
		if (!showShippingAddressEdit || !selectedCustomer) return;

		const payload = {
			shipping: {
				first_name: selectedCustomer?.first_name,
				last_name: selectedCustomer?.last_name,
				address_1: shippingAddress.address_1,
				address_2: shippingAddress.address_2 || '',
				city: shippingAddress.city,
				state: shippingAddress.state,
				postcode: shippingAddress.postcode,
				country: shippingAddress.country,
			},
		};
		axios
			.post(
				`${appLocalizer.apiUrl}/wc/v3/customers/${selectedCustomer?.id}`,
				payload,
				{
					headers: { 'X-WP-Nonce': appLocalizer.nonce },
				}
			)
			.then((res) => {
				setShippingAddress(res.data.shipping);
			});

		setShowShippingAddressEdit(false);
	});

	useEffect(() => {
		axios
			.get(`${appLocalizer.apiUrl}/wc/v3/customers`, {
				headers: {
					'X-WP-Nonce': appLocalizer.nonce,
				},
				params: {
					per_page: 100,
				},
			})
			.then((response) => {
				setCustomers(response.data);
			});
	}, []);

	const customerOptions = [
		{ label: __('Choose customer...', 'multivendorx'), value: '' },
		...(customers
			? customers.map((c) => ({
				label: `${c.first_name} ${c.last_name}`.trim() || c.email,
				value: c.id,
			}))
			: []),
	];

	useEffect(() => {
		if (!showAddProduct) {
			return;
		}

		axios
			.get(`${appLocalizer.apiUrl}/wc/v3/products`, {
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
				params: {
					per_page: 100,
				},
			})
			.then((res) => {
				const products = res.data;
				const onboardingSettings =
					appLocalizer.admin_settings?.['onboarding'];
				// Pro Franchise module: when 'Products available for
				// franchise orders' is set to allow admin products too (see
				// Onboarding.ts), a store can also add products from the
				// admin catalog to a manually-created order, not just its
				// own - otherwise only the store's own products are
				// selectable, same as today.
				const includeAdminProducts =
					onboardingSettings?.store_selling_mode === 'franchise' &&
					onboardingSettings?.products_available_for_franchise_orders ===
					'store_and_admin_products';

				const filtered = products.filter((p) => {
					const storeId = p.meta_data?.find(
						(m) => m.key === 'multivendorx_store_id'
					)?.value;

					if (storeId === appLocalizer.store_id) {
						return true;
					}

					// An admin product has no store owner at all - distinct
					// from a product owned by a different store.
					return includeAdminProducts && !storeId;
				});

				setAllProducts(filtered);
			});
	}, [showAddProduct]);

	const subtotal = addedProducts.reduce((sum, item) => {
		return sum + item.price * (item.qty || 1);
	}, 0);

	const hasCustomer = !!selectedCustomer;

	useEffect(() => {
		axios
			.get(`${appLocalizer.apiUrl}/wc/v3/payment_gateways`, {
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
			})
			.then((res) => {
				const excludedGateways = [
					'wc-bookings-gateway',
					'wcappointmentsgateway',
				];

				const formatted = res.data
					.filter(
						(m) =>
							m.enabled &&
							!excludedGateways.includes(m.id)
					)
					.map((m) => ({
						label: m.title,
						value: m.id,
						method_title: m.title,
					}));

				setPaymentMethods(formatted);
			});
	}, []);

	useEffect(() => {
		axios
			.get(`${appLocalizer.apiUrl}/wc/v3/shipping_methods`, {
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
			})
			.then((res) => {
				const formatted = res.data.map((method) => ({
					label: method.title,
					value: method.id,
					...method,
				}));
				setAvailableShippingMethods(formatted);
			});
	}, []);

	const totalShipping = shippingLines.reduce(
		(sum, s) => sum + Number(s.cost || 0),
		0
	);

	const paymentOptions = [
		{ label: __('Select Payment Method', 'multivendorx'), value: '' },
		...paymentMethods,
	];

	const [taxRates, setTaxRates] = useState<TableRow[][]>([]);

	useEffect(() => {
		axios
			.get(`${appLocalizer.apiUrl}/wc/v3/taxes`, {
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
				params: { per_page: 100 },
			})
			.then((res) => {
				const taxes = Array.isArray(res.data) ? res.data : [];
				const ids = taxes.map((tax) => {
					return tax.id;
				});

				setRowIds(ids);

				setTaxRates(taxes);
			});
	}, []);

	const [showAddTax, setShowAddTax] = useState(false);
	const [selectedTaxRate, setSelectedTaxRate] = useState(null);

	const applyTaxToOrder = () => {
		if (!selectedTaxRate) {
			return;
		}

		const rate = Number(selectedTaxRate.rate) / 100;

		setAddedProducts((prev) =>
			prev.map((item) => ({
				...item,
				tax_rate_id: selectedTaxRate.id,
				tax_amount: item.price * (item.qty || 1) * rate,
			}))
		);
	};

	const createOrder = async () => {
		const orderData = {
			customer_id: selectedCustomer?.id || 0,
			// billingAddress/shippingAddress can carry over fields (e.g.
			// `email`) copied wholesale from an existing customer's WC
			// profile (see setBillingAddress(customer.billing) above) that
			// this screen has no field to edit - an empty string there
			// fails WooCommerce's REST email validation, whereas a field
			// that's simply absent is accepted, so empty values are
			// stripped rather than sent as-is.
			billing: omitEmptyValues(billingAddress),
			shipping: omitEmptyValues(shippingAddress),
			line_items: addedProducts.map((item) => {
				const qty = item.qty || 1;
				const subtotal = item.price * qty;
				const tax = item.tax_amount || 0;

				return {
					product_id: item.id,
					quantity: qty,
					subtotal: subtotal.toFixed(2),
					total: subtotal.toFixed(2),
					// Tax
					subtotal_tax: tax.toFixed(2),
					total_tax: tax.toFixed(2),
					// Required for tax mapping
					taxes: item.tax_rate_id
						? [{ id: item.tax_rate_id, total: tax.toFixed(2) }]
						: [],
				};
			}),
			// Shipping
			shipping_lines: shippingLines.map((s) => ({
				method_id: s.method_id,
				method_title: s.name,
				total: Number(s.cost).toFixed(2),
			})),
			// Payment
			payment_method: selectedPayment?.value || '',
			payment_method_title: selectedPayment?.method_title || '',
			set_paid: false,
			customer_note: orderNote || '',
			meta_data: [
				// {
				// 	key: 'multivendorx_store_id',
				// 	value: appLocalizer.store_id,
				// },
			],
		};

		axios
			.post(`${appLocalizer.apiUrl}/wc/v3/orders`, orderData, {
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
			})
			.then(() => {
				dashNavigate(navigate, ['orders']);
			});
	};

	const orderSubtotal = addedProducts.reduce(
		(sum, item) => sum + item.price * (item.qty || 1),
		0
	);

	const orderTaxTotal = addedProducts.reduce(
		(sum, item) => sum + (item.tax_amount || 0),
		0
	);

	const orderShippingTotal = totalShipping;

	const grandTotal = orderSubtotal + orderTaxTotal + orderShippingTotal;

	const [newCustomer, setNewCustomer] = useState({
		first_name: '',
		last_name: '',
		email: '',
		phone: '',
	});

	const createCustomer = () => {
		const payload = {
			email: newCustomer.email,
			first_name: newCustomer.first_name,
			last_name: newCustomer.last_name,
			billing: {
				first_name: newCustomer.first_name,
				last_name: newCustomer.last_name,
				email: newCustomer.email,
				phone: newCustomer.phone,
			},
			shipping: {
				first_name: newCustomer.first_name,
				last_name: newCustomer.last_name,
			},
		};

		axios
			.post(`${appLocalizer.apiUrl}/wc/v3/customers`, payload, {
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
			})
			.then((res) => {
				const customer = res.data;
				// Add to dropdown immediately
				setCustomers((prev) => [...prev, customer]);
				// Select this customer automatically
				setSelectedCustomer(customer);
				setBillingAddress(customer.billing);
				setShippingAddress(customer.shipping);
				// Close create form
				setShowCreateCustomer(false);
				// Clear form
				setNewCustomer({
					first_name: '',
					last_name: '',
					email: '',
					phone: '',
				});
			});
	};
	const [stateOptions, setStateOptions] = useState<
		{ label: string; value: string }[]
	>([]);

	const fetchStatesByCountry = (countryCode: string) => {
		axios({
			method: 'GET',
			url: getApiLink(appLocalizer, `states/${countryCode}`),
			headers: { 'X-WP-Nonce': appLocalizer.nonce },
		}).then((res) => {
			setStateOptions(res.data || []);
		});
	};

	useEffect(() => {
		if (hasCustomer && billingAddress?.address_1 == '') {
			setShowAddressEdit(true);
		}
	}, [hasCustomer, billingAddress]);

	useEffect(() => {
		if (hasCustomer && shippingAddress?.address_1 == '') {
			setShowShippingAddressEdit(true);
		}
	}, [hasCustomer, shippingAddress]);

	// Define headers for the order items table
	const tableRows = [
		...addedProducts.map((product) => ({
			...product,
			rowType: 'product',
			id: `product-${product.id}`,
		})),
		...shippingLines.map((ship) => ({
			...ship,
			rowType: 'shipping',
			id: `shipping-${ship.id}`,
		})),
	];

	// Single table headers that handle both products and shipping
	const tableHeaders = {
		item: {
			label: __('Item', 'multivendorx'),
			render: (row) => {
				if (row.rowType === 'product') {
					return (
						<div className="item-details">
							<div className="image">
								<img
									src={row?.images?.[0]?.src}
									width={40}
									alt={row.name}
								/>
							</div>
							<div className="detail">
								<div className="name">{row.name}</div>
								{row?.sku && (
									<div className="sku">
										{__('SKU:', 'multivendorx')} {row.sku}
									</div>
								)}
							</div>
						</div>
					);
				} else {
					return (
						<div className="item-details">
							<div className="icon">
								<i className="adminfont-cart green"></i>
							</div>
							<div className="detail">
								<div className="name">
									{__('Shipping', 'multivendorx')}
								</div>
								<SelectInput
									name="shipping_method"
									type="single-select"
									options={availableShippingMethods}
									value={availableShippingMethods.find(
										(o) => o.value === row.method_id
									)}
									onChange={(value) => {
										const selectedOption =
											availableShippingMethods.find(
												(o) => o.value === value
											);
										const method_title =
											selectedOption?.label || '';
										setShippingLines((prev) =>
											prev.map((s) =>
												s.id === row.id
													? {
														...s,
														method_id: value,
														name: method_title,
													}
													: s
											)
										);
									}}
								/>
							</div>
						</div>
					);
				}
			},
		},
		price: {
			label: __('Price', 'multivendorx'),
			render: (row) => {
				if (row.rowType === 'product') {
					return formatCurrency(row.price);
				}
				return '';
			},
		},
		qty: {
			label: __('Qty', 'multivendorx'),
			render: (row) => {
				if (row.rowType === 'product') {
					return (
						<TextInput
							type="number"
							min="1"
							value={row.qty || 1}
							onChange={(value) => {
								const qty = +value;
								setAddedProducts((prev) =>
									prev.map((p) =>
										p.id === row.id ? { ...p, qty } : p
									)
								);
							}}
						/>
					);
				}
				return '';
			},
		},
		total: {
			label: __('Total', 'multivendorx'),
			render: (row) => {
				if (row.rowType === 'product') {
					return formatCurrency(row.price * (row.qty || 1));
				} else {
					return (
						<TextInput
							type="number"
							min="0"
							value={row.cost}
							onChange={(value) => {
								const cost = parseFloat(value) || 0;
								setShippingLines((prev) =>
									prev.map((s) =>
										s.id === row.id ? { ...s, cost } : s
									)
								);
							}}
						/>
					);
				}
			},
		},
	};

	// Tax headers
	const taxTableHeaders = {
		name: {
			label: __('Rate name', 'multivendorx'),
		},
		class: {
			label: __('Tax class', 'multivendorx'),
		},
		code: {
			label: __('Rate code', 'multivendorx'),
		},
		rate: {
			label: __('Rate %', 'multivendorx'),
		},
		action: {
			type: 'action',
			label: __('Action', 'multivendorx'),
			actions: [
				{
					label: __('Select', 'multivendorx'),
					icon: 'check',
					onClick: (row) => {
						if (row) {
							setSelectedTaxRate(row);
						}
					},
				},
			],
		},
	};

	// billing & shipping common card
	const renderAddressCard = (
		title: string,
		address: AddressData,
		isEditMode: boolean,
		setIsEditMode: (value: boolean) => void,
		editRef: React.RefObject<HTMLDivElement | null>,
		type: 'billing' | 'shipping'
	) => {
		const hasCustomer = !!selectedCustomer;

		return (
			<CardComponent
				title={__(title, 'multivendorx')}
				iconName={hasCustomer && !isEditMode ? 'edit' : ''}
				onIconClick={() => setIsEditMode(true)}
			>
				{!hasCustomer && (
					<span>
						{type === 'billing'
							? __('No billing address found', 'multivendorx')
							: __('Please Select a customer', 'multivendorx')}
					</span>
				)}

				{hasCustomer && !isEditMode && (
					<FormGroupWrapperComponent>
						<FormGroupComponent row label={__('Address', 'multivendorx')}>
							{address.address_1}
						</FormGroupComponent>
						<FormGroupComponent row label={__('City', 'multivendorx')}>
							{address.city}
						</FormGroupComponent>
						<FormGroupComponent
							row
							label={__('Postcode / ZIP', 'multivendorx')}
						>
							{address.postcode}
						</FormGroupComponent>
						<FormGroupComponent row label={__('State', 'multivendorx')}>
							{address.state}
						</FormGroupComponent>
						<FormGroupComponent row label={__('Country', 'multivendorx')}>
							{address.country}
						</FormGroupComponent>
					</FormGroupWrapperComponent>
				)}

				{isEditMode && (
					<div ref={editRef}>
						<FormGroupWrapperComponent>
							<FormGroupComponent
								label={__('Address', 'multivendorx')}
								htmlFor={`${type}-address`}
							>
								<TextInput
									name={`${type}_address_1`}
									value={address.address_1 || ''}
									onChange={(value: string) => {
										if (type === 'billing') {
											setBillingAddress(
												(prev: AddressData) => ({
													...prev,
													address_1: value,
												})
											);
										} else {
											setShippingAddress(
												(prev: AddressData) => ({
													...prev,
													address_1: value,
												})
											);
										}
									}}
								/>
							</FormGroupComponent>

							<FormGroupComponent
								cols={6}
								label={__('City', 'multivendorx')}
								htmlFor={`${type}-city`}
							>
								<TextInput
									name={`${type}_city`}
									value={address.city || ''}
									onChange={(value: string) => {
										if (type === 'billing') {
											setBillingAddress(
												(prev: AddressData) => ({
													...prev,
													city: value,
												})
											);
										} else {
											setShippingAddress(
												(prev: AddressData) => ({
													...prev,
													city: value,
												})
											);
										}
									}}
								/>
							</FormGroupComponent>

							<FormGroupComponent
								cols={6}
								label={__('Postcode / ZIP', 'multivendorx')}
								htmlFor={`${type}-postcode`}
							>
								<TextInput
									name={`${type}_postcode`}
									value={address.postcode || ''}
									onChange={(value: string) => {
										if (type === 'billing') {
											setBillingAddress(
												(prev: AddressData) => ({
													...prev,
													postcode: value,
												})
											);
										} else {
											setShippingAddress(
												(prev: AddressData) => ({
													...prev,
													postcode: value,
												})
											);
										}
									}}
								/>
							</FormGroupComponent>

							<FormGroupComponent
								cols={6}
								label={__('Country / Region', 'multivendorx')}
								htmlFor={`${type}-country`}
							>
								<SelectInput
									name={`${type}_country`}
									type="single-select"
									value={address.country}
									options={appLocalizer.country_list || []}
									onChange={(selected: string) => {
										if (type === 'billing') {
											setBillingAddress(
												(prev: AddressData) => ({
													...prev,
													country: selected,
												})
											);
										} else {
											setShippingAddress(
												(prev: AddressData) => ({
													...prev,
													country: selected,
												})
											);
										}
										fetchStatesByCountry(selected);
									}}
								/>
							</FormGroupComponent>

							<FormGroupComponent
								cols={6}
								label={__('State / County', 'multivendorx')}
								htmlFor={`${type}-state`}
							>
								<SelectInput
									name={`${type}_state`}
									type="single-select"
									value={address.state}
									options={stateOptions}
									onChange={(selected: string) => {
										if (type === 'billing') {
											setBillingAddress(
												(prev: AddressData) => ({
													...prev,
													state: selected,
												})
											);
										} else {
											setShippingAddress(
												(prev: AddressData) => ({
													...prev,
													state: selected,
												})
											);
										}
									}}
								/>
							</FormGroupComponent>
						</FormGroupWrapperComponent>
					</div>
				)}
			</CardComponent>
		);
	};

	return (
		<>
			<NavigatorHeaderComponent
				headerTitle={__('Add Order', 'multivendorx')}
				headerDescription={__(
					'Create a new order manually by adding products, charges, and customer details.',
					'multivendorx'
				)}
				buttons={[
					{
						label: __('Create Order', 'multivendorx'),
						icon: 'plus',
						onClick: () => createOrder(),
					},
				]}
			/>
			<ContainerComponent>
				<ColumnComponent grid={8}>
					<CardComponent>
						{(addedProducts.length > 0 ||
							shippingLines.length > 0) && (
								<>
									<TableCard
										headers={tableHeaders}
										rows={tableRows}
										showMenu={false}
									/>

									<div className="total-summary">
										<div className="row">
											<span>
												{__('Subtotal:', 'multivendorx')}
											</span>
											<span>{appLocalizer.currency_symbol}{subtotal.toFixed(2)}</span>
										</div>

										<div className="row">
											<span>
												{__('Tax:', 'multivendorx')}
											</span>
											<span>
												{appLocalizer.currency_symbol}
												{addedProducts
													.reduce(
														(sum, p) =>
															sum +
															(p.tax_amount || 0),
														0
													)
													.toFixed(2)}
											</span>
										</div>

										<div className="row">
											<span>
												{__('Shipping:', 'multivendorx')}
											</span>
											<span>
												{formatCurrency(totalShipping)}
											</span>
										</div>

										<div className="row total">
											<strong>
												{__('Grand Total:', 'multivendorx')}
											</strong>
											<strong>
												{appLocalizer.currency_symbol}{grandTotal.toFixed(2)}
											</strong>
										</div>
									</div>
								</>
							)}
						<FormGroupWrapperComponent>
							<ButtonInput
								position="left"
								buttons={[
									{
										icon: 'plus',
										text: 'Add Product',
										onClick: () => setShowAddProduct(true),
									},
									{
										icon: 'plus',
										text: 'Add Shipping',
										onClick: () =>
											setShippingLines((prev) => [
												...prev,
												{
													id: Date.now(),
													name: 'Shipping',
													cost: 0,
													method_id: '',
												},
											]),
									},
									{
										icon: 'plus',
										text: 'Add Tax',
										onClick: () => setShowAddTax(true),
									},
								]}
							/>

							{showAddProduct && (
								<FormGroupComponent
									row
									label={__('Select Product', 'multivendorx')}
								>
									<SelectInput
										name="product_select"
										type="single-select"
										options={[
											{
												label: __(
													'Select a product',
													'multivendorx'
												),
												value: '',
											},
											...allProducts.map((p) => ({
												label: p.name,
												value: p.id,
											})),
										]}
										onChange={(selected) => {
											if (!selected) {
												return;
											}

											const prod = allProducts.find(
												(p) => p.id == selected
											);
											if (prod) {
												setAddedProducts((prev) => [
													...prev,
													{ ...prod, qty: 1 },
												]);
											}

											setShowAddProduct(false);
										}}
									/>
								</FormGroupComponent>
							)}
						</FormGroupWrapperComponent>

						{showAddTax && (
							<div className="tax-wrapper">
								<div className="title">
									{__('Add tax', 'multivendorx')}
								</div>

								{taxRates.length > 0 ? (
									<>
										<TableCard
											headers={taxTableHeaders}
											rows={taxRates}
											ids={rowIds}
											showMenu={false}
										/>

										<ButtonInput
											buttons={[
												{
													text: __(
														'Apply Tax',
														'multivendorx'
													),
													icon: 'plus',
													onClick: () => {
														applyTaxToOrder();
														setShowAddTax(false);
													},
												},
											]}
										/>
									</>
								) : (
									<div className="desc">
										{__(
											'No tax rates set. Contact admin.',
											'multivendorx'
										)}
									</div>
								)}
							</div>
						)}
					</CardComponent>
				</ColumnComponent>
				<ColumnComponent grid={4}>
					<CardComponent title={__('Payment Method', 'multivendorx')}>
						<FormGroupWrapperComponent>
							<FormGroupComponent
								row
								label={__('Payment Method', 'multivendorx')}
								htmlFor="payment-method"
							>
								<SelectInput
									name="payment_method"
									type="single-select"
									options={paymentOptions}
									value={selectedPayment?.value}
									onChange={(value) => {
										const method = paymentMethods.find(
											(m) => m.value === value
										);
										setSelectedPayment(method || null);
									}}
								/>
							</FormGroupComponent>
						</FormGroupWrapperComponent>
					</CardComponent>

					<CardComponent title={__('Customer details', 'multivendorx')}>
						{!selectedCustomer && (
							<>
								<FormGroupWrapperComponent>
									<FormGroupComponent
										row
										label={__(
											'Select Customer',
											'multivendorx'
										)}
										htmlFor="Select-customer"
									>
										<SelectInput
											name="new_owner"
											type="single-select"
											options={customerOptions}
											onChange={(value) => {
												const customer = customers.find(
													(c) => c.id == value
												);
												setSelectedCustomer(customer);
												if (customer) {
													setShippingAddress(
														customer.shipping
													);
													setBillingAddress(
														customer.billing
													);
													setShowCreateCustomer(
														false
													);
												}
											}}
										/>
									</FormGroupComponent>
								</FormGroupWrapperComponent>

								<ButtonInput
									buttons={{
										icon: 'plus',
										text: __(
											'Add New Customer',
											'multivendorx'
										),
										onClick: () =>
											setShowCreateCustomer(
												!showCreateCustomer
											),
									}}
								/>
							</>
						)}
						{selectedCustomer && (
							<InformationItemComponent
								title={
									[selectedCustomer.first_name, selectedCustomer.last_name]
										.filter(Boolean)
										.join(' ') || __('Guest Customer', 'multivendorx')
								}
								avatar={{
									text: selectedCustomer.first_name?.[0] || 'C',
									iconClass: 'person',
								}}
								descriptions={
									[
										{
											label: __(
												'Customer ID',
												'multivendorx'
											),
											value: `#${selectedCustomer.id}`,
											boldLabel: true,
										},
										{
											value: (
												<>
													<i className="adminfont-mail" />{' '}
													{
														selectedCustomer.email
													}
												</>
											),
										},
										{
											value: (
												<>
													<i className="adminfont-phone" />{' '}
													{
														selectedCustomer
															.billing
															?.phone
													}
												</>
											),
										},
									]
								}
								badges={[
									{
										text: __('Edit', 'multivendorx'),
										className: 'blue',
										onClick: () =>
											setSelectedCustomer(null),
									},
								]}
							/>
						)}
					</CardComponent>

					{showCreateCustomer && !selectedCustomer && (
						<CardComponent title={__('Create customer', 'multivendorx')}>
							<FormGroupWrapperComponent>
								<FormGroupComponent
									cols={6}
									label={__('First name', 'multivendorx')}
									htmlFor="Select-customer"
								>
									<TextInput
										name="first_name"
										value={newCustomer.first_name}
										onChange={(value) =>
											setNewCustomer({
												...newCustomer,
												first_name: value,
											})
										}
									/>
								</FormGroupComponent>

								<FormGroupComponent
									cols={6}
									label={__('Last name', 'multivendorx')}
									htmlFor="last-name"
								>
									<TextInput
										name="last_name"
										value={newCustomer.last_name}
										onChange={(value) =>
											setNewCustomer({
												...newCustomer,
												last_name: value,
											})
										}
									/>
								</FormGroupComponent>

								<FormGroupComponent
									label={__('Email', 'multivendorx')}
									htmlFor="email"
								>
									<EmailInput
										mode="single"
										value={
											newCustomer.email
												? [newCustomer.email]
												: []
										}
										placeholder={__(
											'Enter email...',
											'multivendorx'
										)}
										onChange={(emails) => {
											setNewCustomer({
												...newCustomer,
												email: emails[0] || '',
											});
										}}
									/>
								</FormGroupComponent>

								<FormGroupComponent
									label={__('Phone number', 'multivendorx')}
									htmlFor="phone-number"
								>
									<TextInput
										type="number"
										name="phone"
										value={newCustomer.phone}
										onChange={(value) =>
											setNewCustomer({
												...newCustomer,
												phone: value,
											})
										}
									/>
								</FormGroupComponent>
							</FormGroupWrapperComponent>

							<ButtonInput
								buttons={{
									icon: 'plus',
									text: __('Create', 'multivendorx'),
									onClick: () => createCustomer(),
								}}
							/>
						</CardComponent>
					)}

					{renderAddressCard(
						'Shipping address',
						shippingAddress,
						showShippingAddressEdit,
						setShowShippingAddressEdit,
						shippingAddressEditRef,
						'shipping'
					)}

					{renderAddressCard(
						'Billing address',
						billingAddress,
						showAddressEdit,
						setShowAddressEdit,
						addressEditRef,
						'billing'
					)}

					<CardComponent title={__('Order note', 'multivendorx')}>
						<FormGroupComponent>
							<TextAreaInput
								name="order_note"
								value={orderNote}
								placeholder={__(
									'Enter order note...',
									'multivendorx'
								)}
								onChange={(value) => setOrderNote(value)}
							/>
						</FormGroupComponent>
					</CardComponent>
				</ColumnComponent>
			</ContainerComponent>
		</>
	);
};

export default AddOrder;
