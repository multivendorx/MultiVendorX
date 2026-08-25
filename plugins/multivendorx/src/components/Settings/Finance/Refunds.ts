import { __ } from '@wordpress/i18n';


export default {
	id: 'refunds',
	priority: 5,
	headerTitle: __('Refunds', 'multivendorx'),
	headerDescription: __(
		'Control refund rules, eligibility stages, and valid claim periods.',
		'multivendorx'
	),
	headerIcon: 'marketplace-refund',
	submitUrl: 'settings',
	modal: [
		{
			key: 'customer_refund_status',
			type: 'checkbox',
			label: __('Eligible order status for refund', 'multivendorx'),

			settingDescription: __(
				'Customers can only request a refund when their order is in the selected status.',
				'multivendorx'
			),
			moduleEnabled: 'marketplace-refund',
			options: [
				{
					key: 'completed',
					label: __('Completed', 'multivendorx'),
					value: 'completed',
				},
				{
					key: 'delivered',
					label: __('Delivered', 'multivendorx'),
					value: 'delivered',
					proSetting: true,
				},
				{
					key: 'processing',
					label: __('Processing', 'multivendorx'),
					value: 'processing',
				},
				{
					key: 'shipped',
					label: __('Shipped', 'multivendorx'),
					value: 'shipped',
					proSetting: true,
				},
			],
			selectDeselect: true,
		},
		{
			key: 'refund_days',
			type: 'number',
			label: __('Refund claim period', 'multivendorx'),
			settingDescription: __(
				'Set the number of days within which a customer can request a refund.',
				'multivendorx'
			),
			moduleEnabled: 'marketplace-refund',
			size: 8,
			max: 365,
			postText: 'days',
		},
		{
			key: 'customer_return_status',
			type: 'checkbox',
			label: __('Eligible order status for return', 'multivendorx'),

			settingDescription: __(
				'Customers can only request a return when their order is in the selected status.',
				'multivendorx'
			),
			moduleEnabled: 'marketplace-refund',
			options: [
				{
					key: 'completed',
					label: __('Completed', 'multivendorx'),
					value: 'completed',
				},
				{
					key: 'delivered',
					label: __('Delivered', 'multivendorx'),
					value: 'delivered',
					proSetting: true,
				},
				{
					key: 'processing',
					label: __('Processing', 'multivendorx'),
					value: 'processing',
				},
				{
					key: 'shipped',
					label: __('Shipped', 'multivendorx'),
					value: 'shipped',
					proSetting: true,
				},
			],
			selectDeselect: true,
		},
		{
			key: 'return',
			label: __('Image required', 'multivendorx'),
			settingDescription: __(
				'Configure whether customers must upload an image when requesting a return.',
				'multivendorx'
			),
			type: 'checkbox',
			moduleEnabled: 'marketplace-refund',
			options: [
				{

					key: 'image_require',
					value: 'image_require',
				},
			],
			look: 'toggle',
		},
		{
			key: 'refund_reasons',
			type: 'expandable-panel',
			label: __('Refund reasons', 'multivendorx'),
			placeholder: __('Enter refund reasons here…', 'multivendorx'),
			settingDescription: __(
				'Add one or more reasons that stores can select when handling refund requests.',
				'multivendorx'
			),
			addNewBtn: true,
			min: 1,
			addNewTemplate: {
				label: 'New Reasons',
				editableFields: {
					title: true,
					description: false,
				},
				disableBtn: true,
			},
			modal: [],
			moduleEnabled: 'marketplace-refund',
		},
	],
};