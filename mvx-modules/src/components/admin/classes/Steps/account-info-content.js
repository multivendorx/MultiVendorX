import React from "react";
import { Component } from "react";
import axios from "axios";
import Box from '@mui/material/Box';
import Grid from '@mui/material/Grid';

class AccountInfoContent extends Component {
  constructor(props) {
    super(props);
    this.state = {
      vendor_fname: '',
      vendor_lname: '',
      vendor_username: '',
      vendor_nickname: '',
      vendor_phone: '',
      vendor_email: '',
      vendor_password: '',
      vendor_send_email: '',
      vendor_profile_image : appLocalizer.default_logo,
      email_error: '',
      username_error: '',

      //required fields
			req_email_vendor : false,
			req_uname_vendor : false,
      req_password_vendor : false,
			req_nickname_vendor : false,
    };

    this.handleChange = this.handleChange.bind(this);
    this.runimageUploader = this.runimageUploader.bind(this);
    this.emailValidation = this.emailValidation.bind(this);
    this.GotoStoreInfo = this.GotoStoreInfo.bind(this);
    this.randompassword = this.randompassword.bind(this);
  }

  componentDidMount() {
    this.setState({  
      vendor_fname: this.props.Accountdata.vendor_fname ? this.props.Accountdata.vendor_fname : '',
      vendor_lname: this.props.Accountdata.vendor_lname ? this.props.Accountdata.vendor_lname : '',
      vendor_username: this.props.Accountdata.vendor_username ? this.props.Accountdata.vendor_username : '',
      vendor_nickname: this.props.Accountdata.vendor_nickname ? this.props.Accountdata.vendor_nickname : '',
      vendor_phone: this.props.Accountdata.vendor_phone ? this.props.Accountdata.vendor_phone : '',
      vendor_email: this.props.Accountdata.vendor_email ? this.props.Accountdata.vendor_email : '',
      vendor_password: this.props.Accountdata.vendor_password ? this.props.Accountdata.vendor_password : '',
      vendor_send_email: this.props.Accountdata.vendor_send_email ? this.props.Accountdata.vendor_send_email : '',
      vendor_profile_image : this.props.Accountdata.vendor_profile_image ? this.props.Accountdata.vendor_profile_image : appLocalizer.default_logo,
    }); 
  }

  emailValidation(email){
    const regex = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
    if(!email || regex.test(email) === false){
        return false;
    }
    return true;
}

  handleChange(event, type) {
    if (event) {
      if (type == 'email') {
        this.state.req_email_vendor = false;
        this.setState({ vendor_email : event.target.value });       
        if (this.emailValidation(event.target.value)) {
          axios({
            method: 'post',
            url: `${appLocalizer.apiUrl}/mvx_module/v1/check_email_user`,
            data: {
              email: event.target.value,
            },
          }).then((responce) => {
            this.setState({ email_error : responce.data });
          });
        } else {
          this.setState({ email_error: "Email is not valid" });
        }
      } else if (type == 'username') {
        this.state.req_uname_vendor = false;
        this.setState({ vendor_username : event.target.value });
        axios({
          method: 'post',
          url: `${appLocalizer.apiUrl}/mvx_module/v1/check_uname_user`,
          data: {
            username: event.target.value,
          },
        }).then((responce) => {
          this.setState({  username_error : responce.data });
        }); 
      } else {
        if (type == 'nickname') {
          this.state.req_nickname_vendor = false;
        } else if (type == 'password') {
          this.state.req_password_vendor = false;
        }
        const name = event.target.name;
        const value = event.target.type === "checkbox" ? event.target.checked : event.target.value;
        this.setState({[name]: value});
      }
    }
  };

