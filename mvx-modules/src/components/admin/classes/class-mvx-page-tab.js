import React from "react";
import {
  BrowserRouter as Router,
  Link,
  useLocation,
  withRouter,
  useParams,
  NavLink
} from "react-router-dom";

import HeaderSection from './class-mvx-page-header';
import BannerSection from './class-mvx-page-banner';

export default class TabSection extends React.Component {
  state = {};
  constructor(props) {
    super(props);
    this.state = {};
  }

  renderTab = () => {
    let model = this.props.model;
    
    let funtion_name = this.props.funtion_name;
    let horizontally = this.props.horizontally;
    let no_banner = this.props.no_banner;
    let no_header = this.props.no_header;
    let query_name = this.props.query_name;

    let query_name_modified = this.props.vendor ? query_name.get("name") : query_name;

    let TabUI = model.map((m, index) => {
      return (
        query_name_modified == m.modulename ?
          <div className="mvx-tab-description-start">
            <div className="mvx-tab-name">{m.tablabel}</div>
            <p>{m.description}</p>
          </div>
        : ''
      );
    });

    let TabUIContent =
    <div className={`mvx-general-wrapper mvx-${query_name_modified}`}> 
    {no_header ? '' : <HeaderSection />}
    <div className="mvx-container">
        <div className={`mvx-middle-container-wrapper ${horizontally ? '' : 'mvx-vertical-tabs'}`}>
          {this.props.tab_description && this.props.tab_description == 'no' ? '' : TabUI}
          {this.props.no_tabs ? '' :
            <ul className={`mvx-current-tab-lists ${horizontally ? 'mvx-horizontal-tabs' : ''}`}>
            {model.map((m, index) => {
              return (

                m.link ? 

                <li className={query_name_modified == m.modulename ? 'active-current-tab' : ''}>
                    <a href={m.link}>{m.icon ? <i class={`mvx-font ${m.icon}`}></i> : ''}
                      {m.tablabel}
                    </a>
                </li> 

                :

                <li className={query_name_modified == m.modulename ? 'active-current-tab' : ''} >
                  <Link to={this.props.vendor ? `?page=mvx#&submenu=${m.submenu}&ID=${query_name.get("ID")}&name=${m.modulename}` : `?page=mvx#&submenu=${m.submenu}&name=${m.modulename}`}>
                    {m.icon ? <i class={`mvx-font ${m.icon}`}></i> : ''}
                    {m.tablabel}
                  </Link>
                </li>
              );
            })}
            </ul>
          }
          <div className="mvx-tab-content">
            {this.props.default_vendor_funtion ? <funtion_name.Childparent name={query_name} /> : <funtion_name.Child name={query_name} /> }
          </div>
        </div>
        {no_banner ? '' : <BannerSection />}
      </div>
    </div>
    ;
    return TabUIContent;
  };

  render() {
    return (
      this.renderTab()
    );
  }
}
