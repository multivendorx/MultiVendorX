import React from "react";
import ReactDOM from "react-dom";
import Select from 'react-select';
import axios from 'axios';

export default class DynamicForm extends React.Component {
  state = {};
  constructor(props) {
    super(props);
    this.state = { 
       from_loading: false,
       statedrop: [],
       errordisplay: ''
       
     };

    this.runUploader = this.runUploader.bind(this);
    this.handlenestedAddClick = this.handlenestedAddClick.bind(this);
    this.handlenestedchildAddClick = this.handlenestedchildAddClick.bind(this);

    this.removenestedchildOption = this.removenestedchildOption.bind(this);
    this.removenestedField = this.removenestedField.bind(this);
    
    this.onnestedChange = this.onnestedChange.bind(this);

    //this.onChange = this.onChange.bind(this);
    
  }

  onnestedChange(e, target, index, filedsdetails, nestedchild, childindex, selectarray, m) {

    let itemsnested = m['database_value'];
    
    var country_list_array = new Array();

    if (nestedchild == 'childnested') {
      if (filedsdetails.type == 'checkbox') {
        itemsnested[index].nested_datas[childindex][filedsdetails.key] = e.target.checked;
      } else if(filedsdetails.type == 'select') {
        itemsnested[index].nested_datas[childindex][filedsdetails.key] = selectarray[e.index];
      } else if (filedsdetails.type == 'state') {
        itemsnested[index].nested_datas[childindex][filedsdetails.key] = selectarray[e.index];
      } else {
        itemsnested[index].nested_datas[childindex][filedsdetails.key] = e.target.value;
      }
    } else {
      if (filedsdetails.type == 'checkbox') {
        itemsnested[index][filedsdetails.key] = e.target.checked;
      } else if(filedsdetails.type == 'select') {
        itemsnested[index][filedsdetails.key] = selectarray[e.index];
      } else if (filedsdetails.type == 'country') {
        
        itemsnested[index][filedsdetails.key] = selectarray[e.index];
        var statefromcountrycode = JSON.parse(appLocalizer.countries.replace( /&quot;/g, '"' ))[e.key];
       
        for (const keysssssss in statefromcountrycode) {
          country_list_array.push({label:keysssssss, value:statefromcountrycode[keysssssss]});
        }

        m['child_options'][0]['options'] = country_list_array;

      } else {
        itemsnested[index][filedsdetails.key] = e.target.value;
      }
    }
    
    /*this.setState({
      //[m['database_value']]: itemsnested
    });*/
  
    if(this.props.submitbutton && this.props.submitbutton == 'false') {
      setTimeout(() => {
        this.onSubmit('');
      }, 10)
    }



    //console.log(m);
    //console.log(JSON.parse(appLocalizer.countries.replace( /&quot;/g, '"' )));

    this.setState(
      {
        [target]: itemsnested
      },
      () => {}
    );
    // if(this.props.submitbutton && this.props.submitbutton == 'false') {
    //   this.onSubmit('');
    // }
  }

  removenestedchildOption(e, indexparent, index, m) {
    m['database_value'][indexparent].nested_datas.splice(index, 1);
    this.setState({
      [m['database_value']]: m['database_value']
    });
  }

  handlenestedchildAddClick(e, m, indexop) {

    var child_nested_array = {};
    if(m['type'] == 'nested') {
      m['child_options'].map((keyn, indexn) => {
        if (keyn['type'] == 'checkbox') {
          child_nested_array[keyn['key']] = false;
        } else {
          child_nested_array[keyn['key']] = '';
        }
      });
    }

    var count = m['database_value'][indexop].nested_datas.length + 1;
    m['database_value'][indexop].nested_datas.push(child_nested_array);

    //console.log(m['database_value']);
    //return false;

    this.setState({
      [m['database_value']]: m['database_value']
    });
  }

  removenestedField(e, index, m) {
    m['database_value'].splice(index, 1);
    this.setState({
      [m['database_value']]: m['database_value']
    });
  }

