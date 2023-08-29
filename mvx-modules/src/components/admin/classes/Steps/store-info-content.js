import React from "react";
import { Component } from "react";
import axios from "axios";
import Box from '@mui/material/Box';
import Grid from '@mui/material/Grid';

class StoreInfoContent extends Component {
  constructor(props) {
    super(props);
    this.state = {
      vendor_storename: '',
      vendor_storeurl: '',
      vendor_description: '',
      vendor_image: '',
      vendor_banner: '',
      storeurl_error : '',

      //required fields
			req_storename_vendor : false,
			req_storeurl_vendor : false,
    };

    this.handleChange = this.handleChange.bind(this);
    this.runimageUploader = this.runimageUploader.bind(this);
    this.GotoAddresseInfo = this.GotoAddresseInfo.bind(this);
    this.BacktoAccountInfo = this.BacktoAccountInfo.bind(this);
  }

  componentDidMount() {
    this.setState({  
      vendor_storename: this.props.Storedata.vendor_storename? this.props.Storedata.vendor_storename : '',
      vendor_storeurl: this.props.Storedata.vendor_storeurl? this.props.Storedata.vendor_storeurl : '',
      vendor_description: this.props.Storedata.vendor_description? this.props.Storedata.vendor_description : '',
      vendor_image: this.props.Storedata.vendor_image? this.props.Storedata.vendor_image : appLocalizer.default_logo,
      vendor_banner: this.props.Storedata.vendor_banner? this.props.Storedata.vendor_banner : appLocalizer.default_banner,
    }); 
  }

  handleChange(event, type) {
    if (event) { 
      if (type == 'storename') {
        this.state.req_storename_vendor = false;
        this.state.req_storeurl_vendor = false;
        var storeurl = event.target.value.replace(/\s/g, '-')
        this.setState({ 
          vendor_storename : event.target.value,
          vendor_storeurl : storeurl,
        });
        axios({
          method: 'post',
          url: `${appLocalizer.apiUrl}/mvx_module/v1/check_store_url`,
          data: {
            storeurl: storeurl,
          },
        }).then((responce) => {
          this.setState({ storeurl_error : responce.data });
        });       
      } else if (type == 'storeurl') { 
        this.state.req_storeurl_vendor = false;
        var storeurl = event.target.value.replace(/\s/g, '-')
        this.setState({ 
          vendor_storeurl : storeurl,
        });
        axios({
          method: 'post',
          url: `${appLocalizer.apiUrl}/mvx_module/v1/check_store_url`,
          data: {
            storeurl: storeurl,
          },
        }).then((responce) => {
          this.setState({ storeurl_error : responce.data });
        });
      } else {
        const name = event.target.name;
        const value = event.target.type === "checkbox" ? event.target.checked : event.target.value;
        this.setState({[name]: value});
      }
    }
  };

  runimageUploader( e, name ) {
    let frame = '';
      let attachment = '';
      // Create a new media frame
      frame = wp.media({
          title: 'Select or Upload Media',
          button: {
              text: 'Use this media',
          },
          multiple: false, // Set to true to allow multiple files to be selected
      });
      const self = this; // copy the reference
      frame.on('select', function () {
          // Get media attachment details from the frame state
          attachment = frame.state().get('selection').first().toJSON();
          if ( name == 'vendor_image' ) {
            self.setState({ vendor_image: attachment.url });
          } else if ( name == 'vendor_banner' ) {
            self.setState({ vendor_banner: attachment.url });
          }
        
      });
    // Finally, open the modal on click
    frame.open();
  };

  ReqStoreName() {
		(this.state.vendor_storename == '') ? this.setState({ req_storename_vendor : true }) : this.setState({ req_storename_vendor : false });	
	}
	ReqStoreURL() {
		(this.state.vendor_storeurl == '') ? this.setState({ req_storeurl_vendor : true }) : this.setState({ req_storeurl_vendor : false });	
	}

  BacktoAccountInfo() {
    this.props.handleNewVendorStoreData(this.state);
    this.props.OnAccountClick();
  }

  GotoAddresseInfo() {
    if (this.state.storeurl_error == false && 
      this.state.vendor_storename != '' &&
      this.state.vendor_storeurl != ''
    ) {
      
      this.props.handleNewVendorStoreData(this.state);
      this.props.OnAddressClick();
    }
    this.ReqStoreName();
		this.ReqStoreURL();  
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
              <input name="vendor_storename" type="text" placeholder="Store Name" value = {this.state.vendor_storename} onChange = {(e) => {this.handleChange(e, 'storename')}}/>
              <span class="storenamereq">{this.state.req_storename_vendor ? 'Required' : '' }</span>
            </Grid>
            <Grid item xs={6}>
              <input name="vendor_storeurl" type="text" placeholder="Store URL" value = {this.state.vendor_storeurl} onChange = {(e) => {this.handleChange(e, 'storeurl')}}/>
              <span class="storeurlstatus">{this.state.storeurl_error}</span>
              <span class="storeurlreq">{this.state.req_storeurl_vendor ? 'Required' : '' }</span>
            </Grid>
            <Grid item xs={12}>
            <textarea name="vendor_description" placeholder="Vendor Details" value = {this.state.vendor_description} onChange = {this.handleChange}/>
            </Grid>
            <Grid item xs={4} >
              <input className='test' type="hidden" name="vendor_image" value={this.state.vendor_image} />
              <img src={this.state.vendor_image} width='100' height='100' />
              <button className="mvx-btn btn-purple" type="button" onClick={(e) => {this.runimageUploader(e, 'vendor_image')}} > Upload </button>
            </Grid>
            <Grid item xs={8}>
              <input className='test' type="hidden" name="vendor_banner" value={this.state.vendor_banner} />
              <img src={this.state.vendor_banner} width='320' height='100'/>
              <button className="mvx-btn btn-purple" type="button" onClick={(e) => {this.runimageUploader(e, 'vendor_banner')}} > upload </button>  
            </Grid>
          </Grid>
        </Box>
        <button onClick={this.BacktoAccountInfo} style={{color: "blue"}}>Back</button>
        <button onClick={this.GotoAddresseInfo} style={{color: "red"}}>Next</button>
      </div>
    );
  }
};

export default StoreInfoContent;
