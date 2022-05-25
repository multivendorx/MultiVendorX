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
    let query_name = this.props.query_name;
    let funtion_name = this.props.funtion_name;
    let horizontally = this.props.horizontally;
    let no_banner = this.props.no_banner;
    
    let TabUI = model.map((m, index) => {
      return (
        query_name == m.modulename ?
          <div className="mvx-tab-description-start">
            <div className="mvx-tab-name">{m.tablabel}</div>
            <p>{m.description}</p>
          </div>
        : ''
      );
    });

    let TabUIContent =
    <div className={`mvx-general-wrapper mvx-${query_name}`}> 
    <HeaderSection />
    <div className="mvx-tab-wrapper">
      {this.props.tab_description && this.props.tab_description == 'no' ? '' : TabUI}
      <ul className={`mvx-current-tab-lists ${horizontally ? 'mvx-horizontal-tabs' : ''}`}>
      {model.map((m, index) => {
        return (
          <li className={query_name == m.modulename ? 'active-current-tab' : ''} >
            <Link to={`?page=mvx#&submenu=${m.submenu}&name=${m.modulename}`}>
              {m.icon ? <i class={`mvx-font ${m.icon}`}></i> : ''}
              {m.tablabel}
            </Link>
          </li>
        );
      })}
      </ul>
      <div className="mvx-tab-content">
        <funtion_name.Child name={query_name} />
      </div>
    </div>
    {no_banner ? '' : <BannerSection />}
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
