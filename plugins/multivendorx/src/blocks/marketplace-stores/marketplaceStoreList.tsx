/* global storesList */
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { getApiLink } from '@zyra/core';
import { MapComponent } from '@zyra/components';
import { __, sprintf } from '@wordpress/i18n';

interface StoreRow {
	id: number;
	name: string;
	store_slug: string;
	store_name?: string;
	location_lat?: string;
	location_lng?: string;
	topProducts?: string[];
}

type Category = {
	id: number;
	name: string;
};

interface StoresListProps {
	order?: string;
	perPage?: number;
	showMap?: boolean;
	hideEmpty?: boolean;
	excludeIds?: string;
}

interface Product {
	id: number;
	name: string;
	permalink?: string;
	images?: Array<{
		src: string;
	}>;
	categories?: Array<{
		id: number;
		name: string;
		slug: string;
	}>;
	average_rating?: number;
	price_html?: string;
}

const MarketplaceStoreList: React.FC<StoresListProps> = ({
	order = '',
	perPage = 5,
	showMap = true,
	hideEmpty = false,
	excludeIds = "",
}) => {
	const [data, setData] = useState<StoreRow[] | []>([]);
	const [categoryList, setCategoryList] = useState<Category[]>([]);
	const [product, setProduct] = useState<[]>([]);
	const [page, setPage] = useState(1);
	const [total, setTotal] = useState(0);
	const [apiKey, setApiKey] = useState('');
	const [viewMode, setViewMode] = useState<'list' | 'split' | 'map'>('list');
	const isWidget = !!storesList?.storeDetails?.storeId;

	const [storeTopProducts, setStoreTopProducts] = useState<
		Record<number, Product[]>
	>({});
	const [addressData, setAddressData] = useState({
		location_lat: '',
		location_lng: '',
		address: '',
		city: '',
		state: '',
		country: '',
		zip: '',
		timezone: '',
	});
	const [filters, setFilters] = useState({
		address: '',
		distance: '',
		miles: '',
		order: order,
		product: '',
		location_lat: '',
		location_lng: '',
	});
	const [mapConfig, setMapConfig] = useState<{
		provider: string | null;
		apiKey: string;
	}>({ provider: 'null', apiKey: '' });

	const settings = storesList.admin_settings;
	const [topFilters, setTopFilters] = useState({
		sort: '',
		category: '',
	});

	useEffect(() => {
		if (!settings?.geolocation) {
			return;
		}

		const provider = settings.geolocation.choose_map_api;
		setMapConfig({
			provider: provider || null,
			apiKey: settings.geolocation[`${provider}_api_key`],
		});
	}, [settings]);

	const totalPages = Math.ceil(total / perPage);

	useEffect(() => {
		axios
			.get(`${storesList.apiUrl}/wc/v3/products/categories`, {
				headers: { 'X-WP-Nonce': storesList.nonce },
			})
			.then((response) => {
				setCategoryList(response.data);
			});
	}, []);

	useEffect(() => {
		const params = {
			per_page: 50,
			status: 'publish',
			meta_key: 'multivendorx_store_id',
		};

		if (topFilters.category) {
			params.category = topFilters.category;
		}

		axios
			.get(`${storesList.apiUrl}/wc/v3/products`, {
				headers: { 'X-WP-Nonce': storesList.nonce },
				params,
			})
			.then((response) => {
				setProduct(response.data);
			});
	}, [topFilters.category]);

	const fetchTopProducts = (storeId: number) => {
		axios
			.get(`${storesList.apiUrl}/wc/v3/products`, {
				headers: { 'X-WP-Nonce': storesList.nonce },
				params: {
					per_page: 3,
					meta_key: 'multivendorx_store_id',
					value: storeId,
					status: 'publish',
					orderby: 'date',
					order: 'desc',
				},
			})
			.then((response) => {
				// Store the products in state keyed by storeId
				setStoreTopProducts((prev) => ({
					...prev,
					[storeId]: response.data,
				}));
			});
	};

	useEffect(() => {
		if (data.length) {
			data.forEach((store) => {
				fetchTopProducts(store.id);
			});
		}
	}, [data]);
	useEffect(() => {
		axios({
			method: 'GET',
			url: getApiLink(storesList, 'stores'),
			headers: { 'X-WP-Nonce': storesList.nonce },
			params: {
				page: page,
				row: perPage,
				filters: true,
				...filters,
				filter_status: 'active',
				exclude_ids: Array.isArray(excludeIds)
					? excludeIds
					: String(excludeIds || '')
						.split(',')
						.map((id) => parseInt(id.trim(), 10))
						.filter((id) => !isNaN(id)),
			},
		})
			.then((response) => {
				setData(response.data || []);
				setTotal(Number(response?.headers?.['x-wp-total']) || 0);
			})
			.catch((error) =>
				console.error('Error fetching filtered stores:', error)
			);
	}, [filters, page, perPage]);

	const handleInputChange = (
		e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>
	) => {
		const { name, value } = e.target;
		setTopFilters((prev) => ({ ...prev, [name]: value }));
	};

	const handleLocationUpdate = (locationData) => {
		if (!locationData) {
			return;
		}

		const updatedAddress = {
			location_lat: locationData.location_lat || '',
			location_lng: locationData.location_lng || '',
			address: locationData.address || '',
			city: locationData.city || '',
			state: locationData.state || '',
			country: locationData.country || '',
			zip: locationData.zip || '',
		};

		// Update map UI
		setAddressData((prev) => ({
			...prev,
			...updatedAddress,
		}));

		// Update filters so API call works
		setFilters((prev) => ({
			...prev,
			location_lat: updatedAddress.location_lat,
			location_lng: updatedAddress.location_lng,
		}));
	};
	const requestUserLocation = () => {
		if (!navigator.geolocation) {
			return;
		}

		navigator.geolocation.getCurrentPosition((position) => {
			const lat = position.coords.latitude.toString();
			const lng = position.coords.longitude.toString();

			setFilters((prev) => ({
				...prev,
				location_lat: lat,
				location_lng: lng,
			}));

			setAddressData((prev) => ({
				...prev,
				location_lat: lat,
				location_lng: lng,
				address: 'My Current Location',
			}));
		});
	};

	useEffect(() => {
		if (!addressData.location_lat && !addressData.location_lng) {
			requestUserLocation();
		}
	}, []);

	const visibleStores = hideEmpty
		? data.filter(
			(store) =>
				storeTopProducts[store.id] && storeTopProducts[store.id].length > 0
		)
		: data;

	const renderMapComponent = () => {
		if (!mapConfig.apiKey || !mapConfig.provider) {
			return null;
		}

		const mapStores = {
			data: data.map((store) => ({
				id: store.id,
				store_name: store.store_name || store.name,
				location_lat: store.location_lat,
				location_lng: store.location_lng,
			})),
		};

		return (
			<MapComponent
				apiKey={mapConfig.apiKey}
				mapId={settings?.geolocation?.google_map_id || ''}
				locationAddress={addressData.address}
				locationLat={addressData.location_lat}
				locationLng={addressData.location_lng}
				isUserLocation={false}
				onLocationUpdate={handleLocationUpdate}
				placeholderSearch={__(
					'Search for a location...',
					'multivendorx'
				)}
				stores={mapStores}
				mapProvider={mapConfig.provider}
			/>
		);
	};
	return (
		<>
			{isWidget && (
				<>
					<div className="woocommerce multivendorx-store">
						<div className="store-list-wrapper">
							<div className="store-list">
								{data &&
									visibleStores.map((store) => (
										<div key={store.id} className="store">
											<div className="store-body">
												<div className="store-header">
													{store.store_image ? (
														<div className="store-image">
															<img
																src={store.store_image}
																alt={store.store_name}
															/>
														</div>
													) : (
														<div className="avatar">
															{store.store_name
																?.charAt(0)
																.toUpperCase()}
														</div>
													)}

													<div className="store-details">
														<a href={`${storesList.store_page_url}${store.store_slug || ''}`}> <h4>{store.store_name}</h4> </a>

														<div className="review-rating">
															{store.rating !==
																undefined && (
																	<div
																		className="star-rating"
																		role="img"
																		aria-label={sprintf(
																			__(
																				'Rated %s out of 5',
																				'multivendorx'
																			),
																			store.rating.toFixed(
																				2
																			)
																		)}
																	>
																		<span
																			style={{
																				width: `${(store.rating / 5) * 100}%`,
																			}}
																		>
																			{__(
																				'Rated',
																				'multivendorx'
																			)}{' '}
																			<strong className="rating">
																				{store.rating.toFixed(
																					2
																				)}
																			</strong>{' '}
																			{__(
																				'out of 5',
																				'multivendorx'
																			)}
																		</span>
																	</div>
																)}
														</div>
														<div className="contact-wrapper">
															{store.phone && (
																<span>
																	<i className="dashicons dashicons-phone" />{' '}
																	{typeof store.phone ===
																		'object'
																		? `${store.phone.country_code || ''} ${store.phone.phone || ''}`
																		: store.phone}
																</span>
															)}

															{store.address && (
																<span>
																	<i className="dashicons dashicons-location" />
																	{store.address}
																</span>
															)}
														</div>
													</div>
												</div>
											</div>
										</div>
									))}
							</div>
						</div>
					</div>
				</>
			)
			}
			{!isWidget && (
				<div className="woocommerce multivendorx-store">
					<div className="view-tabs-wrapper">
						<ul className="view-tabs">
							<li
								className={viewMode === 'list' ? 'active' : ''}
								onClick={() => setViewMode('list')}
							>
								<a>{__('List', 'multivendorx')}</a>
							</li>

							<li
								className={viewMode === 'split' ? 'active' : ''}
								onClick={() => setViewMode('split')}
							>
								<a>{__('Split', 'multivendorx')} </a>
							</li>

							<li
								className={viewMode === 'map' ? 'active' : ''}
								onClick={() => setViewMode('map')}
							>
								<a> {__('Map', 'multivendorx')} </a>
							</li>
						</ul>
					</div>

					<div className="store-filter-wrapper">
						{apiKey != '' && (
							<>
								<div className="woocommerce-widget-layered-nav-list">
									<div className="woocommerce-product-search">
										<input
											type="text"
											className="search-field"
											value={filters.address}
											onChange={handleInputChange}
											placeholder={__(
												'Enter Address',
												'multivendorx'
											)}
										/>
										<button
											type="button"
											className="button location-button"
											onClick={requestUserLocation}
										>
											<svg
												xmlns="http://www.w3.org/2000/svg"
												width="16"
												height="16"
												viewBox="0 0 24 24"
												fill="none"
												stroke="#3d7a3e"
												stroke-width="2.2"
												stroke-linecap="round"
												stroke-linejoin="round"
											>
												<circle
													cx="12"
													cy="12"
													r="3"
												></circle>
												<line
													x1="12"
													y1="2"
													x2="12"
													y2="6"
												></line>
												<line
													x1="12"
													y1="18"
													x2="12"
													y2="22"
												></line>
												<line
													x1="2"
													y1="12"
													x2="6"
													y2="12"
												></line>
												<line
													x1="18"
													y1="12"
													x2="22"
													y2="12"
												></line>
											</svg>
										</button>
									</div>
								</div>
								<select
									name="distance"
									value={filters.distance}
									onChange={handleInputChange}
								>
									<option value="">
										{__('Within', 'multivendorx')}
									</option>
									<option value="5">5</option>
									<option value="10">10</option>
									<option value="25">25</option>
								</select>

								<select
									name="miles"
									value={filters.miles}
									onChange={handleInputChange}
								>
									<option value="miles">
										{__('Miles', 'multivendorx')}
									</option>
									<option value="km">
										{__('Kilometers', 'multivendorx')}
									</option>
									<option value="nm">
										{__('Nautical miles', 'multivendorx')}
									</option>
								</select>
							</>
						)}
						<select
							name="sort"
							value={topFilters.sort}
							onChange={handleInputChange}
						>
							<option value="name">
								{__('Select', 'multivendorx')}
							</option>
							<option value="category">
								{__('By Category', 'multivendorx')}
							</option>
							<option value="shipping">
								{__('By Shipping', 'multivendorx')}
							</option>
						</select>

						{topFilters.sort == 'category' && (
							<select
								name="category"
								value={topFilters.category || ''}
								onChange={handleInputChange}
							>
								<option value="">
									{__('Select Category', 'multivendorx')}
								</option>

								{categoryList.map((cat) => (
									<option key={cat.id} value={cat.id}>
										{cat.name}
									</option>
								))}
							</select>
						)}
						<select
							name="product"
							value={filters.product || ''}
							onChange={(e) =>
								setFilters((prev) => ({
									...prev,
									product: e.target.value
										? Number(e.target.value)
										: '',
								}))
							}
						>
							<option value="">
								{__('Select Product', 'multivendorx')}
							</option>

							{product.map((p) => (
								<option key={p.id} value={p.id}>
									{p.name}
								</option>
							))}
						</select>
					</div>
					<p>
						{__('Showing', 'multivendorx')} {data.length}{' '}
						{__('stores', 'multivendorx')}
					</p>
					<div className={`store-list-wrapper is-${viewMode}`}>
						<div className="store-list">
							{data &&
								visibleStores.map((store) => (
									<div key={store.id} className="store">
										<div className="store-body">
											<div className="store-header">
												{store.store_image ? (
													<div className="store-image">
														<img
															src={store.store_image}
															alt={store.store_name}
														/>
													</div>
												) : (
													<div className="avatar">
														{store.store_name
															?.charAt(0)
															.toUpperCase()}
													</div>
												)}

												<div className="store-details">
													<a href={`${storesList.store_page_url}${store.store_slug || ''}`}> <h4>{store.store_name}</h4> </a>
													<div className="review-rating">
														{store.rating !==
															undefined && (
																<div
																	className="star-rating"
																	role="img"
																	aria-label={sprintf(
																		__(
																			'Rated %s out of 5',
																			'multivendorx'
																		),
																		store.rating.toFixed(
																			2
																		)
																	)}
																>
																	<span
																		style={{
																			width: `${(store.rating / 5) * 100}%`,
																		}}
																	>
																		{__(
																			'Rated',
																			'multivendorx'
																		)}{' '}
																		<strong className="rating">
																			{store.rating.toFixed(
																				2
																			)}
																		</strong>{' '}
																		{__(
																			'out of 5',
																			'multivendorx'
																		)}
																	</span>
																</div>
															)}
													</div>
													{store.phone &&
														store.address && (
															<div className="contact-wrapper">
																{store.phone && (
																	<span>
																		<i className="dashicons dashicons-phone" />{' '}
																		{
																			store
																				.phone
																				.country_code
																		}{' '}
																		{
																			store
																				.phone
																				.phone
																		}
																	</span>
																)}

																{store.address && (
																	<span>
																		<i className="dashicons dashicons-location" />
																		{
																			store.address
																		}
																	</span>
																)}
															</div>
														)}
												</div>
											</div>

											<div className="store-products">
												<h4>
													{' '}
													{__(
														'Products',
														'multivendorx'
													)}{' '}
												</h4>
												{storeTopProducts[store.id]
													?.length > 0 ? (
													<ul className="products columns-3">
														{storeTopProducts[
															store.id
														]?.map((p) => {
															return (
																<li
																	key={p.id}
																	className={`product type-product post-${p.id} status-publish first instock product_cat-${p.categories?.[0]?.slug || 'uncategorized'} has-post-thumbnail shipping-taxable purchasable product-type-simple`}
																>
																	<a
																		href={
																			p.permalink ||
																			'#'
																		}
																		className="woocommerce-LoopProduct-link woocommerce-loop-product__link"
																	>
																		<img
																			width="324"
																			height="324"
																			src={
																				p
																					.images?.[0]
																					?.src ||
																				storesList?.placeholder_url
																			}
																			alt={
																				p.name ||
																				'Product Image'
																			}
																			className={
																				p
																					.images?.[0]
																					?.src
																					? 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail'
																					: 'woocommerce-placeholder wp-post-image'
																			}
																			decoding="async"
																			loading="lazy"
																		/>
																		<h2 className="woocommerce-loop-product__title">
																			{p.name}
																		</h2>

																		{/* Add star rating if available */}
																		{p.average_rating >
																			0 && (
																				<div
																					className="star-rating"
																					role="img"
																					aria-label={`Rated ${p.average_rating} out of 5`}
																				>
																					<span
																						style={{
																							width: `${(p.average_rating / 5) * 100}%`,
																						}}
																					>
																						Rated{' '}
																						<strong className="rating">
																							{
																								p.average_rating
																							}
																						</strong>{' '}
																						out
																						of
																						5
																					</span>
																				</div>
																			)}

																		{/* Price HTML */}
																		{p.price_html && (
																			<span
																				className="price"
																				dangerouslySetInnerHTML={{
																					__html: p.price_html,
																				}}
																			/>
																		)}
																	</a>
																</li>
															);
														})}
													</ul>
												) : (
													<div className="no-products-found">
														<p>No products found</p>
													</div>
												)}
											</div>
										</div>
									</div>
								))}
							{totalPages > 1 && (
								<nav className="woocommerce-pagination">
									<ul className="page-numbers">
										<li>
											<button
												disabled={page === 1}
												onClick={() =>
													setPage((p) => p - 1)
												}
												className="page-numbers"
											>
												{__('Previous', 'multivendorx')}
											</button>
										</li>

										<li>
											<span className="page-numbers current">
												{page}
											</span>
										</li>

										<li>
											<button
												disabled={page >= totalPages}
												onClick={() =>
													setPage((p) => p + 1)
												}
												className="page-numbers"
											>
												{__('Next', 'multivendorx')}
											</button>
										</li>
									</ul>
								</nav>
							)}
						</div>
						{showMap && (
							<div className="store-map">{renderMapComponent()}</div>
						)}
					</div>
				</div>
			)}
		</>
	);
};

export default MarketplaceStoreList;