  handlenestedAddClick(event, m) {
    //parent_options
    var parent_nested_array = {};
    var child_nested_array = new Array({});
    if(m['type'] == 'nested') {
      m['parent_options'].map((keyn, indexn) => {
        if (keyn['type'] == 'checkbox') {
          parent_nested_array[keyn['key']] = false;
        } else {
          parent_nested_array[keyn['key']] = '';
        }
        parent_nested_array['nested_datas'] = child_nested_array;
      });
    }

/*    console.log(parent_nested_array);
    console.log({value: 'option' + count, label: 'Option ' + count, selected: false, nested_datas: [{}]});
    return false;*/
    //var count = this.state.nestedarray.options.length + 1;

    m['database_value'].push(parent_nested_array);

    this.setState({
      [m['database_value']]: m['database_value']
    });

    /*this.setState({ 
      nestedarray: true 
    });*/

    //this.state.nestedarray
    //console.log(this.state.nestedarray.options.length);
  }

  runUploader(event, target, index) {
    let frame = '';
    var attachment = '';
    frame = frame + target;
    // Create a new media frame
    frame = wp.media({
        title: 'Select or Upload Media Of Your Chosen Persuasion',
        button: {
            text: 'Use this media',
        },
        multiple: false, // Set to true to allow multiple files to be selected
    })
    
    var self = this; // copy the reference
    frame.on( 'select', function() {
      // Get media attachment details from the frame state
      attachment = frame.state().get('selection').first().toJSON();
      self.setState({
        [target]: attachment.url
      });
    });
    // Finally, open the modal on click
    frame.open()
  }

  static getDerivedStateFromProps(nextProps, prevState) {
    //console.log("gds:p:s", nextProps.defaultValues, prevState);

    let derivedState = {};
    
    if (
      nextProps.defaultValues &&
      nextProps.defaultValues.id !== prevState.id
    ) {
      //   Object.keys(prevState).forEach(k => {
      //     derivedState[k] = "";
      //   });
      return {
        ...nextProps.defaultValues
      };
    }

    //console.log("no state change");
    return null;
  }

  onSubmit = e => {

    // block to refresh pages
    let prop_submitbutton = this.props.submitbutton && this.props.submitbutton == 'false' ? '' : 'true';
    if (prop_submitbutton) {
      e.preventDefault();
    }

    this.setState({ from_loading: true });
    this.props.model.map(key => {
      if (key['type'] == 'wpeditor') {
        this.state[key['key']] = wp.editor.getContent(key['key']);
      }
    });
    
    axios({
      method: this.props.method,
      url: appLocalizer.apiUrl + '/' + this.props.url,
      data: {
        model: this.state,
        modelname: this.props.modelname,
        vendor_id: this.props.vendor_id ? this.props.vendor_id : ''
      }
    })
    .then( ( res ) => {
      //console.log(res.data.ddddddddddd);

      this.setState({ from_loading: false });

      this.setState({ errordisplay: res.data.error });


      setTimeout(() => {
        this.setState({ errordisplay: '' });
      }, 2000)

      if (res.data.redirect_link) {
        window.location.href = res.data.redirect_link;
      }

    } );
  };

