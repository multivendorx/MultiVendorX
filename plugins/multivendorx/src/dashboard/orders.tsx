/* global appLocalizer */
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useLocation, useNavigate } from 'react-router-dom';
import { __ } from '@wordpress/i18n';


import { ButtonInput, SelectInput, TextInput } from '@zyra/inputs';
import { getApiLink, useModules } from '@zyra/core';
import {
	PopupComponent,
	FormGroupComponent,
	FormGroupWrapperComponent,
	NavigatorHeaderComponent,
} from '@zyra/components';
import { TableCard, TableRow, QueryProps, CategoryCount } from '@zyra/table';
import {
	downloadCSV,
	formatLocalDate,
	toWcIsoDate,
	dashNavigate,
} from '../services/commonFunction';
import { applyFilters } from '@wordpress/hooks';
import './orders.scss';

const fetchOrderById = async (orderId: number) => {
	const res = await axios.get(
		`${appLocalizer.apiUrl}/wc/v3/orders/${orderId}`,
		{
			headers: { 'X-WP-Nonce': appLocalizer.nonce },
		}
	);

	return res.data;
};

const Orders: React.FC = () => {
	const [rows, setRows] = useState<TableRow[][]>([]);
	const [totalRows, setTotalRows] = useState(0);
	const [isLoading, setIsLoading] = useState(false);
	const [rowIds, setRowIds] = useState<number[]>([]);
	const [categoryCounts, setCategoryCounts] = useState<
		CategoryCount[] | null
	>(null);
	const [confirmOpen, setConfirmOpen] = useState(false);
	const [tracking, setTracking] = useState(false);
	const [trackingOrderId, setTrackingOrderId] = useState<number | false>(false);
	const [message, setMessage] = useState('');
	const { modules } = useModules();
	const location = useLocation();
	const navigate = useNavigate();
	const hash = location.hash.replace(/^#/, '') || '';
	const privacy =
		appLocalizer.admin_settings?.privacy?.[
			'customer_information_access'
		];

	const exportAllOrders = () => {
		let allOrders = [];
		let page = 1;
		const perPage = 100;

		const fetchPage = () => {
			return axios
				.get(`${appLocalizer.apiUrl}/wc/v3/orders`, {
					headers: { 'X-WP-Nonce': appLocalizer.nonce },
					params: {
						per_page: perPage,
						page,
						meta_key: 'multivendorx_store_id',
						value: appLocalizer.store_id,
					},
				})
				.then((res) => {
					allOrders = allOrders.concat(res.data);

					const totalPages = parseInt(
						res.headers['x-wp-totalpages'] || '1'
					);

					if (page < totalPages) {
						page++;
						return fetchPage(); // recursively fetch next page
					}
				});
		};
		fetchPage()
			.then(() => {
				if (allOrders.length === 0) {
					setMessage('No orders found to export');
					setConfirmOpen(true);
					return;
				}

				const csvRows: string[] = [];
				csvRows.push('Order ID,Customer,Email,Total,Status,Date');

				allOrders.forEach((order) => {
					const customer = order.billing?.first_name
						? `${order.billing.first_name} ${order.billing.last_name || ''}`
						: 'Guest';

					const email = order.billing?.email || '';
					const total = order.total || '';
					const status = order.status || '';
					const date = order.date_created || '';

					csvRows.push(
						[order.id, customer, email, total, status, date]
							.map(
								(field) =>
									`"${String(field).replace(/"/g, '""')}"`
							)
							.join(',')
					);
				});

				const csvString = csvRows.join('\n');

				const blob = new Blob([csvString], {
					type: 'text/csv;charset=utf-8;',
				});
				const link = document.createElement('a');
				link.href = URL.createObjectURL(blob);
				link.download = `orders_${appLocalizer.store_id}_${new Date().toISOString()}.csv`;
				link.click();
				URL.revokeObjectURL(link.href);
			})
			.catch((err) => {
				console.error('Error exporting orders:', err);
			});
	};

	const fetchOrderStatusCounts = () => {
		const statuses = [
			'all',
			'pending',
			'processing',
			'on-hold',
			'completed',
			'cancelled',
			'refunded',
			'failed',
			'trash',
		];

		if (modules.includes('marketplace-refund')) {
			statuses.push('refund-requested');
		}

		const requests = statuses.map((status) => {
			const params = {
				per_page: 1,
				meta_key: 'multivendorx_store_id',
				value: appLocalizer.store_id,
			};

			if (status !== 'all') {
				params.status = status;
			}

			return axios
				.get(`${appLocalizer.apiUrl}/wc/v3/orders`, {
					headers: { 'X-WP-Nonce': appLocalizer.nonce },
					params,
				})
				.then((res) => {
					const total = parseInt(res.headers['x-wp-total'] || '0');

					return {
						value: status,
						label:
							status === 'all'
								? __('All', 'multivendorx')
								: status.charAt(0).toUpperCase() +
									status.slice(1),
						count: total,
					};
				});
		});

		Promise.all(requests)
			.then((counts) => {
				setCategoryCounts(counts);
			})
			.catch((error) => {
				console.error('Error fetching order status counts:', error);
			});
	};

	// Fetch dynamic order status counts for typeCounts filter
	useEffect(() => {
		fetchOrderStatusCounts();
	}, []);

	// Fetch orders
	useEffect(() => {
		if (hash === 'refund-requested') {
			doRefreshTableData({ categoryFilter: 'refund-requested' });
		} else {
			doRefreshTableData({});
		}
	}, []);

	const bulkActions = [
		{ label: __('Pending Payment', 'multivendorx'), value: 'pending' },
		{ label: __('Processing', 'multivendorx'), value: 'processing' },
		{ label: __('On Hold', 'multivendorx'), value: 'on-hold' },
		{ label: __('Completed', 'multivendorx'), value: 'completed' },
		{ label: __('Cancelled', 'multivendorx'), value: 'cancelled' },
		{ label: __('Refunded', 'multivendorx'), value: 'refunded' },
		{ label: __('Failed', 'multivendorx'), value: 'failed' },
	];

	const privacyHeaders = privacy?.includes('name')
		? {
				customer: {
					label: __('Customer', 'multivendorx'),
					render: (row) =>
						row.billing?.first_name
							? `${row.billing.first_name} ${row.billing.last_name || ''}`
							: 'Guest',
				},
			}
		: {};

	const headers = {
		id: {
			label: __('Order ID', 'multivendorx'),
			render: (row) => (
				<>
					<span
						onClick={() =>
							dashNavigate(navigate, [
								'orders',
								'view',
								String(row.id),
							])
						}
						className="link-item"
					>
						#{row.id}
					</span>
					{applyFilters('multivendorx_order_badge', null, row)}
				</>
			),
		},

		...privacyHeaders,

		date_created: {
			label: __('Date', 'multivendorx'),
			type: 'date',
		},

		status: {
			label: __('Status', 'multivendorx'),
			type: 'status' , statusClass: (row) => `${row.status}`,
		},

		total: {
			label: __('Total', 'multivendorx'),
			type: 'currency',
		},

		action: {
			type: 'action',
			label: 'Action',
			actions: applyFilters('multivendorx_store_order_actions', [
				...(appLocalizer.edit_order_capability
					? [
							{
								label: __('View', 'multivendorx'),
								icon: 'eye',
								onClick: (row) => {
									dashNavigate(navigate, [
										'orders',
										'view',
										String(row.id),
									]);
								},
							},
						]
					: []),

				{
					label: __('Download', 'multivendorx'),
					icon: 'download',
					onClick: (row) => {
						const singleOrderQuery = {
							searchValue: String(row.id),
						};
						downloadCSVByQuery(singleOrderQuery);
					},
				},
				{
					label: __('Shipping', 'multivendorx'),
					icon: 'shipping',
					onClick: (row) => {
						setTracking(true);
						setTrackingOrderId(row.id);
					},
				},
			]),
		},
	};
	const filters = [
		{
			key: 'created_at',
			label: 'Created Date',
			type: 'date',
		},
	];

	const doRefreshTableData = (query: QueryProps) => {
		setIsLoading(true);

		axios
			.get(`${appLocalizer.apiUrl}/wc/v3/orders`, {
				headers: {
					'X-WP-Nonce': appLocalizer.nonce,
				},
				params: buildOrderQueryParams(query),
			})
			.then((response) => {
				const orders = Array.isArray(response.data)
					? response.data
					: [];
				setRowIds(orders.map((o) => o.id));

				setRows(orders);
				setTotalRows(Number(response.headers['x-wp-total']) || 0);
				setIsLoading(false);
			})
			.catch(() => {
				setRows([]);
				setTotalRows(0);
				setIsLoading(false);
			});
	};

	const handleBulkAction = (action: string, selectedIds: number[]) => {
		if (!action || selectedIds.length === 0) {
			return;
		}

		const updatePayload = {
			update: selectedIds.map((id) => ({
				id,
				status: action,
			})),
		};

		axios
			.post(`${appLocalizer.apiUrl}/wc/v3/orders/batch`, updatePayload, {
				headers: {
					'X-WP-Nonce': appLocalizer.nonce,
				},
			})
			.then(() => {
				doRefreshTableData({});
			})
			.catch((err) => {
				console.error('Error performing bulk action:', err);
			});
	};

	const buildOrderQueryParams = (
		query: QueryProps,
		includePagination: boolean = true
	) => {
		const params = {
			search: query.searchValue,
			status: query.categoryFilter === 'all' ? '' : query.categoryFilter,
			orderby: query.orderby || 'date',
			order: query.order || 'desc',
			meta_key: 'multivendorx_store_id',
			value: appLocalizer.store_id,
			after: query.filter?.created_at?.startDate
				? toWcIsoDate(query.filter.created_at.startDate, 'start')
				: undefined,
			before: query.filter?.created_at?.endDate
				? toWcIsoDate(query.filter.created_at.endDate, 'end')
				: undefined,
		};

		if (includePagination) {
			params.page = query.paged || 1;
			params.per_page = query.per_page || 10;
		}
		return params;
	};

	const [formData, setFormData] = useState({
		provider: '',
		tracking_date: '',
		tracking_url: '',
		tracking_id: '',
	});

	const fetchOrder = () => {
		axios
			.get(`${appLocalizer.apiUrl}/wc/v3/orders/${trackingOrderId}`, {
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
			})
			.then((res) => {
				const meta = (key: string) =>
					res.data.meta_data.find((m) => m.key === key)?.value ?? '';
				setFormData({
					provider: meta(
						appLocalizer.order_meta['shipping_provider']
					),
					tracking_date: meta(
						appLocalizer.order_meta['tracking_date']
					),
					tracking_url: meta(appLocalizer.order_meta['tracking_url']),
					tracking_id: meta(appLocalizer.order_meta['tracking_id']),
				});
			});
	};

	useEffect(() => {
		if (!trackingOrderId) {
			return;
		}

		fetchOrder();
	}, [trackingOrderId]);

	const handleChange = (key, value) => {
		setFormData((prev) => ({
			...prev,
			[key]: value,
		}));
	};

	const handleTracking = () => {
		axios({
			method: 'POST',
			url: getApiLink(appLocalizer, `tracking`),
			headers: { 'X-WP-Nonce': appLocalizer.nonce },
			data: {
				...formData,
				order_id: trackingOrderId,
			},
		}).then(() => {
			setTracking(false);
		});
	};

	const providers =
		appLocalizer.admin_settings?.shipping?.shipping_providers || [];

	const formattedProviders = providers.map((provider) => ({
		value: provider,
		label: provider
			.replace(/[-_]/g, ' ')
			.replace(/\b\w/g, (char) => char.toUpperCase()),
	}));

	const downloadCSVByQuery = (query: QueryProps) => {
		axios
			.get(`${appLocalizer.apiUrl}/wc/v3/orders`, {
				headers: {
					'X-WP-Nonce': appLocalizer.nonce,
				},
				params: buildOrderQueryParams(query, false),
			})
			.then((response) => {
				const rows = response.data || [];

				downloadCSV(
					headers,
					rows,
					`order-${formatLocalDate(new Date())}.csv`
				);
			})
			.catch((error) => {
				console.error('CSV download failed:', error);
			});
	};
	const buttonActions = [
		{
			label: __('Download CSV', 'multivendorx'),
			icon: 'download',
			onClickWithQuery: downloadCSVByQuery,
		},
	];

	return (
		<>
			<NavigatorHeaderComponent
				headerTitle={__('Orders', 'multivendorx')}
				headerDescription={__(
					'View, track, and manage all your store orders and earnings in one place.',
					'multivendorx'
				)}
				buttons={[
					// {
					// 	label: __('Export', 'multivendorx'),
					// 	icon: 'export',
					// 	onClick: exportAllOrders,
					// },
					{
						label: __('Add New', 'multivendorx'),
						icon: 'plus',
						onClick: () => {
							dashNavigate(navigate, ['orders', 'add']);
						},
					},
				]}
			/>
			<TableCard
				headers={headers}
				rows={rows}
				totalRows={totalRows}
				isLoading={isLoading}
				onQueryUpdate={doRefreshTableData}
				search={{
					placeholder: __('Search...', 'multivendorx'),
					size: 10,
					options: [
						{ label: __('All', 'multivendorx'), value: 'all' },
						{
							label: __('Order Id', 'multivendorx'),
							value: 'order_id',
						},
						{
							label: __('Products', 'multivendorx'),
							value: 'products',
						},
						{
							label: __('Customer Email', 'multivendorx'),
							value: 'customer_email',
						},
						{
							label: __('Customer', 'multivendorx'),
							value: 'customer',
						},
					],
				}}
				filters={filters}
				buttonActions={buttonActions}
				ids={rowIds}
				categoryCounts={categoryCounts}
				bulkActions={bulkActions}
				onBulkActionApply={(action: string, selectedIds: []) => {
					handleBulkAction(action, selectedIds);
				}}
				format={appLocalizer.date_format}
				currency={{
					currencySymbol: appLocalizer.currency_symbol,
					priceDecimals: appLocalizer.price_decimals,
					decimalSeparator: appLocalizer.decimal_separator,
					thousandSeparator: appLocalizer.thousand_separator,
					currencyPosition: appLocalizer.currency_position,
				}}
			/>

			<PopupComponent
				position="lightbox"
				open={confirmOpen}
				onClose={() => setConfirmOpen(false)}
				width={31.25}
			>
				{message}
			</PopupComponent>

			{tracking && (
				<PopupComponent
					open={tracking}
					onClose={() => setTracking(false)}
					width={31.25}
					height={40}
					header={{
						icon: 'shipping',
						title: __('Tracking', 'multivendorx'),
					}}
					footer={
						<ButtonInput
							buttons={[
								{
									icon: 'close',
									text: __('Cancel', 'multivendorx'),
									color: 'red',
									onClick: () => {
										setTracking(false);
									},
								},
								{
									icon: 'send',
									color: 'purple',
									text: __('Send', 'multivendorx'),
									onClick: handleTracking,
								},
							]}
						/>
					}
				>
					<FormGroupWrapperComponent>
						<FormGroupComponent
							cols={6}
							label={__('Shipping Providers', 'multivendorx')}
							htmlFor="provider"
						>
							<SelectInput
								type="single-select"
								name="provider"
								value={formData.provider || ''}
								options={formattedProviders}
								onChange={(selected) =>
									handleChange('provider', selected)
								}
							/>
						</FormGroupComponent>
						<FormGroupComponent
							cols={6}
							label={__('Date', 'multivendorx')}
							htmlFor="tracking_date"
						>
							<TextInput
								type="date"
								value={formData.tracking_date}
								onChange={(value: any) =>
									handleChange('tracking_date', value)
								}
							/>
						</FormGroupComponent>
						<FormGroupComponent
							cols={6}
							label={__('Tracking URL', 'multivendorx')}
							htmlFor="tracking_url"
						>
							<TextInput
								type="text"
								value={formData.tracking_url}
								onChange={(value: any) =>
									handleChange('tracking_url', value)
								}
							/>
						</FormGroupComponent>
						<FormGroupComponent
							cols={6}
							label={__('Tracking Number', 'multivendorx')}
							htmlFor="title"
						>
							<TextInput
								type="text"
								value={formData.tracking_id}
								onChange={(value: any) =>
									handleChange('tracking_id', value)
								}
							/>
						</FormGroupComponent>
					</FormGroupWrapperComponent>
				</PopupComponent>
			)}
		</>
	);
};

export default Orders;
