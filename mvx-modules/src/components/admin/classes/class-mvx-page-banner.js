/* global appLocalizer */
import React, { Component } from 'react';
class MVX_Banner_Adv extends Component {
	render() {
		return (
			<div className="mvx-sidebar">
				
				<div className='mvx-banner-right'>
					<div className='mvx-logo-right'>	<img
							src={appLocalizer.multivendor_right_white_logo}
						/> </div>
					<p className='mvx-banner-description'>Welcome to <span>MultivendorX Beta!</span> Our plugin offers a wide range of features, so feel free to explore. 
					<a href="https://github.com/wcmarketplace/MultivendorX/issues/new/choose">Git your ticket.</a> And as always do share your experience and help us improve!</p>
				</div>
			</div>
		);
	}
}
export default MVX_Banner_Adv;
