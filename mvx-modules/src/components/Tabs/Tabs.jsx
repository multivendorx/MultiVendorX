import { Link } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import HeaderSection from '../Headers/Headers';
import BannerSection from '../Banner/Banner';

const Tabs = ( props ) => {
    const { tabData, currentTab, subMenu, getForm } = props;

    // Get the description of the current tab.
    const getTabDescription = () => {
        return tabData.map( ( tab ) => {
			return  tab.id === currentTab &&
                <div className="mvx-tab-description-start">
                    <div className="mvx-tab-name">{ __( tab.name ) }</div>
                    <p>{ __( tab.description ) }</p>
                </div>
		});
    }
    
    return (
        <>
            <div className={ `mvx-general-wrapper mvx-${ props.queryName }` }>
                <HeaderSection />
				<div className="mvx-container">
					<div
						className={ `mvx-middle-container-wrapper ${
							props.horizontally
								? 'mvx-horizontal-tabs'
								: 'mvx-vertical-tabs'
						}`}
                    >
                        {/* Render name and description of the current tab */}
                        { getTabDescription() }
						<div className="mvx-middle-child-container">
                            <div className="mvx-current-tab-lists">
                                {
                                    tabData.map( ( tab ) => {
                                        return tab.link ? (
                                            <a href={ tab.link }>
                                                { tab.icon && <i className={`mvx-font ${ tab.icon }`}></i> }
                                                { __( tab.name ) }
                                            </a>
                                        ) : (
                                            <Link
                                                className={ currentTab === tab.id ? 'active-current-tab' : '' }
                                                to={ `?page=mvx#&submenu=${ subMenu }&name=${ tab.id }` }
                                            >
                                                { tab.icon && <i className={ `mvx-font ${ tab.icon }` } ></i> }
                                                { __( tab.name ) }
                                            </Link>
                                        );
                                    })
                                }
                            </div>
                            <div className="mvx-tab-content">
                                {/* Render the form from parent component for better controll */}
                                { getForm( currentTab )}
							</div>
						</div>
					</div>
					<BannerSection />
				</div>
			</div>
        </>
    );
}

export default Tabs;