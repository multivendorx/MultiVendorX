import React from "react";
import { Component } from "react";
import Select from 'react-select';
import axios from "axios";
import Box from '@mui/material/Box';
import Grid from '@mui/material/Grid';

class AddressInfoContent extends Component {
  constructor(props) {
    super(props);
    this.state = {
      vendor_address1: '',
      vendor_address2: '',
      vendor_country: '',
      vendor_state_list: [],
      vendor_state: '',
      vendor_city:'',
      vendor_zipcode:''
    };

    this.handleChange = this.handleChange.bind(this);
    this.handleSelectChange = this.handleSelectChange.bind(this);
    this.GotoSubmitInfo = this.GotoSubmitInfo.bind(this);
    this.BacktoStoreInfo = this.BacktoStoreInfo.bind(this);
  }

  componentDidMount() {
    this.setState({ 
      vendor_address1: this.props.Addressdata.vendor_address1 ? this.props.Addressdata.vendor_address1 : '',
      vendor_address2: this.props.Addressdata.vendor_address2 ? this.props.Addressdata.vendor_address2 : '',
      vendor_country: this.props.Addressdata.vendor_country ? this.props.Addressdata.vendor_country : '',
      vendor_state: this.props.Addressdata.vendor_state ? this.props.Addressdata.vendor_state : '',
      vendor_city: this.props.Addressdata.vendor_city ? this.props.Addressdata.vendor_city : '',
      vendor_zipcode: this.props.Addressdata.vendor_zipcode ? this.props.Addressdata.vendor_zipcode : '',
      vendor_state_list : this.props.Addressdata.vendor_state_list ? this.props.Addressdata.vendor_state_list : [],
    }); 
  }

  handleSelectChange(e, name, from_type) {
    if (name == 'vendor_country') {
      this.setState({
        [name]: e.value,
      });
      const country_list_array = [];
      const statefromcountrycode = JSON.parse(
        appLocalizer.countries.replace(/&quot;/g, '"')
      )[e.value];
      for (const key_country in statefromcountrycode) {
        country_list_array.push({
          label: statefromcountrycode[key_country],
          value: key_country,
        });
      }
      this.setState({
        vendor_state_list: country_list_array,
      });
      console.log(this.state);
    } else {
      this.setState({
        [name]: e.value
      });
    }
  };
  
  handleChange(event) {
    if (event) {
      const name = event.target.name;
      const value = event.target.type === "checkbox" ? event.target.checked : event.target.value;
      this.setState({[name]: value});
    }
  };

  BacktoStoreInfo() {
    console.log(this.state);
    this.props.handleNewVendorAddressData(this.state);
    this.props.OnStoreClick();
  }

  GotoSubmitInfo() { 
    // console.log(this.props.Accountdata);
		// console.log(this.props.Storedata);
		// console.log(this.state);
    axios({
			method: 'post',
			url: `${appLocalizer.apiUrl}/mvx_module/v1/add_new_vendor_model`,
			data: {
				account_info: this.props.Accountdata,
				store_info: this.props.Storedata,
				address_info: this.state
			},
		}).then((response) => {
			if (responce.data.redirect_link) {
				window.location.href = response.data.redirect_link;
			}
		});
   
  }

  render() {
    return (
      <div>
        <Box
        sx={{
          width: 600
        }}
        >
          <Grid container spacing={2}>
            <Grid item xs={6}>
              <input name="vendor_address1" type="text" placeholder="Address 1" autoFocus value ={this.state.vendor_address1} onChange = {this.handleChange}/>
            </Grid>
            <Grid item xs={6}>
              <input name="vendor_address2" type="text" placeholder="Address 2" value ={this.state.vendor_address2} onChange = {this.handleChange}/>
            </Grid>
            <Grid item xs={6}>
            <Select name="vendor_country" 
              value={appLocalizer.country_list.filter(({value}) => value === this.state.vendor_country)}
              options={appLocalizer.country_list}
              onChange = {(e) => {this.handleSelectChange(e,'vendor_country', 'select')}} 
              placeholder="Country"
            />
            </Grid>
            <Grid item xs={6}>
            <Select name="vendor_state"
              value={this.state.vendor_state_list.filter(({value}) => value === this.state.vendor_state)}
              options={this.state.vendor_state_list}
              onChange = {(e) => {this.handleSelectChange(e,'vendor_state', 'select')}} 
              placeholder="State" 
            />
            </Grid>
            <Grid item xs={6}>
              <input name="vendor_city" type="text" placeholder="City" value ={this.state.vendor_city} onChange = {this.handleChange}/>
            </Grid>
            <Grid item xs={6}>
              <input name="vendor_zipcode" type="text" placeholder="Zip code" value ={this.state.vendor_zipcode} onChange = {this.handleChange}/>
            </Grid>
          </Grid>
        </Box>
        <button onClick={this.BacktoStoreInfo} style={{color: "blue"}}>Back</button>
        <button onClick={(e) => this.GotoSubmitInfo(e)} style={{color: "green"}}>Register</button>
      </div>
    );
  }
}

export default AddressInfoContent;
