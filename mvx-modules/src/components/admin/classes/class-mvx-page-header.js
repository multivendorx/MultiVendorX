import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';

import {
  BrowserRouter as Router
} from "react-router-dom";


class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      fetch_all_settings_for_searching: []
    };
    
    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);
    this.handleOnChange = this.handleOnChange.bind(this);
  }

  handleOnChange(event) {
    if (event.target.value) {
      axios.get(
      `${appLocalizer.apiUrl}/mvx_module/v1/fetch_all_settings_for_searching`, { params: { value: event.target.value } 
      })
      .then(response => {

        this.setState({
          fetch_all_settings_for_searching: response.data,
        });
      })
    } else {
      this.setState({
        fetch_all_settings_for_searching: [],
      });
    }

  }

  QueryParamsDemo() {
    return (
      <div className="mvx-module-section-nav">
        <div className="mvx-module-nav-left-section">
          <div className="mvx-module-section-nav-child-data">
            <img src={appLocalizer.mvx_logo} alt="WC Marketplace" className="mvx-section-img-fluid"/>
          </div>
          <div className="mvx-module-section-nav-child-data">
            {appLocalizer.marketplace_text}
          </div>
        </div>
        <div className="mvx-module-nav-right-section">
          <div className="mvx-header-search-section"> 
            <label><i className='mvx-font icon-search'></i></label>
            <input type="text" placeholder="Search Options" name="search" onChange={(e) => this.handleOnChange(e)}/>

            {console.log(this.state.fetch_all_settings_for_searching.length)}

            { this.state.fetch_all_settings_for_searching.length > 0 ? 

              <div className="mvx-search-content">
                {this.state.fetch_all_settings_for_searching.map((data, index) => (
                  <div>

                    <a href={data.link}><div>{data.label}</div>
                    <div><p dangerouslySetInnerHTML={{ __html: data.desc }}></p></div></a>

                    
                  </div>
                ))}
              </div> 

              : '' }
          </div>
          <a href={appLocalizer.knowledgebase} title={appLocalizer.knowledgebase_title} target="_blank" className="mvx-module-section-nav-child-data"><i class='mvx-font icon-knowledge-topbar'></i></a>
        </div>
      </div>
    );
  }

  render() {
    return (
      <div>
          <Router>
            <this.QueryParamsDemo />
          </Router>
      </div>
    );
  }
}
export default App;