/* global appLocalizer */
import { useEffect, useState } from 'react';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom';


import {
	TextInput,
	SelectInput,
	TextAreaInput,
	FileInput,
	ButtonInput,
} from '@zyra/inputs';
import { getApiLink, useModules } from '@zyra/core';
import {
	CardComponent,
	ColumnComponent,
	ContainerComponent,
	FormGroupWrapperComponent,
	FormGroupComponent,
	PopupComponent,
	NoticeComponent,
	NavigatorHeaderComponent,
} from '@zyra/components';
import { applyFilters } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { dashNavigate } from '@/services/commonFunction';
import './addProducts.scss';
import { htmlToText } from '../services/commonFunction';

const AddProduct = () => {
	const { modules } = useModules();
	const navigate = useNavigate();
	const { context_id } = useParams();
	const productId = context_id;

	const [product, setProduct] = useState({});
	const [translation, setTranslation] = useState([]);
	const [featuredImage, setFeaturedImage] = useState(null);
	const [galleryImages, setGalleryImages] = useState([]);
	const [errorMsg, setErrorMsg] = useState('');
	const [appeal, setAppeal] = useState(false);

	useEffect(() => {
		if (!productId) {
			return;
		}

		axios
			.get(`${appLocalizer.apiUrl}/wc/v3/products/${productId}`, {
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
			})
			.then(function (res) {
				const images = res.data.images || [];

				if (images.length > 0) {
					setFeaturedImage(images[0]);
				}

				setGalleryImages(images.slice(1));

				if (res.data.cost_of_goods_sold?.values?.[0]?.defined_value === null) {
					res.data.cost_of_goods_sold.values[0].defined_value = 0;
				}

				setProduct(res.data);
			})
			.catch((error) => {
				console.error('Error fetching product:', error);
			});
		if (modules.includes('wpml')) {
			axios({
				method: 'GET',
				url: getApiLink(appLocalizer, 'wpml'),
				headers: { 'X-WP-Nonce': appLocalizer.nonce },
				params: { product_id: productId },
			})
				.then((response) => {
					setTranslation(response.data);
				})
				.catch(() => {
					setTranslation([]);
				});
		}
	}, [productId]);

	const defaultTypeOptions = [{ label: 'Simple Product', value: 'simple' }];

	const typeOptions = applyFilters(
		'multivendorx_product_type_options',
		defaultTypeOptions
	);

	const handleChange = (field, value) => {
		setProduct((prev) => ({
			...prev,
			[field]: value,
		}));
	};

	const createProduct = () => {
		const imagePayload = [];

		if (featuredImage) {
			imagePayload.push({ id: featuredImage.id });
		}

		galleryImages.forEach((img) => {
			imagePayload.push({ id: img.id });
		});

		const payload = {
			...product,
			status: product.status ? product.status : appLocalizer.current_user?.allcaps?.publish_products ? 'publish' : 'draft',
			images: imagePayload,
			meta_data: [
				...product.meta_data,
				{
					key: 'multivendorx_store_id',
					value: appLocalizer.store_id,
				},
				{
					key: 'multivendorx_shipping_policy',
					value: product.shipping_policy || '',
				},
				{
					key: 'multivendorx_refund_policy',
					value: product.refund_policy || '',
				},
				{
					key: 'multivendorx_cancellation_policy',
					value: product.cancellation_policy || '',
				},
				{ key: '_is_auto_draft', value: false },
			],
		};

		const shouldContinue = applyFilters(
			'multivendorx_before_product_save',
			true,
			payload,
			setErrorMsg
		);

		if (shouldContinue) {
			const {
				booking_location_type,
				booking_duration_unit,
				exclude_global_add_ons,
				...productData
			} = payload;

			axios
				.post(
					`${appLocalizer.apiUrl}/wc/v3/products/${productId}`,
					productData,
					{ headers: { 'X-WP-Nonce': appLocalizer.nonce } }
				)
				.then(() => {
					window.location.reload();
				})
				.catch((error) => {
					console.error('Error updating product:', error);
				});
		}
	};

	const [checklist, setChecklist] = useState({
		name: false,
		image: false,
		price: false,
		stock: false,
		categories: false,
		policies: false,
	});

	useEffect(() => {
		let baseChecklist = {
			name: !!product.name,
			image: !!featuredImage,
			categories: !!product.categories,
			policies:
				!!product.shipping_policy ||
				!!product.refund_policy ||
				!!product.cancellation_policy,
		};

		if (product.type === 'simple') {
			baseChecklist.price = !!product.regular_price;
			baseChecklist.stock = !!product.stock_status;
		}

		const filteredChecklist = applyFilters(
			'product_checklist_items',
			baseChecklist,
			product
		);

		setChecklist(filteredChecklist);
	}, [product, featuredImage]);

	const handleTranslationClick = (lang) => {
		if (lang.translated_product_id) {
			dashNavigate(navigate, [
				'products',
				'edit',
				String(lang.translated_product_id),
			]);
			return;
		}

		// CASE 2: Translation does not exist → create or fetch translation
		axios({
			method: 'POST',
			url: getApiLink(appLocalizer, 'wpml'),
			headers: { 'X-WP-Nonce': appLocalizer.nonce },
			data: {
				product_id: productId,
				lang: lang.code,
			},
		}).then((res) => {
			if (res.data?.product_id) {
				dashNavigate(navigate, [
					'products',
					'edit',
					String(res.data?.product_id),
				]);
			}
		});
	};

	const checklistValues = Object.values(checklist);
	const completedCount = checklistValues.filter(Boolean).length;
	const totalCount = checklistValues.length;

	const productFields =
		appLocalizer.admin_settings?.['product-preferences']
			?.products_fields || [];
	const typeFields =
		appLocalizer.admin_settings?.['product-preferences']
			?.type_options || [];

	const rejectNote =
		product?.status === 'draft'
			? product?.meta_data?.find((m) => m.key === '_reject_note')?.value
			: null;

	const isAutoDraft = product?.meta_data?.some(
		(m) => m.key === '_is_auto_draft' && m.value === '1'
	);

	return (
		<>
			{translation
				?.filter((lang) => lang.is_default) // only include default language
				.map((lang) => (
					<div
						key={lang.code}
						className="multivendorx-translation-row"
					>
						<div>
							<img src={lang.flag_url} alt={lang.code} />
							<strong>{lang.native_name}</strong>
						</div>
					</div>
				))}
			{errorMsg && (
				<NoticeComponent
					type="error"
					validity={5000}
					displayPosition="notice"
					message={errorMsg}
				/>
			)}
			<NavigatorHeaderComponent
				headerTitle={__('Add Product', 'multivendorx')}
				headerDescription={__(
					'Enter your product details - name, price, stock, and image & publish.',
					'multivendorx'
				)}
				buttons={applyFilters('multivendorx_product_button', [
					{
						label: __('View', 'multivendorx'),
						icon: 'eye',
						color: 'border-purple',
						onClick: () =>
							window.open(product?.permalink, '_blank'),
					},
					{
						label: __('Save', 'multivendorx'),
						icon: 'save',
						onClick: () => createProduct(),
					},
				])}
			/>
			<ContainerComponent>
				<ColumnComponent grid={3}>
					<CardComponent
						title={__(
							'What kind of product is this?',
							'multivendorx'
						)}
						desc={__(
							'Choose the type that best describes what you are selling.',
							'multivendorx'
						)}
					>
						<FormGroupWrapperComponent>
							<FormGroupComponent>
								<SelectInput
									name="type"
									type="single-select"
									options={typeOptions}
									value={product.type}
									onChange={(selected) => {
										handleChange('type', selected);
									}}
								/>
							</FormGroupComponent>
						</FormGroupWrapperComponent>
					</CardComponent>
					<div className='sticky-card-wrapper'>
						<CardComponent
							title={__('Recommended', 'multivendorx')}
							toggle={true}
							action={
								<div className="admin-badge blue">
									{completedCount}/{totalCount}
								</div>
							}
							className="recommended-card"
						>
							<div className="checklist-wrapper">
								<ul>
									<li className={checklist.name ? 'checked' : ''}>
										<div className="check-icon">
											<span></span>
										</div>
										<div className="details">
											<div className="title">
												{__('Product Name', 'multivendorx')}
											</div>
											<div className="des">
												{__('A clear, descriptive title that helps customers find your product', 'multivendorx')}
											</div>
										</div>
									</li>
									{product.type === 'simple' && (
										<>
											<li
												className={
													checklist.price ? 'checked' : ''
												}
											>
												<div className="check-icon">
													<span></span>
												</div>
												<div className="details">
													<div className="title">
														Price
													</div>
													<div className="des">
														Set competitive prices
														including any sale or
														discount options
													</div>
												</div>
											</li>

											<li
												className={
													checklist.stock ? 'checked' : ''
												}
											>
												<div className="check-icon">
													<span></span>
												</div>
												<div className="details">
													<div className="title">
														Stock
													</div>
													<div className="des">
														A clear, descriptive title
														that helps customers find
														your product
													</div>
												</div>
											</li>
										</>
									)}
									<li
										className={checklist.image ? 'checked' : ''}
									>
										<div className="check-icon">
											<span></span>
										</div>
										<div className="details">
											<div className="title">
												{__('Product Images', 'multivendorx')}
											</div>
											<div className="des">
												{__('High-quality photos showing your product from multiple angles', 'multivendorx')}
											</div>
										</div>
									</li>

									<li
										className={
											checklist.categories ? 'checked' : ''
										}
									>
										<div className="check-icon">
											<span></span>
										</div>
										<div className="details">
											<div className="title">{__('Category', 'multivendorx')}</div>
											<div className="des">
												{__('Organize your product to help customers browse your store', 'multivendorx')}
											</div>
										</div>
									</li>

									<li
										className={
											checklist.policies ? 'checked' : ''
										}
									>
										<div className="check-icon">
											<span></span>
										</div>
										<div className="details">
											<div className="title">{__('Policies', 'multivendorx')}</div>
											<div className="des">
												{__('A clear, descriptive title that helps customers find your product', 'multivendorx')}
											</div>
										</div>
									</li>

									{applyFilters(
										'product_checklist_items_render',
										null,
										checklist,
										product
									)}
								</ul>
							</div>
						</CardComponent>
						{applyFilters(
							'multivendorx_product_sidebar_cards',
							null,
							product
						)}
					</div>
				</ColumnComponent>

				<ColumnComponent grid={6}>
					{rejectNote && (
						<CardComponent
							title={__(
								'Product Rejected by Admin',
								'multivendorx'
							)}
						// action={
						// <ButtonInput
						// 	buttons={[
						// 		{
						// 			icon: 'plus',
						// 			text: __('Appeal Decision', 'multivendorx'),
						// 			color: 'purple',
						// 			onClick: () => setAppeal(true),
						// 		},
						// 	]}
						// />}
						>
							<NoticeComponent
								type="error"
								title={__('Admin Note', 'multivendorx')}
								displayPosition="inline-notice"
								message={rejectNote}
							/>
						</CardComponent>
					)}
					<CardComponent
						title={__(
							'General information - Tell customers what you are selling',
							'multivendorx'
						)}
						desc={__(
							'A good name and description help people find your product and feel confident buying it.',
							'multivendorx'
						)}
					>
						<FormGroupWrapperComponent>
							<div className="form-group  ai-form">
								<label className="settings-form-label">
									{__('Product name', 'multivendorx')}
									{applyFilters(
										'multivendorx_product_field_suggestions',
										null,
										{
											product,
											setProduct,
											field: 'name',
										}
									)}
								</label>

								<div className="settings-input-content">
									<TextInput
										name="name"
										value={product.name}
										onChange={(value) =>
											handleChange('name', value)
										}
										disabled={
											modules.includes(
												'shared-listing'
											) && !isAutoDraft
										}
									/>
									<div className="settings-metabox-description">
										{__(
											'Use names your customers would actually search for.',
											'multivendorx'
										)}
									</div>
								</div>
							</div>

							{productFields.includes('general') && (
								<>
									<div className="form-group  ai-form">
										<label className="settings-form-label">
											{__(
												'Short description - One-line summary',
												'multivendorx'
											)}
											{applyFilters(
												'multivendorx_product_field_suggestions',
												null,
												{
													product,
													setProduct,
													field: 'short_description',
												}
											)}
										</label>

										<div className="settings-input-content">
											<TextAreaInput
												name="short_description"
												value={appLocalizer.tinymceApiKey ? product.short_description : htmlToText(product.short_description)}
												tinymceApiKey={appLocalizer.tinymceApiKey}
												onChange={(value) =>
													handleChange(
														'short_description',
														value
													)
												}
											/>
											<div className="settings-metabox-description">
												{__(
													'This short texts appears with the product - keep it punchy.',
													'multivendorx'
												)}
											</div>
										</div>
									</div>

									<div className="form-group  ai-form">
										<label className="settings-form-label">
											{__(
												'Full description',
												'multivendorx'
											)}
											{applyFilters(
												'multivendorx_product_field_suggestions',
												null,
												{
													product,
													setProduct,
													field: 'description',
												}
											)}
										</label>

										<div className="settings-input-content">
											<TextAreaInput
												name="description"
												value={appLocalizer.tinymceApiKey ? product.description : htmlToText(product.description)}
												tinymceApiKey={appLocalizer.tinymceApiKey}
												onChange={(value) =>
													handleChange(
														'description',
														value
													)
												}
											/>
											<div className="settings-metabox-description">
												{__(
													'More detail helps customers feel confident buying.',
													'multivendorx'
												)}
											</div>
										</div>
									</div>
								</>
							)}
						</FormGroupWrapperComponent>
					</CardComponent>
					<PopupComponent
						open={appeal}
						onClose={() => {
							setAppeal(false);
						}}
						width={31.25}
						header={{
							icon: 'announcement',
							title: __(
								'Appeal Rejection Decision',
								'multivendorx'
							),
							description: __(
								'Explain why you believe this product meets marketplace guidelines. Our team will review your appeal within 48 hours.',
								'multivendorx'
							),
						}}
						footer={
							<ButtonInput
								buttons={[
									{
										icon: 'close',
										text: __('Cancel', 'multivendorx'),
										color: 'red',
										onClick: () => setAppeal(false),
									},
									{
										icon: 'save',
										text: __(
											'Submit Appeal',
											'multivendorx'
										),
										// onClick: () => handleSubmit(),
									},
								]}
							/>
						}
					>
						<FormGroupWrapperComponent>
							<FormGroupComponent
								label={__(
									'Your appeal message',
									'multivendorx'
								)}
								htmlFor="title"
							>
								<TextAreaInput name="content" />
							</FormGroupComponent>
						</FormGroupWrapperComponent>
					</PopupComponent>
					{productFields.includes('general') && (
						<CardComponent
							title={__(
								'Pricing - How much does it cost?',
								'multivendorx'
							)}
							desc={__(
								'Set your normal price. If you are running a promotion, you can add sale price',
								'multivendorx'
							)}
						>
							<FormGroupWrapperComponent>
								{product?.type === 'simple' && (
									<>
										<FormGroupComponent
											row
											label={__('Regular price', 'multivendorx')}
										>
											<TextInput
												size="10rem"
												name="regular_price"
												value={product.regular_price}
												onChange={(value) =>
													handleChange('regular_price', value)
												}
											/>
										</FormGroupComponent>

										<FormGroupComponent
											row
											label={__('Sale price', 'multivendorx')}
										>
											<TextInput
												size="10rem"
												name="sale_price"
												value={product.sale_price}
												onChange={(value) =>
													handleChange('sale_price', value)
												}
											/>
										</FormGroupComponent>
									</>
								)}

								{['simple', 'variable'].includes(product?.type) &&
									Object.prototype.hasOwnProperty.call(
										product,
										'cost_of_goods_sold'
									) && (
										<FormGroupComponent
											row
											label={__(
												`Cost of goods ${appLocalizer.currency_symbol}`,
												'multivendorx'
											)}
										>
											<TextInput
												size="10rem"
												name="cost_of_goods_sold"
												value={
													product.cost_of_goods_sold?.values?.[0]
														?.defined_value ?? ''
												}
												onChange={(value) =>
													handleChange('cost_of_goods_sold', {
														values: [{ defined_value: value }],
													})
												}
											/>
										</FormGroupComponent>
									)}
							</FormGroupWrapperComponent>
						</CardComponent>
					)}
					{applyFilters(
						'multivendorx_add_product_middle_section',
						null,
						product,
						setProduct,
						handleChange,
						productFields,
						typeFields,
						modules,
						setFeaturedImage
					)}
				</ColumnComponent>
				<ColumnComponent grid={3}>
					{applyFilters(
						'multivendorx_add_product_right_section',
						null,
						product,
						setProduct,
						handleChange,
						productFields,
						setErrorMsg
					)}

					{modules.includes('wpml') && (
						<CardComponent
							title={__('Translations', 'multivendorx')}
							iconName="translate"
							toggle={true}
						>
							<FormGroupWrapperComponent>
								<div className="multivendorx-translation-list">
									{translation
										?.filter((lang) => !lang.is_default)
										.map((lang) => (
											<div
												key={lang.code}
												className="multivendorx-translation-row"
											>
												<div>
													<img
														src={lang.flag_url}
														alt={lang.code}
													/>
													<strong>
														{lang.native_name}
													</strong>
												</div>

												<button
													className="admin-btn btn-small btn-secondary"
													onClick={() =>
														handleTranslationClick(
															lang
														)
													}
												>
													<i className="adminfont-edit" />
												</button>
											</div>
										))}
								</div>
							</FormGroupWrapperComponent>
						</CardComponent>
					)}

					<CardComponent title={__('Upload image', 'multivendorx')}>
						<FormGroupWrapperComponent>
							<FormGroupComponent
								label={__('Features Image', 'multivendorx')}
							>
								<FileInput
									imageSrc={featuredImage?.thumbnail || ''}
									multiple={false}
									openUploader={__(
										'Select Featured Image',
										'multivendorx'
									)}
									onChange={(val) => {
										const [file] = Array.isArray(val)
											? val
											: [val];
										const url = file?.url || '';
										if (!val) {
											setFeaturedImage(null);
											return;
										}
										setFeaturedImage({
											id: file?.id, // wp.media id not available from current FileInputFieldComponent
											src: url,
											thumbnail: url,
										});
									}}
								/>
							</FormGroupComponent>
							{applyFilters('product_image_enhancement', null, {
								currentImage: featuredImage ?? null,
								isFeaturedImage: true,
								setImage: setFeaturedImage,
								product: product,
							})}

							{applyFilters(
								'multivendorx_show_product_gallery',
								true,
								{ product }
							) && (
									<FormGroupComponent label={__('Product gallery', 'multivendorx')}>
										<FileInput
											imageSrc={galleryImages.map((img) => img.thumbnail)}
											multiple={true}
											openUploader={__('Add Gallery Image', 'multivendorx')}
											onChange={(val) => {
												if (!val) {
													setGalleryImages([]);
													return;
												}

												const urls = Array.isArray(val) ? val : [val];

												const formatted = urls.map((file, index) => ({
													id: file?.id || galleryImages[index]?.id,
													src: file?.url,
													thumbnail: file?.url,
												}));

												setGalleryImages(formatted);
											}}
										/>
									</FormGroupComponent>
								)}

						</FormGroupWrapperComponent>
					</CardComponent>
				</ColumnComponent>
			</ContainerComponent>
		</>
	);
};

export default AddProduct;
