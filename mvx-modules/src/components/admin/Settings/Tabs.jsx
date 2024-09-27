import { Link } from 'react-router-dom';
import HeaderSection from './Header.jsx';
import BannerSection from '../classes/class-mvx-page-banner.js';

const Tabs = (props) => {
    const getApiUrl = () => {
        return props.model.find(({ modulename }) => modulename === props.currentTab).apiurl;
    }

    const getTabDescription = () => {
        return props.model.map((tab) => {
			return props.currentTab === tab.modulename &&
                <div className="mvx-tab-description-start">
                    <div className="mvx-tab-name">{tab.tablabel}</div>
                    <p>{tab.description}</p>
                </div>
		});
    }
    
    return (
        <>
            <div className={`mvx-general-wrapper mvx-${props.queryName}`}>
                <HeaderSection />
				<div className="mvx-container">
					<div
						className={`mvx-middle-container-wrapper ${
							props.horizontally
								? 'mvx-horizontal-tabs'
								: 'mvx-vertical-tabs'
						}`}
					>
                        { getTabDescription() }
						<div className="mvx-middle-child-container">
                            <div className="mvx-current-tab-lists">
                                {
                                    props.model.map((tab) => {
                                        return tab.link ? (
                                            <a href={ tab.link }>
                                                { tab.icon && <i className={`mvx-font ${tab.icon}`}></i> }
                                                { tab.tablabel }
                                            </a>
                                        ) : (
                                            <Link
                                                className={ props.currentTab === tab.modulename ? 'active-current-tab' : ''}
                                                to={ `?page=mvx#&submenu=${tab.submenu}&name=${tab.modulename}` }
                                            >
                                                { tab.icon && <i className={`mvx-font ${tab.icon}`} ></i> }
                                                { tab.tablabel }
                                            </Link>
                                        );
                                    })
                                }
                            </div>
                            <div className="mvx-tab-content">
                                { props.getCurrentTab(props.currentTab, getApiUrl()) }
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