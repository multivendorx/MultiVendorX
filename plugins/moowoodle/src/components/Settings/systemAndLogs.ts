import { __ } from '@wordpress/i18n';

export default {
	id: 'system-logs',
	priority: 3,
	headerTitle: __('System & Logs', 'moowoodle'),
	headerDescription: __(
		'Manage advanced settings for synchronization, error logging, and connection handling.',
		'moowoodle'
	),
	headerIcon: 'tools',
	submitUrl: 'settings',
	modal: [
		{
			key: 'moowoodle_adv_log',
			type: 'checkbox',
			label: __('Advance log', 'moowoodle'),
			desc: __(
				`<span class="highlighted-part">Activating this option will log more detailed error information. Enable it only when essential, as it may result in a larger log file.</span>`,
				'moowoodle'
			),
			options: [
				{
					key: 'moowoodle_adv_log',
					value: 'moowoodle_adv_log',
				},
			],
			look: 'toggle',
		},
		{
			key: 'section',
			type: 'section',
			title: __('System Operations', 'moowoodle'),
		},
		{
			key: 'moodle_timeout',
			type: 'text',
			desc: __(
				'When WordPress sends a request to the Moodle server for data, communication delays might exceed the default server connection timeout. You can customize the timeout parameters by adjusting them here. <br>Default: 5 seconds.',
				'moowoodle'
			),
			size: 8,
			postInsideText: __('sec', 'moowoodle'),
			label: __('Connection timeout', 'moowoodle'),
			parameter: __('Seconds', 'moowoodle'),
		},
		{
			key: 'schedule_interval',
			type: 'text',
			desc: __(
				'Select the interval for the user synchronization process. Based on this schedule, the cron job will run to sync users between WordPress and Moodle.',
				'moowoodle'
			),
			size: 8,
			postInsideText: __('min', 'moowoodle'),
			parameter: __('Minutes', 'moowoodle'),
			label: __('Automatic synchronization frequency', 'moowoodle'),
		},
		{
			key: 'moowoodle_adv_log',
			type: 'log',
			classes: 'log-section',
			apiLink: 'logs',
			fileName: 'error.txt',
			label: __('Logs', 'moowoodle'),
		},
	],
};