  componentDidMount() {

    this.props.model.map(m => {
      this.setState({
        [m['key']]: m['database_value']
      });

      if(m['type'] == 'nested') {

      }


      
      if(m['type'] == 'wpeditor') {
        // add wp editor from textarea
        wp.editor.initialize(m['key'], {
          mediaButtons: true,
          tinymce: {
            wpautop  : true,
            theme    : 'modern',
            skin     : 'lightgray',
            language : 'en',
            formats  : {
              alignleft  : [
              { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'left' } },
              { selector: 'img,table,dl.wp-caption', classes: 'alignleft' }
              ],
              aligncenter: [
              { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'center' } },
              { selector: 'img,table,dl.wp-caption', classes: 'aligncenter' }
              ],
              alignright : [
              { selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles: { textAlign: 'right' } },
              { selector: 'img,table,dl.wp-caption', classes: 'alignright' }
              ],
              strikethrough: { inline: 'del' }
            },
            relative_urls       : true,
            remove_script_host  : true,
            convert_urls        : true,
            browser_spellcheck  : true,
            fix_list_elements   : true,
            entities            : '38,amp,60,lt,62,gt',
            entity_encoding     : 'raw',
            keep_styles         : true,
            paste_webkit_styles : 'font-weight font-style color',
            preview_styles      : 'font-family font-size font-weight font-style text-decoration text-transform',
            tabfocus_elements   : ':prev,:next',
            plugins    : 'charmap,hr,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpview',
            resize     : 'vertical',
            menubar    : true,
            indent     : true,
            toolbar1   : 'bold,italic,strikethrough,bullist,numlist,blockquote,hr,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
            toolbar2   : 'formatselect,underline,alignjustify,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
            toolbar3   : '',
            toolbar4   : '',
            body_class : 'id post-type-post post-status-publish post-format-standard',
            wpeditimage_disable_captions: true,
            wpeditimage_html5_captions  : true

          },
          quicktags: true
        });
      }

     /* var parent_nested_array = new Array({});
      var child_nested_array = new Array({});
      if(m['type'] == 'nested') {
        
        m['child_options'].map((keyn, indexn) => {
          child_nested_array[0][keyn['id']] = '';
        });

        m['parent_options'].map((keyn, indexn) => {
          if (keyn['type'] == 'checkbox') {
            parent_nested_array[0][keyn['id']] = false;
          } else {
            parent_nested_array[0][keyn['id']] = '';
          }
          parent_nested_array[0]['nested_datas'] = child_nested_array;
        });

      }*/

      /*let ggggggggg = this.state.nestedarray.options;
      ggggggggg = parent_nested_array;
      this.setState({
        ggggggggg
      });
      console.log(ggggggggg);*/

    });
  }

  onChange = (e, key, type = "single", from_type = '', array_values = []) => {
    if (type === "single") {

      if (from_type === "select") {
        this.setState(
          {
            [key]: array_values[e.index]
          },
          () => {}
        );
        
      } else if ( from_type && from_type === "country" || from_type === "state") {

        this.setState(
          {
            [key]: array_values[e.index]
          },
          () => {}
        );
        var country_list_array = [];
        var statefromcountrycode = JSON.parse(appLocalizer.countries.replace( /&quot;/g, '"' ))[e.value];
        for (const key_country in statefromcountrycode) {
          country_list_array.push({label:key_country, value:statefromcountrycode[key_country]});
        }
        this.setState(
          {
            statedrop: country_list_array
          }
        );

      } else if(from_type === "multi-select") {
          this.setState(
            {
              [key]: e
            },
            () => {}
          );
      } else {
        this.setState(
          {
            [key]: e.target.value
          },
          () => {}
        );
      }
      
    } else {
      // Array of values (e.g. checkbox): TODO: Optimization needed.
      let found = this.state[key]
        ? this.state[key].find(d => d === e.target.value)
        : false;

      if (found) {
        let data = this.state[key].filter(d => {
          return d !== found;
        });
        this.setState({
          [key]: data
        });
      } else {
        // console.log("found", key, this.state[key]);
        // this.setState({
        //   [key]: [e.target.value, ...this.state[key]]
        // });
        let others = this.state[key] ? [...this.state[key]] : [];
        this.setState({
          [key]: [e.target.value, ...others]
        });
      }
    }
    
    if(this.props.submitbutton && this.props.submitbutton == 'false') {
      setTimeout(() => {
        this.onSubmit('');
      }, 10)
    }

  };

  renderForm = () => {

    //console.log(this.state.nestedarray.options);

    let model = this.props.model;
    let defaultValues = this.props.defaultValues;
    let formUI = model.map( (m, index) => {
      let key = m.key;
      let type = m.type || "text";
      let props = m.props || {};
      let name = m.name;
      let value = m.value;
      let placeholder = m.placeholder;
      let limit = m.limit;
      let selected = m.selected;

      let target = key;
      value = this.state[target] || "";

      if (m.restricted_page && m.restricted_page == this.props.location) {
        return false;
      }

      // If no array key found
      if (!m.key) {return false;}

      if(m.depend && this.state[m.depend] && this.state[m.depend].value != m.dependvalue) {
        return false;
      }


      let input = '';

      /*let input = (
        <input
          {...props}
          className="mvx-setting-form-input"
          type={type}
          key={key}
          name={name}
          value={value}
          onChange={e => {
            this.onChange(e, target);
          }}
        />
      );*/

      if (type == "text" || "url" || "password" || "email" || "number" ) {
         input = (
          <div className="mvx-settings-basic-input-class">
            <input
              {...props}
              className="mvx-setting-form-input"
              type={type}
              key={key}
              id={m.id}
              placeholder={placeholder}
              name={name}
              value={value}
              onChange={e => {
                this.onChange(e, target);
              }}
            />
            {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
          </div>
      );
      }

      if (type == "label") {
        input = (
          <div>
           <label dangerouslySetInnerHTML={{ __html: m.valuename }}></label>
           <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p>
          </div>
         );
       }

       if (type == "section") {
        input = (
          <div className="mvx-setting-section-divider">&nbsp;
          </div>
         );
       }


      if (type == "blocktext") {
        input = (
          <div className="mvx-blocktext-class">
            {m.blocktext ? <p className="mvx-settings-metabox-description-code" dangerouslySetInnerHTML={{ __html: m.blocktext }}></p> : ''}
          </div>
       );
     }

    if (type == "table") {
        var inputlabels = m.label_options.map(ol => {
          return (
            <th className="mvx-settings-th-wrap">{ol}</th>
          );
        });  

        input =  m.options.map(o => {
          return (
            <tr className="mvx-settings-tr-wrap">
              <td className="mvx-settings-td-wrap"><p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: o.variable }}></p></td>
              <td className="mvx-settings-td-wrap">{o.description}</td>
            </tr>
            
          );
        }) ;
        input = <div className="mvx-settings-mvx-form-table">
          <table className="mvx-settings-table-wrap">
            <tr className="mvx-settings-tr-wrap">
              {inputlabels}
            </tr>
            {input}
            
          </table>
        </div>;
     }


      if (type == "normalfile") {
       input = (
          <input
            {...props}
            className="mvx-setting-form-input"
            type="file"
            key={key}
            name={name}
            value={value}
            onChange={e => {
              this.onChange(e, target);
            }}
          />
        );
      }

      if (type == "recaptcha") {
        var recaptcha_type = m.recaptchatype;
        var sitekey = m.sitekey;
        var secretkey = m.secretkey;
        var script_url = (recaptcha_type == 'v3') ? 'https://www.google.com/recaptcha/api.js?render='+ sitekey : 'https://www.google.com/recaptcha/api.js';
        
        if (recaptcha_type == 'v3') {
          grecaptcha.ready(function () {
            grecaptcha.execute(sitekey, { action: 'mvx_vendor_registration' }).then(function (token) {
              var recaptchaResponse = document.getElementById('recaptchav3Response');
              recaptchaResponse.value = token;
            });
          });
        }

       input = (
          <div className="mvx-regi-form-row">
            {m.script}
            <input type="hidden" name={`${m.key}-value`} value="Verified" />
            <input type="hidden" name={`${m.key}-label`} value={m.label} />
            <input type="hidden" name={`${m.key}-type`} value="recaptcha" />
            
            {recaptcha_type == 'v3' ? 
              <div>
                <input type="hidden" name="recaptchav3Response" id="recaptchav3Response" />
                <input type="hidden" name="recaptchav3_sitekey" value={sitekey} />
                <input type="hidden" name="recaptchav3_secretkey" value={secretkey} />
              </div>
            : '' }
            <input type="hidden" name="g-recaptchatype" value={recaptcha_type} />
          </div>
        );
      }

      if (type == "radio") {
        input = m.options.map(o => {
          let checked = o.value == value;
          return (
            <React.Fragment key={"fr" + o.key}>
              <input
                {...props}
                className="mvx-setting-form-input"
                type={type}
                key={o.key}
                name={o.name}
                checked={checked}
                value={o.value}
                onChange={e => {
                  this.onChange(e, o.name);
                }}
              />
              <label key={"ll" + o.key}>{o.label}</label>
            </React.Fragment>
          );
        });
        input = <div className="mvx-settings-mvx-form-group-radio">
          {input}
          {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
        </div>;
      }

      if (type == "toggle_rectangle") {
        input = m.options.map(o => {
          let checked = o.value == value;
          return (
            <React.Fragment key={"fr" + o.key}>
              <li>
              <input
                {...props}
                className="mvx-setting-form-input"
                id={`mvx-toggle-rectangle-${o.key}`}
                type="radio"
                key={o.key}
                name={o.name}
                checked={checked}
                value={o.value}
                onChange={e => {
                  this.onChange(e, o.name);
                }}
              />
              <label for={`mvx-toggle-rectangle-${o.key}`} key={"ll" + o.key}>{o.label}</label>
              </li>
            </React.Fragment>
          );
        });
        input = <div className="mvx-settings-mvx-form-group-radio">
          <div className="mvx-toggle-rectangle-merge">
            <ul>
            {input}
            </ul>
          </div>
          {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
        </div>;
      }



      if (type == "radio_select") {
        input = m.options.map(o => {
          let checked = o.value == value;
          return (
            <React.Fragment key={"fr" + o.key}>
            <div className={`mvx-radioselect-class ${checked? 'radio-select-active' : ''}`}>
              
              <input
                {...props}
                className="mvx-setting-form-input"
                type="radio"
                id={`mvx-radio-select-under-${o.key}`}
                key={o.key}
                name={o.name}
                checked={checked}
                value={o.value}
                onChange={e => {
                  this.onChange(e, o.name);
                }}
              />
              <label className="mvx-radio-select-under-label-class" for={`mvx-radio-select-under-${o.key}`}>{o.label}</label>
              <img src={o.color} alt={o.label} className="mvx-section-img-fluid"/>
              
              </div>
            </React.Fragment>
          );
        });
        input = <div className="mvx-form-group-radio-select">
        {input}
        {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
        </div>;
      }

      if (type == "radio_color") {
        input = m.options.map(o => {
          let checked = o.value == value;
          return (
            <React.Fragment key={"fr" + o.key}>
            <div className={`mvx-settings-radio-color ${checked? 'radio-color-active' : ''}`}>
              <div className="mvx-merge-radio-color-input-label">
                <input
                  {...props}
                  className="mvx-setting-form-input"
                  type="radio"
                  key={o.key}
                  name={o.name}
                  checked={checked}
                  value={o.value}
                  onChange={e => {
                    this.onChange(e, o.name);
                  }}
                />
                <label key={"ll" + o.key}>{o.label}</label>
              </div>

              <p className="color-palette">
                {
                  o.color.map((color1, indexc) => 
                  (
                    <div style={{ backgroundColor: color1 }}>&nbsp;</div>
                  )
                  )
                }  
              </p>
                  



              
              </div>
            </React.Fragment>
          );
        });
        input = <div className="mvx-form-group-radio-color">
        {input}
        {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
        </div>;
      }

      if (type == "select") {
        let options_data = [];
        let defaultselect = [];
        input = m.options.map((o, index) => {
          if(o.selected) {
            defaultselect[index] = {value:o.value, label:o.label, index:index };
          }
          options_data[index] = {value:o.value, label:o.label, index:index };
        });
        input = (
          <div>
          <Select className={key}
            value={value ? value : ''}
            options={options_data}
            onChange={e => {
              this.onChange(e, m.key, 'single', type, options_data);
            }}
          >
          </Select>
          {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
          </div>
        );
      }

      if (type == "multi-select") {
        let multiarray = [];
        input = m.options.map((o, index) => {
          multiarray[index] = {value:o.value, label:o.label, index:index };
        });

        input = (
        <div className="mvx-settings-from-multi-select">
          <Select className={key}
            value={value}
            isMulti
            options={multiarray}
            onChange={e => {
              this.onChange(e, m.key, 'single', type, multiarray);
            }}
          >
          </Select>
          {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
        </div>
        );
      }

      if (type == "textarea") {
         input = (
         <div className="mvxsetting-from-textarea">
          <textarea 
            {...props}
            className={m.class ? m.class : 'form-input'}
            key={key}
            maxlength={limit}
            placeholder={placeholder}
            name={name}
            value={value}
            rows="4"
            cols="50"
            onChange={e => {
              this.onChange(e, target);
            }}
          />
          {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
          </div>
      );
      }


      if (type == "separator") {
         input = (
            <div class="mvx_regi_form_box">
              <div class="clearboth"></div>
              <h3 class="reg_header2"></h3>
            </div>
        );
      }

       if (type == "file") {
         input = (

         <div className = "mvx-setting-file-uploader-class">
         <input
          {...props}
          className= {`${key} mvx-form-input`}
          type="hidden"
          key={key}
          name={name}
          value={value}
          onChange={e => {
            this.onChange(e, target);
          }}
        />
          <img src={value ? value : appLocalizer.default_logo}  width={m.width} height={m.height}/>
          <button {...props} className="mvx-upload-button-class" type='button' onClick={e => {
              this.runUploader(e, target, index);
            }} >
              Open Uploader
          </button>
          {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
        </div>
      );
      }

      if (type == "wpeditor") {
         input = (
         <div className={m.class}>
         <textarea 
          {...props}
          id={key}
          className="mvx-setting-form-input"
          key={key}
          name={name}
          value={value}
          rows="4"
          cols="50"
          onChange={e => {
            this.onChange(e, target);
          }}
        />
        {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
        </div>
      );
      }


      if (type == "country") {
         let countryselectdrop = [];
        input = m.options.map((selectdata, index) => {
            countryselectdrop[index] = {value:selectdata.value, label:selectdata.label, index:index, key:selectdata.lebel };
        });

        input = (
        <div className="mvx-country-choice-class">
          <Select className={key}
            value={value}
            options={countryselectdrop}
            onChange={e => {
              this.onChange(e, m.key, 'single', type, countryselectdrop);
            }}
          >
          </Select>
          {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
        </div>
        );
      }

      if (type == "state") {
         let stateselectdrop = [];
        input = this.state.statedrop.length > 0 ? this.state.statedrop.map((selectdata, index) => {
            stateselectdrop[index] = {value:selectdata.value, label:selectdata.value, index:index, key:selectdata.label };
        }) : '';

        input = (
        <div className="mvx-state-choice-class">
          <Select className={key}
            value={value}
            options={stateselectdrop}
            onChange={e => {
              this.onChange(e, m.key, 'single', type, stateselectdrop);
            }}
          >
          </Select>
          {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
        </div>
        );
      }


      if (type == "nested") {
        var carsnew = [];
        var parentseectoption = [];
        var statedata = [];
        let ggg;
        input = (
          <div className="mvx-multi-nested-class">
              {m.database_value.map((o, indexop) => 
                <div className="mvx-boarder-parent">
                {m.parent_options.map(op =>
                  <div className="mvx-boarder-parent-loop">

                  <label className="mvx-setting-form-label">
                  <p dangerouslySetInnerHTML={{ __html: op.label }}></p>
                  </label>

                  {op.type == 'text' ? 
                    <input
                    {...props}
                    className="mvx-setting-form-input"
                    type={op.type}
                    value={o[op.key]}
                    onChange={e => {
                      this.onnestedChange(e, target, indexop, op, '', '', '', m);
                    }}
                    />
                  : '' }

                  {op.type == 'checkbox' ? 
                    <input
                    {...props}
                    className="mvx-setting-form-input"
                    type={op.type}
                    value="true"
                    checked={o[op.key]}
                    onChange={e => {
                      this.onnestedChange(e, target, indexop, op, '', '', '', m);
                    }}
                    />
                  : '' }


                  {
                    op.type == 'select' ? 
                    op.options.map((selectdata, index) => {
                      parentseectoption[index] = {value:selectdata.value, label:selectdata.label, index:index };
                    }) : ''
                  ,
                    op.type == 'select' ?
                    <Select className="mvx-setting-form-input"
                    value={o[op.key]}
                    options={parentseectoption}
                    onChange={e => {
                      this.onnestedChange(e, target, indexop, op, '', '', parentseectoption, m);
                    }}
                    >
                    </Select>
                    : ''
                  }


                  {
                    op.type == 'country' ? 
                    op.options.map((selectdata, index) => {
                      parentseectoption[index] = {value:selectdata.value, label:selectdata.value, index:index, key:selectdata.lebel };
                    }) : ''
                  ,
                    op.type == 'country' ?
                    <Select className="mvx-setting-form-input"
                    value={o[op.key]}
                    options={parentseectoption}
                    onChange={e => {
                      this.onnestedChange(e, target, indexop, op, '', '', parentseectoption, m);
                    }}
                    >
                    </Select>
                    : ''
                  }


                  </div>
                )}
                <div className="mvx-boarder-nested-child-start">

                  {
                    o.nested_datas ? o.nested_datas.map((opn, indexchildop) =>
                    <div>
                    { m.child_options.map((opnjj, indexcop) => 
                      <div className="mvx-boarder-nested-child"> 
                        <div className="mvx-boarder-nested-child-loop">
                        
                        <label className="mvx-setting-form-label">
                        {opnjj.label} :
                        </label>

                        {opnjj.type == 'text' ?
                        <input
                        {...props}
                        className="mvx-setting-form-input"
                        type={opnjj.type}
                        value={opn[opnjj.key]}
                        onChange={e => {
                          this.onnestedChange(e, target, indexop, opnjj, 'childnested', indexchildop, '', m);
                        }}
                        />
                        : '' }


                        {opnjj.type == 'checkbox' ? 
                        <input
                        {...props}
                        className="mvx-setting-form-input"
                        type={opnjj.type}
                        value="true"
                        checked={opn[opnjj.key]}
                        onChange={e => {
                          this.onnestedChange(e, target, indexop, opnjj, 'childnested', indexchildop, '', m);
                        }}
                        />
                        : '' }


                        {
                          opnjj.type == 'select' ? 
                          opnjj.options.map((okkkk, index) => {
                            carsnew[index] = {value:okkkk.value, label:okkkk.value, index:index };
                          }) : ''
                        }

                        {
                          opnjj.type == 'select' ?
                          <Select className="mvx-setting-form-input"
                          value={opn[opnjj.key]}
                          options={carsnew}
                          onChange={e => {
                            this.onnestedChange(e, target, indexop, opnjj, 'childnested', indexchildop, carsnew, m);
                          }}
                          >
                          </Select>
                          : ''
                        }



                        {
                          opnjj.type == 'state' ? 
                          opnjj.options.map((okkkk, index) => {
                            statedata[index] = {value:okkkk.value, label:okkkk.value, index:index };
                          }) : ''
                        ,
                          opnjj.type == 'state' ?
                          <Select className="mvx-setting-form-input"
                          value={opn[opnjj.key]}
                          options={statedata}
                          onChange={e => {
                            this.onnestedChange(e, target, indexop, opnjj, 'childnested', indexchildop, statedata, m);
                          }}
                          >
                          </Select>
                          : ''
                        }


                        </div>
                        </div>
                    )}

                    {m.child_options.length > 0 ? 
                    <div className="horizontal-class">
                      <p className="button-controls"><a onClick={(e) => this.removenestedchildOption(e, indexop, indexchildop, m)} className="button-secondary">-</a></p>
                      {o.nested_datas.length -1 == indexchildop ? <p className="button-controls"><a onClick={(e) => this.handlenestedchildAddClick(e, m, indexop)} className="button-primary">+</a></p> : ''}
                    </div>
                    : ''
                    }

                    </div>
                    ) : ''
                  }

                  {/* add nested */}
                  
                  </div>

                  <div className="horizontal-class">
                    <p onClick={(e) => this.removenestedField(e, indexop, m)} className="button-controls button-secondary">-</p>
                    {m.database_value.length -1 == indexop ? <p className="button-controls"><a onClick={(e) => this.handlenestedAddClick(e, m)} className="button-primary">+</a></p> : ''}
                  </div>

                </div>
              )}
          {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
          </div>
        );

      }

      if (type == "checkbox") {
        input = (
        <div className={m.parent_class ? "mvx-checkbox-list-side-by-side" : '' }>
        {
        m.options.map(o => {
          //let checked = o.value == value;
          let checked = false;
          if (value && value.length > 0) {
            checked = value.indexOf(o.value) > -1 ? true : false;
          }
          return (
            <div className={m.parent_class ? m.parent_class : '' }>
            <React.Fragment key={"cfr" + o.key}>
              <div className="mvx-toggle-checkbox-content">
                <input
                  {...props}
                  className={m.class}
                  type={type}
                  id={`mvx-toggle-switch-${o.key}`}
                  key={o.key}
                  name={o.name}
                  checked={checked}
                  value={o.value}
                  onChange={e => {
                    this.onChange(e, m.key, "multiple");
                  }}
                />
                <label for={`mvx-toggle-switch-${o.key}`}></label>
              </div>
              <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: o.label }}></p>
              {o.hints ? <span class="dashicons dashicons-info"><div className="mvx-hover-tooltip">{o.hints}</div></span> : ''}

            </React.Fragment>
              
              </div>
          );
        })
      }
        {m.desc ? <p className="mvx-settings-metabox-description" dangerouslySetInnerHTML={{ __html: m.desc }}></p> : ''}
        </div>
        )
      }
      return (
        <div>
        {m.type == 'section' ? input :
        <div key={"g" + key} className="form-group">
            <label className="mvx-settings-form-label" key={"l" + key} htmlFor={key}>
            <p dangerouslySetInnerHTML={{ __html: m.label }}></p>
            </label>
            
          <div className="mvx-settings-input-content">
            {input}
          </div>
        </div>
        }
        </div>
      );
    });
    return formUI;
  };

  render() {
    let title = this.props.title || "Dynamic Form";
    let prop_submitbutton = this.props.submitbutton && this.props.submitbutton == 'false' ? '' : 'true';
    return (
      <div className={this.props.className}>
        {this.state.errordisplay ? <div className="mvx-notic-display-title">{this.state.errordisplay}</div> : '' }
        
        <form
          className="dynamic-form"
          onSubmit={e => {
            this.onSubmit(e);
          }}
        >
          {this.renderForm()}
          {prop_submitbutton ? 
          <div className="form-actions">
            <button className="button-secondary" disabled={this.state.from_loading}  type="submit">{this.state.from_loading && (
              <i
              className="dashicons dashicons-update"
              style={{ marginRight: "5px" }}
              />
              )}
              {this.state.from_loading && <span>Saving..</span>}
              {!this.state.from_loading && <span>{this.props.submit_title ? this.props.submit_title : 'Save'}</span>}</button>
          </div>
          : ''}
        </form>

      </div>
    );
  }
}
