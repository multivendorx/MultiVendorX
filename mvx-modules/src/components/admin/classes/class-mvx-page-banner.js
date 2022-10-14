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
					<p className='mvx-banner-description'>

					With MultiendorX Pro You Get More Control Over Every Aspect of Your Marketplace
					<br/>
					1.All Product Type Support 
					<br/>
					2.Marketplace-Friendly Payment Options
					<br/>
					3.Dynamic Membership Package Builder 
					<br/>
					4.Better Communication with Live Chat 
					<br/>
					5.Automated Invoice Generation
					<br/>
					6.Identity Verification through Social Media
					<br/>
					7.Product Import and Export
					<br/>
					8.Advanced Analytics Options
					<br/>	
					9.Inventory Management with Stock Alerts
					<br/>
					10.Vacation-Mode Marketplace Management

					Upgrade to <a href="https://wcmarketpstag.wpengine.com/pricing/">MultiVendorX Pro</a> Today!!


					</p>
				</div>
			</div>
		);
	}
}
export default MVX_Banner_Adv;
