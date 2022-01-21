import React, { Component } from 'react';
import { render } from 'react-dom';
import axios from 'axios';

import DataTable from 'react-data-table-component';

import FilterComponent from 'react-data-table-component';

import {
  BrowserRouter as Router,
  Link,
  useLocation,
  withRouter,
  useParams,
  NavLink
} from "react-router-dom";

import DynamicForm from "../../../DynamicForm";


class App extends Component {
  constructor(props) {
    super(props);
    this.state = {
      error: null,
      isLoaded: false,
      items: [],
      checkedState: [],
      module_ids: [],
      open_model: false,
      open_model_dynamic: [],
      isLoading: true,
      loading: false,
      module_tabs: [],
      tabIndex: 0,
      query: null,
      firstname: true,
      lastname: '',
      email: '',
      abcarray: [],
      first_toggle: '',
      second_toggle: '',      
      current: {},
      filterText: '',
      resetPaginationToggle: false,
      /*filteredItems: fakeUsers.filter(
        item => item.name && item.name.toLowerCase().includes(filterText.toLowerCase()),
        ),*/

      columns_vendor: [
        {
            name: <h1>Name</h1>,
            selector: row => row.name,
            sortable: true,
        },
        {
            name: <h1>Email</h1>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.email}}></div>,
            sortable: true,
        },
        {
            name: <h1>Registered</h1>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.registered}}></div>,
            sortable: true,
        },
        {
            name: <h1>Products</h1>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.products}}></div>,
            sortable: true,
        },
        {
            name: <h1>Status</h1>,
            selector: row => <div dangerouslySetInnerHTML={{__html: row.status}}></div>,
            sortable: true,
        },
      ],

      datavendor: [],

    };

    this.handleChange = this.handleChange.bind(this);

    this.subHeaderComponentMemo = this.subHeaderComponentMemo.bind(this);

    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);

    this.useQuery = this.useQuery.bind(this);

    this.useQuery = this.useQuery.bind(this);
  }

  useQuery() {
    return new URLSearchParams(useLocation().search);
  }

  QueryParamsDemo(e) {
    let queryt = this.useQuery();
    var vendor_id = 45;
    var namename = 'dddddddddd';
    return (
      <div className="container">
      {!queryt.get("ID") ?
        <DataTable
            columns={this.state.columns_vendor}
            data={this.state.datavendor}
            selectableRows
            onSelectedRowsChange={this.handleChange}
            pagination
        />
        : 

        
        <div className="general-tab-area">

          <ul className="mvx-general-tabs-list">
              <li>
                <i class="mvx-font ico-store-icon"></i>
                <Link to={`?page=marketplace-manager-settings&ID=${vendor_id}&name=${namename}`} >sdfsdf</Link>
              </li>

              <li>
                <i class="mvx-font ico-store-icon"></i>
                <Link to={`?page=marketplace-manager-settings&ID=54&name=rtet`} >sdfsdf</Link>
              </li>

              <li>
                <i class="mvx-font ico-store-icon"></i>
                <Link to={`?page=marketplace-manager-settings&ID=99&name=cvvcvv`} >sdfsdf</Link>
              </li>
          </ul>

          <div className="tabcontentclass">
            <this.Child name={queryt.get("name")} />
          </div>

        </div>

        }

      </div>
    );
  }

  Child({ name }) {
  return (
    <div>
    {name}
    </div>
  );
}


  handleChange(e) {
    console.log(e);
  }

  subHeaderComponentMemo(e) {
      React.useMemo(() => {
      

      return (
      <FilterComponent filterText={this.state.filterText} />
      );
      }, [this.state.filterText, this.state.resetPaginationToggle]);
  }

  componentDidMount() {
    axios({
      url: `${appLocalizer.apiUrl}/mvx_module/v1/all_vendors`
    })
    .then(response => {
      console.log(this.state.datavendor);
      this.setState({
        datavendor: response.data,
      });
    })
    
  }


  render() {
    console.log(this.state.datavendor)
    return (

    <Router>
      <this.QueryParamsDemo />
    </Router>
    );
  }
}
export default App;