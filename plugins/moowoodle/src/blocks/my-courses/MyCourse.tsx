/* global courseMyAcc */
import React, { useEffect, useState, useCallback } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import axios from 'axios';
import './MyCourse.scss';
import { applyFilters } from '@wordpress/hooks';

interface Course {
	user_name?: string;
	course_name?: string;
	enrollment_date?: string;
	password?: string;
	moodle_url?: string;
}

const MyCourse: React.FC = () => {
	const [courses, setCourses] = useState<Course[]>([]);
	const [totalRows, setTotalRows] = useState<number>(0);
	const [currentPage, setCurrentPage] = useState<number>(1);
	const [rowsPerPage] = useState<number>(5);
	const [loading, setLoading] = useState<boolean>(false);
	const [error, setError] = useState<string>('');
	const [statusFilter, setStatusFilter] = useState<string>('');
	const [statusCounts, setStatusCounts] = useState({
		all: 0,
		enrolled: 0,
		expired: 0,
		unenrolled: 0,
	});

	const totalPages = Math.ceil(totalRows / rowsPerPage);

	const fetchCourses = useCallback(() => {
		setLoading(true);
		setError('');
		axios({
			method: 'GET',
			url: `${courseMyAcc.apiUrl}/moowoodle/v1/my-courses`,
			headers: { 'X-WP-Nonce': courseMyAcc.nonce },
			params: {
				page: currentPage,
				row: rowsPerPage,
				status: statusFilter,
			},
		})
			.then((response) => {
				setCourses(response.data || []);
				setTotalRows(Number(response.headers['x-wp-total']) || 0);
				setStatusCounts({
					all: Number(response.headers['x-wp-total']) || 0,
					enrolled: Number(response.headers['x-wp-enrolled']) || 0,
					expired: Number(response.headers['x-wp-expired']) || 0,
					unenrolled: Number(response.headers['x-wp-unenrolled']) || 0,
				});
			})
			.catch(() => {
				setError(__('Failed to fetch courses.', 'moowoodle'));
			})
			.finally(() => setLoading(false));
	}, [currentPage, rowsPerPage, statusFilter]);

	useEffect(() => {
		fetchCourses();
	}, [fetchCourses]);

	useEffect(() => {
		setCurrentPage(1);
	}, [statusFilter]);

	const filters = [
		{
			key: '',
			label: __('All Courses', 'moowoodle'),
			count: statusCounts.all,
			icon: `
				<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none">
					<path
						d="M12 10.4V20M12 10.4C12 8.15979 12 7.03969 11.564 6.18404C11.1805 5.43139 10.5686 4.81947 9.81596 4.43597C8.96031 4 7.84021 4 5.6 4H4.6C4.03995 4 3.75992 4 3.54601 4.10899C3.35785 4.20487 3.20487 4.35785 3.10899 4.54601C3 4.75992 3 5.03995 3 5.6V16.4C3 16.9601 3 17.2401 3.10899 17.454C3.20487 17.6422 3.35785 17.7951 3.54601 17.891C3.75992 18 4.03995 18 4.6 18H7.54668C8.08687 18 8.35696 18 8.61814 18.0466C8.84995 18.0879 9.0761 18.1563 9.29191 18.2506C9.53504 18.3567 9.75977 18.5065 10.2092 18.8062L12 20M12 10.4C12 8.15979 12 7.03969 12.436 6.18404C12.8195 5.43139 13.4314 4.81947 14.184 4.43597C15.0397 4 16.1598 4 18.4 4H19.4C19.9601 4 20.2401 4 20.454 4.10899C20.6422 4.20487 20.7951 4.35785 20.891 4.54601C21 4.75992 21 5.03995 21 5.6V16.4C21 16.9601 21 17.2401 20.891 17.454C20.7951 17.6422 20.6422 17.7951 20.454 17.891C20.2401 18 19.9601 18 19.4 18H16.4533C15.9131 18 15.643 18 15.3819 18.0466C15.15 18.0879 14.9239 18.1563 14.7081 18.2506C14.465 18.3567 14.2402 18.5065 13.7908 18.8062L12 20"
						stroke="#000000"
						stroke-width="2"
						stroke-linecap="round"
						stroke-linejoin="round"
					/>
				</svg>
			`,
		},
		{
			key: 'enrolled',
			label: __('Enrolled Courses', 'moowoodle'),
			count: statusCounts.enrolled,
			icon: `
				<svg xmlns="http://www.w3.org/2000/svg" width="40px" height="40px" viewBox="0 0 24 24" fill="none">
					<circle cx="12" cy="12" r="10" stroke="#000" stroke-width="1.5"/>
					<path d="M15.4137 10.941C16.1954 11.4026 16.1954 12.5974 15.4137 13.059L10.6935 15.8458C9.93371 16.2944 9 15.7105 9 14.7868L9 9.21316C9 8.28947 9.93371 7.70561 10.6935 8.15419L15.4137 10.941Z" stroke="#000" stroke-width="1.5"/>
				</svg>
			`,
		},
		{
			key: 'expired',
			label: __('Expired Courses', 'moowoodle'),
			count: statusCounts.expired,
			icon: `
				<svg xmlns="http://www.w3.org/2000/svg" width="40px" height="40px" viewBox="0 0 24 24" fill="none">
					<path d="M12 7V12L14.5 13.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			`,
		},
		{
			key: 'unenrolled',
			label: __('Unenrolled', 'moowoodle'),
			count: statusCounts.unenrolled,
			icon: `
				<svg xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" height="40px" width="40px" version="1.1" viewBox="0 0 24 24" xml:space="preserve">
					<g id="inactive">
						<path d="M13.6,23.9c-7.8,1-14.5-5.6-13.5-13.5c0.7-5.3,5-9.7,10.3-10.3c7.8-1,14.5,5.6,13.5,13.5C23.2,18.9,18.9,23.2,13.6,23.9z    M13.7,2.1C6.9,1,1,6.9,2.1,13.7c0.7,4.1,4,7.5,8.2,8.2C17.1,23,23,17.1,21.9,10.3C21.2,6.2,17.8,2.8,13.7,2.1z"/>
						<polyline points="5.6,4.2 19.8,18.3 18.4,19.8 4.2,5.6  "/>
					</g>
				</svg>			
			`,
		},
	];

	const renderTableContent = () => {
		if (loading) {
			return (
				<>
					{Array.from({ length: rowsPerPage }).map((_, index) => (
						<tr
							key={index}
							className="woocommerce-orders-table__row moowoodle-skeleton-row"
						>
							<td
								className="woocommerce-orders-table__cell course-details"
								data-label={__('Course Name', 'moowoodle')}
							>
								<div className="course-thumbnail">
									<span
										className="skeleton skeleton-circular"
										style={{
											width: '3rem',
											height: '3rem',
										}}
									/>
								</div>

								<div className="details">
									<span
										className="skeleton skeleton-text"
										style={{
											width: '10rem',
											height: '1rem',
										}}
									/>

									<span
										className="skeleton skeleton-text"
										style={{
											width: '7rem',
											height: '0.75rem',
										}}
									/>
								</div>
							</td>

							<td
								className="woocommerce-orders-table__cell"
								data-label={__('Enrolment Date', 'moowoodle')}
							>
								<span
									className="skeleton skeleton-text"
									style={{
										width: '7rem',
										height: '1rem',
									}}
								/>
							</td>

							<td
								className="woocommerce-orders-table__cell"
								data-label={__('Action', 'moowoodle')}
							>
								<span
									className="skeleton skeleton-rectangular"
									style={{
										width: '7rem',
										height: '2.25rem',
									}}
								/>
							</td>
						</tr>
					))}
				</>
			);
		}

		return courses.map((course, index) => (
			<tr key={index} className="woocommerce-orders-table__row">
				<td
					className="woocommerce-orders-table__cell course-details"
					data-label={__('Course Name', 'moowoodle')}
				>
					<div className='course-thumbnail'>
						{course.product_image ? (
							<img
								src={course.product_image}
								alt={course.course_name || __('Unknown Course', 'moowoodle')}
								className="course-thumbnail"
							/>
						) : (
							<svg xmlns="http://www.w3.org/2000/svg" width="40px" height="40px" viewBox="0 0 24 24" fill="none" class="">
								<path d="M12 10.4V20M12 10.4C12 8.15979 12 7.03969 11.564 6.18404C11.1805 5.43139 10.5686 4.81947 9.81596 4.43597C8.96031 4 7.84021 4 5.6 4H4.6C4.03995 4 3.75992 4 3.54601 4.10899C3.35785 4.20487 3.20487 4.35785 3.10899 4.54601C3 4.75992 3 5.03995 3 5.6V16.4C3 16.9601 3 17.2401 3.10899 17.454C3.20487 17.6422 3.35785 17.7951 3.54601 17.891C3.75992 18 4.03995 18 4.6 18H7.54668C8.08687 18 8.35696 18 8.61814 18.0466C8.84995 18.0879 9.0761 18.1563 9.29191 18.2506C9.53504 18.3567 9.75977 18.5065 10.2092 18.8062L12 20M12 10.4C12 8.15979 12 7.03969 12.436 6.18404C12.8195 5.43139 13.4314 4.81947 14.184 4.43597C15.0397 4 16.1598 4 18.4 4H19.4C19.9601 4 20.2401 4 20.454 4.10899C20.6422 4.20487 20.7951 4.35785 20.891 4.54601C21 4.75992 21 5.03995 21 5.6V16.4C21 16.9601 21 17.2401 20.891 17.454C20.7951 17.6422 20.6422 17.7951 20.454 17.891C20.2401 18 19.9601 18 19.4 18H16.4533C15.9131 18 15.643 18 15.3819 18.0466C15.15 18.0879 14.9239 18.1563 14.7081 18.2506C14.465 18.3567 14.2402 18.5065 13.7908 18.8062L12 20" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
						)
						}
					</div>
					<div className="details">
						{course.course_name || __('Unknown Course', 'moowoodle')}
						<span>
							{__('User Name: ', 'moowoodle')}
							{course.user_name || __('N/A', 'moowoodle')}
						</span>
					</div>
				</td>
				<td
					className="woocommerce-orders-table__cell"
					data-label={__('Enrolment Date', 'moowoodle')}
				>
					{course.enrollment_date ||
						__('No Date Available', 'moowoodle')}
				</td>
				<td
					className="woocommerce-orders-table__cell"
					data-label={__('Action', 'moowoodle')}
				>
					{applyFilters(
						'moowoodle_my_course_actions',
						course.moodle_url || course.product_url ? (
							<div
								className="woocommerce-button wp-element-button moowoodle"
								onClick={() =>
									window.open(
										course.status === 'expired' || course.status === 'unenrolled'
											? course.product_url
											: course.moodle_url,
										'_blank',
										'noopener,noreferrer'
									)
								}
								role="button"
								tabIndex={0}
							>
								{__(
									course.status === 'expired' || course.status === 'unenrolled'
										? 'Renew'
										: 'Open Course',
									'moowoodle'
								)}
							</div>
						) : (
							<span className="disabled">
								{__('No Link', 'moowoodle')}
							</span>
						),
						course
					)}
				</td>
			</tr>
		));
	};

	const renderPagination = () => {
		if (totalPages <= 1) {
			return null;
		}

		return (
			<div className="pagination">
				<button
					disabled={currentPage === 1 || loading}
					onClick={() =>
						setCurrentPage((prev) => Math.max(prev - 1, 1))
					}
				>
					{__('Previous', 'moowoodle')}
				</button>
				<span>
					{sprintf(
						// translators: %1$d is the current page number, %2$d is the total number of pages.
						__('Page %1$d of %2$d', 'moowoodle'),
						currentPage,
						totalPages
					)}
				</span>
				<button
					disabled={currentPage === totalPages || loading}
					onClick={() =>
						setCurrentPage((prev) => Math.min(prev + 1, totalPages))
					}
				>
					{__('Next', 'moowoodle')}
				</button>
			</div>
		);
	};

	return (
		<div className="moowoodle-my-courses woocommerce-js">
			<div className="moowoodle-course-filters">
				{filters.map((filter) => (
					<div
						key={filter.key || 'all'}
						className={`filter-card ${statusFilter === filter.key ? 'active' : ''
							}`}
						onClick={() => !loading && setStatusFilter(filter.key)}
						role="button"
						tabIndex={0}
					>
						<div className="filter-icon" dangerouslySetInnerHTML={{ __html: filter.icon }} />
						{loading ? (
							<>
								<span className="skeleton skeleton-text" style={{ width: '3rem', height: '1.5rem' }} />
								<span className="skeleton skeleton-text" style={{ width: '7rem', height: '1rem' }} />
								<span className="skeleton skeleton-text" style={{ width: '5rem', height: '0.875rem' }} />
							</>
						) : (
							<>
								<div className="filter-count">{filter.count}</div>
								<div className="filter-label">{filter.label}</div>
								<span>{__('View all →', 'moowoodle')}</span>
							</>
						)}
					</div>
				))}
			</div>
			{loading ? (
				<table className="moowoodle-table shop_table shop_table_responsive my_account_orders">
					<thead>
						<tr>
							<th className="woocommerce-orders-table__header">
								{__('Course Name', 'moowoodle')}
							</th>
							<th className="woocommerce-orders-table__header">
								{__('Enrolment Date', 'moowoodle')}
							</th>
							<th className="woocommerce-orders-table__header">
								{__('Action', 'moowoodle')}
							</th>
						</tr>
					</thead>

					<tbody>{renderTableContent()}</tbody>
				</table>
			) : courses.length > 0 ? (
				<>
					<table className="moowoodle-table shop_table shop_table_responsive my_account_orders">
						<thead>
							<tr>
								<th className="woocommerce-orders-table__header">
									{__('Course Name', 'moowoodle')}
								</th>
								<th className="woocommerce-orders-table__header">
									{__('Enrolment Date', 'moowoodle')}
								</th>
								<th className="woocommerce-orders-table__header">
									{__('Action', 'moowoodle')}
								</th>
							</tr>
						</thead>

						<tbody>{renderTableContent()}</tbody>
					</table>

					{renderPagination()}
				</>
			) : (
				<div className="woocommerce-notices-wrapper">
					<ul className="woocommerce-error" role="alert">
						<li>
							{__(
								"You haven't purchased any courses yet.",
								'moowoodle'
							)}
						</li>
					</ul>
				</div>
			)}
			{error && (
				<div className="woocommerce-notices-wrapper">
					<ul className="woocommerce-error" role="alert">
						<li>{error}</li>
					</ul>
				</div>
			)}
		</div>
	);
};

export default MyCourse;