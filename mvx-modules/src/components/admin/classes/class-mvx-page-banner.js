/* global appLocalizer */
import React, { Component } from 'react';
class MVX_Banner_Adv extends Component {
	render() {
		return (
			<div className="mvx-sidebar">
				<a href={appLocalizer.knowledgebase} target="__blank">
					<img
						alt={appLocalizer.marketplace_text}
						src={appLocalizer.multivendor_logo}
					/>
				</a>
			</div>
		);
	}
}
export default MVX_Banner_Adv;