  runimageUploader() {
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
          self.setState({ vendor_profile_image: attachment.url });       
      });
    // Finally, open the modal on click
    frame.open();
  };
  randompassword() {
    var generatePassword = (
        length = 20,
        wishlist = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz~!@-#$'
      ) =>
        Array.from(crypto.getRandomValues(new Uint32Array(length))).map((x) => wishlist[x % wishlist.length]).join('');
      
        this.setState({ vendor_password: generatePassword() });
        this.state.req_password_vendor = false;
  }

  ReqEmail() {
		(this.state.vendor_email == '') ? this.setState({ req_email_vendor : true }) : this.setState({ req_email_vendor : false });	
	}
	ReqUname() {
		(this.state.vendor_username == '') ? this.setState({ req_uname_vendor : true }) : this.setState({ req_uname_vendor : false });	
	}
	ReqNickname() {
		(this.state.vendor_nickname == '') ? this.setState({ req_nickname_vendor : true }) : this.setState({ req_nickname_vendor : false });	
	}
  ReqPassword() {
		(this.state.vendor_password == '') ? this.setState({ req_password_vendor : true }) : this.setState({ req_password_vendor : false });	
	}

  GotoStoreInfo(){
    if (this.state.email_error == false && 
      this.state.username_error == false && 
      this.state.vendor_nickname != '' &&
      this.state.vendor_email != '' &&
      this.state.vendor_username != '' &&
      this.state.vendor_password != ''
    ) {
      this.props.handleNewVendorAccontData(this.state);
      this.props.OnStoreClick();
    }
    this.ReqEmail();
		this.ReqUname();
		this.ReqNickname();
    this.ReqPassword();  
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
                <input name="vendor_fname" type="text" placeholder="First Name" value = {this.state.vendor_fname} autoFocus onChange = {this.handleChange}/>
              </Grid>
              <Grid item xs={6}>
                <input name="vendor_lname" type="text" placeholder="Last Name" value = {this.state.vendor_lname} onChange = {this.handleChange}/>
              </Grid>
              <Grid item xs={6}>
                <input name="vendor_nickname" type="text" placeholder="Nick Name" value = {this.state.vendor_nickname} onChange = {(e) => {this.handleChange(e, 'nickname')}}/>
                <span class="nicknamereq">{this.state.req_nickname_vendor ? 'Required' : '' }</span>
              </Grid>
              <Grid item xs={6}>
                <input name="vendor_phone" type="number" placeholder="Phone" value = {this.state.vendor_phone} onChange = {this.handleChange}/>
              </Grid>
              <Grid item xs={12}>
                <input name="vendor_email" type="email" placeholder="Email" value = {this.state.vendor_email} onChange ={(e) => {this.handleChange(e, 'email')}}/>
                <span class="emailstatus">{this.state.email_error}</span>
                <span class="emailreq">{this.state.req_email_vendor ? 'Required' : '' }</span>
              </Grid>
              <Grid item xs={6}>
                <input name="vendor_username" type="text" placeholder="Username" value = {this.state.vendor_username} onChange = {(e) => {this.handleChange(e, 'username')}}/>
                <span class="usernamestatus">{this.state.username_error}</span>
                <span class="usernamereq">{this.state.req_uname_vendor ? 'Required' : '' }</span>
              </Grid>
              <Grid item xs={6}>
                <input name="vendor_password" type="text" placeholder="Password" value = {this.state.vendor_password} onChange = {this.handleChange}/>
                <span class="passwordreq">{this.state.req_password_vendor ? 'Required' : '' }</span>
              </Grid>
              <Grid item xs={6} >
                <input className='test' type="hidden" name="vendor_profile_image" value={this.state.vendor_profile_image} />
                <img src={this.state.vendor_profile_image} width='100' height='100' />
                <button className="mvx-btn btn-purple" type="button" onClick={this.runimageUploader} > Upload </button>
              </Grid>
              <Grid item xs={6} >
                <div className="mvx-btn-container">
                  <div className="pass_btn">
                    <button
                    className="mvx-btn btn-purple"
                    type="button"
                    onClick={this.randompassword}
                    >Genarate Password</button>
                  </div>
                  <div className="email_btn">
                    <label for="input-id">Send the vendor an email about their account.</label>
                    <div className="mvx-toggle-checkbox-content">
											<input className='mvx-toggle-checkbox' type='checkbox' name="vendor_send_email" checked={this.state.vendor_send_email} onChange = {this.handleChange}/>
										</div>
                  </div>
                </div>
              </Grid>
            </Grid>
          </Box>
          <button onClick={this.GotoStoreInfo} style={{color: "red"}}>Next</button>
        </div>
     );
  }
}
export default AccountInfoContent;