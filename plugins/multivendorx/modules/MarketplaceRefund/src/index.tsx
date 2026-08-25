/* global appLocalizer */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import PendingRefund from './PendingRefund';
import PendingReturn from './PendingReturn';

addFilter(
	'multivendorx_approval_queue_api_configs',
	'multivendorx/refund-and-return-api',
	(configs, { appLocalizer }) => {
		configs.push({
			id: 'refund-requests',
			url: `${appLocalizer.apiUrl}/wc/v3/orders`,
			params: {
				meta_key: 'multivendorx_store_id',
				status: 'refund-requested',
				page: 1,
				per_page: 1,
			},
			header: 'x-wp-total',
		});
		configs.push({ 
			id: 'return-requests', 
			url: `${appLocalizer.apiUrl}/wc/v3/orders`,
			params: {
				meta_key: 'multivendorx_store_id',
				status: 'return-requested',
				page: 1,
				per_page: 1,
			},
			header: 'x-wp-total',
		});
		return configs;
	}
);

addFilter(
	'multivendorx_approval_queue_tab',
	'multivendorx/refund-or-return-tab',
	(settingContent) => {
		settingContent.push({
			type: 'file',
			module: 'marketplace-refund',
			content: {
				id: 'refund-requests',
				headerTitle: __('Refunds', 'multivendorx'),
				headerDescription: __('Need your decision', 'multivendorx'),
				settingTitle: __('Refund tracker', 'multivendorx'),
				settingSubTitle: __(
					'Monitor refund trends and stay informed on returns.',
					'multivendorx'
				),
				headerIcon: 'marketplace-refund blue',
			},
		});
		settingContent.push({ 
			type: 'file', 
			module: 'marketplace-refund', 
			content: { 
				id: 'return-requests', 
				headerTitle: __( 'Returns', 'multivendorx' ), 
				headerDescription: __( 'Review customer return requests', 'multivendorx' ), 
				settingTitle: __( 'Return tracker', 'multivendorx' ), 
				settingSubTitle: __( 'Review, approve, or reject customer return requests.', 'multivendorx' ), 
				headerIcon: 'marketplace-refund blue', 
			}, 
		});
		return settingContent;
	}
);

addFilter(
	'multivendorx_approval_queue_tab_content',
	'multivendorx/refund-tab-content',
	(defaultForm, { tabId }) => {
		if (tabId === 'refund-requests') {
			return <PendingRefund />;
		}
		else if (tabId === 'return-requests') { 
			return <PendingReturn />; 
		}

		return defaultForm;
	}
);