import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';

import {
	ColumnComponent,
	ContainerComponent,
	InformationItemComponent,
	PopupComponent,
	NavigatorHeaderComponent,
} from '@zyra/components';
import { TableCard } from '@zyra/table';
import ShowProPopup from '../Popup/Popup';
import { applyFilters } from '@wordpress/hooks';
import { dummyCohorts } from './CohortUtil';

export interface CohortRow {
	id?: number;
	moodle_cohort_id?: number;
	cohort_name?: string;
	products?: Record<string, string>;
	enrolled_user?: number;
	view_users_url?: string;
	product_image?: string;
	status?: string;
}

const Cohort: React.FC = () => {
	const [openPopup, setopenPopup] = useState(false);

	let tableProps: any = {};

	// Define table headers
	const headers = {
		cohort_name: {
			label: __('Cohorts', 'moowoodle'),
			render: (row: CohortRow) => (
				<InformationItemComponent
					title={row.cohort_name}
					avatar={{ iconClass: 'cohort' }}
				/>
			),
		},

		products: {
			label: __('Product', 'moowoodle'),
			render: (row: CohortRow) => (
				<>
					{row.products && Object.keys(row.products).length
						? Object.entries(row.products).map(([name], index) => (
							<React.Fragment key={index}>
								{name}
							</React.Fragment>
						))
						: '-'}
				</>
			),
		},

		enrolled_user: {
			label: __('Enrolled users', 'moowoodle'),
			render: (row: CohortRow) => <>{row.enrolled_user || 0}</>,
		},

		action: {
			type: 'action',
			label: __('Action', 'moowoodle'),

			actions: [
				{
					label: __('Sync Cohort Data', 'moowoodle'),
					icon: 'refresh',
					onClick: (row) => {
						setopenPopup(true);
					},
				},

				{
					label: (row: CohortRow) => {
						return row?.products && Object.keys(row.products).length
							? __(
								'Sync Cohort Data & Update Product',
								'moowoodle'
							)
							: __('Create Product', 'moowoodle');
					},

					icon: (row: CohortRow) => {
						return row?.products && Object.keys(row.products).length
							? 'update-product'
							: 'add-product';
					},
					onClick: (row) => {
						setopenPopup(true);
					},
				},
			],
		},
	};

	const defaultTableProps = {
		headers,

		rows: dummyCohorts,
		totalRows: dummyCohorts.length,
		search: {
			placeholder: __('Search...', 'moowoodle'),
		},
	};

	tableProps = applyFilters(
		'moowoodle_cohort_table_props',
		defaultTableProps
	);

	const handleTableWrapperClick = () => {
		if (!appLocalizer.khali_dabba) {
			setopenPopup(true);
		}
	};

	return (
		<>
			{openPopup && (
				<PopupComponent
					position="lightbox"
					open={openPopup}
					onClose={() => setopenPopup(false)}
					width={31.25}
					height="auto"
				>
					<ShowProPopup />
				</PopupComponent>
			)}

			<NavigatorHeaderComponent
				headerIcon="cohort"
				headerDescription={__(
					'Cohort information is presented with associated products and student enrollments to support administrative actions.',
					'moowoodle'
				)}
				headerTitle={__('Cohorts', 'moowoodle')}
			/>
			<ContainerComponent general>
				<ColumnComponent>
					<div className="demo-wrapper" onClick={handleTableWrapperClick}>
						{!appLocalizer.khali_dabba && (
							<div className="watermark">{__('This is sample Data', 'moowoodle')}</div>
						)}
						<TableCard {...tableProps} />
					</div>
				</ColumnComponent>
			</ContainerComponent>
		</>
	);
};

export default Cohort;
