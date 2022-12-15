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
					2.Instant Payment to Vendors 
					<br/>
					3.Create Vendor Membership Plans
					<br/>
					4.Quick Real-Time Chat with Vendors
					<br/>
					5.Hassle-free Billing Invoice and Packing Slip
					<br/>
					6.Verify Vendors to Secure Marketplace
					<br/>
					7.Seamless Product Import and Export
					<br/>
					8.Advanced Sales and Tax Analytics
					<br/>	
					9.Inventory Tracker
					<br/>
					10.Marketplace Vacation-Mode

					Upgrade to <a href="https://multivendorx.com/pricing/">MultiVendorX Pro</a> Today!!


					</p>
				</div>
			</div>
		);
	}
}
export default MVX_Banner_Adv;
