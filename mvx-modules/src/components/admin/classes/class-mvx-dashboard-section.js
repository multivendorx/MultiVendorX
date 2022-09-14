import React, { Component } from 'react';
import HeaderSection from './class-mvx-page-header';
class MVX_Dashboard extends Component {
    constructor(props) {
        super(props);
        this.state = {
            money_back_show_more_lifetime: false,
            money_back_show_more_yearly: false,
            money_back_show_more_monthly: false,
            money_back_show_more_compared : false,
        };
    }
    componentDidMount() {
        var $ = jQuery;
        var cs = 1;
        var cm = 4;
        $(document).on("click", ".p-prev", function (event) {
          event.preventDefault();
          if (cs > 1) {
            $('.mvx-dashboard-slider').hide();
            cs--;
            $('.mvx-dashboard-slider:nth-child(' + cs + ')').show();
            $('.message-banner-sliding span').html(cs + ' of 4');
          }
        });
        $(document).on("click", ".p-next", function (event) {
          event.preventDefault();
          if (cs < cm) {
            $('.mvx-dashboard-slider').hide();
            cs++;
            $('.mvx-dashboard-slider:nth-child(' + cs + ')').show();
            $('.message-banner-sliding span').html(cs + ' of 4');
          }
        });
    }
    render() {
        return (
            <div className="mvx-general-wrapper mvx-dashboard">
                <HeaderSection />
                <div className="mvx-sub-container mvx-container">
                    <div className="mvx-left-container">
                        <div className="mvx-dashboard-top-heading">
                            {appLocalizer.dashboard_string.dashboard1}
                        </div>
                        <div className="mvx-slider-content-main-wrapper">
                            <div className="mvx-dashboard-slider">
                                <div className='mvx-dashboard-slider-cmp-wrap'>
                                <div className="mvx-dashboard-top-icon">
                                    <span>{appLocalizer.dashboard_string.dashboard2}</span>
                                </div>
                                <div className="mvx-pro-txt">
                                    <h3>{appLocalizer.dashboard_string.dashboard3}</h3>
                                    <p>
                                        {appLocalizer.dashboard_string.dashboard4}
                                    </p>
                                    <a href={appLocalizer.dashboard_string.dashboard88} className="mvx-btn btn-red">
                                    {appLocalizer.dashboard_string.dashboard5}
                            </a>
                                </div>
                                </div>
                            </div>

                            <div className="mvx-dashboard-slider mvx-flex-content">
                            <div className="mvx-dashboard-slider-cmp-wrap">

                                <div className="mvx-dashboard-top-icon">
                                    <span>{appLocalizer.dashboard_string.dashboard2}</span>
                                </div>
                                <div className="mvx-pro-txt">
                                    <h3>
                                        {appLocalizer.dashboard_string.dashboard6}
                                    </h3>
                                    <p>
                                        {appLocalizer.dashboard_string.dashboard7}
                                    </p>
                                    <a href={appLocalizer.dashboard_string.dashboard88} className="mvx-btn btn-red">
                                        {appLocalizer.dashboard_string.dashboard5}
                                    </a>
                                </div>
                            </div>
                            </div>

                            <div className="mvx-dashboard-slider mvx-flex-content">
                            <div className="mvx-dashboard-slider-cmp-wrap">
                                <div className="mvx-dashboard-top-icon">
                                    <span>{appLocalizer.dashboard_string.dashboard2}</span>
                                </div>
                                <div className="mvx-pro-txt">
                                    <h3>
                                        {appLocalizer.dashboard_string.dashboard8}
                                    </h3>
                                    <p>
                                        {appLocalizer.dashboard_string.dashboard9}
                                    </p>
                                    <a href={appLocalizer.dashboard_string.dashboard88} className="mvx-btn btn-red">
                                        {appLocalizer.dashboard_string.dashboard5}
                                    </a>
                                </div>
                                </div>
                            </div>

                            <div className="mvx-dashboard-slider mvx-flex-content">
                            <div className="mvx-dashboard-slider-cmp-wrap">
                                <div className="mvx-dashboard-top-icon">
                                    <span>{appLocalizer.dashboard_string.dashboard2}</span>
                                </div>
                                <div className="mvx-pro-txt">
                                    <h3>
                                        {appLocalizer.dashboard_string.dashboard10}
                                    </h3>
                                    <p>
                                        {appLocalizer.dashboard_string.dashboard11}
                                    </p>
                                    <a href={appLocalizer.dashboard_string.dashboard88} className="mvx-btn btn-red">    {appLocalizer.dashboard_string.dashboard5}
                                    </a>
                                </div>
                                </div>
                            </div>

                            <div className="message-banner-sliding">
                                <a href="#" className="p-prev">
                                    <i className="mvx-font icon-left-arrow" />
                                </a>
                                <span>{appLocalizer.dashboard_string.dashboard12}</span>
                                <a href="#" className="p-next">
                                    <i className="mvx-font icon-right-arrow" />
                                </a>
                            </div>
                        </div>
                        <div className="mvx-setup-documentation">
                            <div className="mvx-setup-marketing-white-box">
                                <h2 className="mvx-block-title">
                                    {appLocalizer.dashboard_string.dashboard13}
                                </h2>
                                <ul className="mvx-table-ul">
                                    <li className="mvx-align-items-center hover-border-box">
                                        <div className="mvx-allign-li-txt">
                                            <span>
                                                <i className="mvx-font icon-chart-line" />
                                            </span>{' '}
                                            {appLocalizer.dashboard_string.dashboard14}
                                        </div>
                                        <div className="li-action">
                                            <a
                                                href="#"
                                                className="chckbx-purple"
                                            >
                                                <i className="mvx-font icon-yes" />
                                            </a>
                                        </div>
                                    </li>
                                    <li className="mvx-align-items-center hover-border-box">
                                        <div className="mvx-allign-li-txt">
                                            <span>
                                                <i className="mvx-font icon-chart-line" />
                                            </span>{' '}
                                            {appLocalizer.dashboard_string.dashboard15}
                                        </div>
                                        <div className="li-action">
                                            <a
                                                href="#"
                                                className="chckbx-purple"
                                            >
                                                <i className="mvx-font icon-yes" />
                                            </a>
                                        </div>
                                    </li>
                                    <li className="mvx-align-items-center hover-border-box">
                                        <div className="mvx-allign-li-txt">
                                            <span>
                                                <i className="mvx-font icon-chart-line" />
                                            </span>{' '}
                                            {appLocalizer.dashboard_string.dashboard16}
                                        </div>
                                        <div className="li-action">
                                            <a
                                                href="#"
                                                className="chckbx-purple"
                                            >
                                                <i className="mvx-font icon-yes" />
                                            </a>
                                        </div>
                                    </li>
                                    <li className="mvx-align-items-center hover-border-box">
                                        <div className="mvx-allign-li-txt">
                                            <span>
                                                <i className="mvx-font icon-chart-line" />
                                            </span>{' '}
                                            {appLocalizer.dashboard_string.dashboard17}
                                        </div>
                                        <div className="li-action">
                                            <a
                                                href="#"
                                                className="chckbx-purple"
                                            >
                                                <i className="mvx-font icon-yes" />
                                            </a>
                                        </div>
                                    </li>
                                    <li className="mvx-align-items-center hover-border-box">
                                        <div className="mvx-allign-li-txt">
                                            <span>
                                                <i className="mvx-font icon-chart-line" />
                                            </span>{' '}
                                            {appLocalizer.dashboard_string.dashboard18}
                                        </div>
                                        <div className="li-action">
                                            <a
                                                href="#"
                                                className="chckbx-purple"
                                            >
                                                <i className="mvx-font icon-yes" />
                                            </a>
                                        </div>
                                    </li>
                                    <li className="mvx-align-items-center hover-border-box">
                                        <div className="mvx-allign-li-txt">
                                            <span>
                                                <i className="mvx-font icon-chart-line" />
                                            </span>{' '}
                                            {appLocalizer.dashboard_string.dashboard19}
                                        </div>
                                        <div className="li-action">
                                            <a
                                                href="#"
                                                className="chckbx-purple"
                                            >
                                                <i className="mvx-font icon-yes" />
                                            </a>
                                        </div>
                                    </li>
                                    <li className="mvx-align-items-center hover-border-box">
                                        <div className="mvx-allign-li-txt">
                                            <span>
                                                <i className="mvx-font icon-chart-line" />
                                            </span>{' '}
                                            {appLocalizer.dashboard_string.dashboard20}
                                        </div>
                                        <div className="li-action">
                                            <a
                                                href="#"
                                                className="chckbx-purple"
                                            >
                                                <i className="mvx-font icon-yes" />
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div className="mvx-documentation-quick-link-wrapper">
                                <div class="mvx-documentation-quick-link">
                                    <div className="mvx-documentation-support-forum left-forum">
                                        <figure>
                                            <i className="mvx-font icon-documentation-forum" />
                                        </figure>
                                        <figcaption>
                                            <h2>{appLocalizer.dashboard_string.dashboard21}</h2>
                                            <div>
                                                <p>
                                                    {appLocalizer.dashboard_string.dashboard22}
                                                </p>
                                            </div>
                                            <a href="https://multivendorx.com/doc/">
                                                {appLocalizer.dashboard_string.dashboard23}{' '}
                                                <span className="mvx-font icon-link-right-arrow" />
                                            </a>
                                        </figcaption>
                                    </div>

                                    <div className="mvx-documentation-support-forum right-forum">
                                        <figure>
                                            <i className="mvx-font icon-support-forum" />
                                        </figure>
                                        <figcaption>
                                            <h2>{appLocalizer.dashboard_string.dashboard24}</h2>
                                            <div>
                                                <p>
                                                    {appLocalizer.dashboard_string.dashboard25}
                                                </p>
                                            </div>
                                            <a href="https://wc-marketplace.com/support-forum">
                                                {appLocalizer.dashboard_string.dashboard26}{' '}
                                                <span className="mvx-font icon-link-right-arrow" />
                                            </a>
                                        </figcaption>
                                    </div>

                                    <div className="mvx-quick-link-sec">
                                        <h3 className="block-title">Quick Link</h3>
                                        <ul className="row-link">
                                            <li>
                                                <a href={`?page=mvx#&submenu=vendor&name=add-new`}>
                                                    <figure>
                                                        <i className="mvx-font icon-vendor-personal" />
                                                    </figure>
                                                    {appLocalizer.dashboard_string.dashboard27}
                                                </a>
                                            </li>
                                            <li>
                                                <a href={`?page=mvx#&submenu=commission`}>
                                                    <figure>
                                                        <i className="mvx-font icon-vendor-personal" />
                                                    </figure>
                                                    {appLocalizer.dashboard_string.dashboard28}
                                                </a>
                                            </li>
                                            <li>
                                                <a href={`post-new.php?post_type=product`}>
                                                    <figure>
                                                        <i className="mvx-font icon-vendor-personal" />
                                                    </figure>
                                                    {appLocalizer.dashboard_string.dashboard29}
                                                </a>
                                            </li>
                                            <li>
                                                <a href={`?page=mvx#&submenu=payment`}>
                                                    <figure>
                                                        <i className="mvx-font icon-vendor-personal" />
                                                    </figure>
                                                    {appLocalizer.dashboard_string.dashboard30}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="mvx-upgrade-pro-section">
                            <div className="mvx-pro-title">
                                <div className="mvx-dashboard-top-icon">
                                    <span>{appLocalizer.dashboard_string.dashboard2}</span>
                                </div>
                            </div>
                            <h1>{appLocalizer.dashboard_string.dashboard31}</h1>
                            <p>
                                {appLocalizer.dashboard_string.dashboard32}
                            </p>
                            <a href="#" className="mvx-btn btn-red">
                                {appLocalizer.dashboard_string.dashboard3}
                            </a>
                        </div>

                        <div className="mvx-text-center">
                            <h1>
                                {appLocalizer.dashboard_string.dashboard34}
                            </h1>
                            <ul className="mvx-compare-table-holder">
                                <li className="mvx-compare-table-row">
                                    <ul>
                                        <li>{appLocalizer.dashboard_string.dashboard85}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard35}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard36}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard37}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard38}</li>
                                        <li>{appLocalizer.dashboard_string.dashboard39}</li>
                                        <li>{appLocalizer.dashboard_string.dashboard40}</li>
                                        <li>{appLocalizer.dashboard_string.dashboard41}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard6}</li>
                                        <li>{appLocalizer.dashboard_string.dashboard43}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard44}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard45}</li>
                                         <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard46}</li>
                                         <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard47}</li>
                                        <li>{appLocalizer.dashboard_string.dashboard48}</li>
                                        
                                        {this.state.money_back_show_more_compared ?
                                            <>
                                              
                                                <li>{appLocalizer.dashboard_string.dashboard49}</li>
                                                <li>{appLocalizer.dashboard_string.dashboard50}</li>
                                                <li>{appLocalizer.dashboard_string.dashboard51}</li>
                                                <li>{appLocalizer.dashboard_string.dashboard52}</li>
                                                <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard8}</li>
                                                <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard54}</li>
                                                <li>{appLocalizer.dashboard_string.dashboard55}</li>
                                                <li>{appLocalizer.dashboard_string.dashboard56}</li>
                                                <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard57}</li>
                                                <li>{appLocalizer.dashboard_string.dashboard58}</li>
                                            </>
                                            : ''
                                        }
                                         
                                    </ul>
                                    <div className='show-responsive-money-btn-wrapper'>
                                            {this.state.money_back_show_more_compared ?

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_compared: false
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        {/* <i className="mvx-font icon-up-round-arrow" /> */}
                                                        <i className="mvx-font icon-eye-see-more" />

                                                    </span>{' '}
                                                    {/* {appLocalizer.dashboard_string.dashboard87} */}
                                                    {appLocalizer.dashboard_string.dashboard74}

                                                </a>

                                                :

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_compared: true
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        {/* <i className="mvx-font icon-down-round-arrow" /> */}
                                                        <i className="mvx-font icon-eye-see-more" />

                                                    </span>{' '}
                                                    {/* {appLocalizer.dashboard_string.dashboard86} */}
                                                    {appLocalizer.dashboard_string.dashboard75}
                                                
                                                </a>
                                            }
                                        </div>
                                </li>

                           

                                <li className="mvx-compare-table-row mvx-recomend">
                                   
                                    <ul>
                                        <li>Pro</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard35}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard36}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard37}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard38}</li>
                                        <li  className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard39}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard40}</li>
                                        <li  className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard41}</li>
                                        <li  className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard6}</li>
                                        <li  className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard43}</li>
                                        <li  className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard44}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard45}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard46}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard47}</li>
                                        <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard48}</li>
                                        
                                        {this.state.money_back_show_more_compared ?
                                            <>
                                               
                                                <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard49}</li>
                                                <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard50}</li>
                                                <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard51}</li>
                                                <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard52}</li>
                                                <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard8}</li>
                                                <li className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard54}</li>
                                                <li  className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard55}</li>
                                                <li  className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard56}</li>
                                                <li  className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard57}</li>
                                                <li  className='mvx-cmpr-active'>{appLocalizer.dashboard_string.dashboard58}</li>
                                            </>
                                            : ''
                                        }
                                        
                                    </ul>
                                    <div className='show-responsive-money-btn-wrapper'>
                                            {this.state.money_back_show_more_compared ?

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_compared: false
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        {/* <i className="mvx-font icon-up-round-arrow" /> */}
                                                        <i className="mvx-font icon-eye-see-more" />

                                                    </span>{' '}
                                                    {/* {appLocalizer.dashboard_string.dashboard87} */}
                                                    {appLocalizer.dashboard_string.dashboard74}

                                                </a>

                                                :

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_compared: true
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        {/* <i className="mvx-font icon-down-round-arrow" /> */}
                                                        <i className="mvx-font icon-eye-see-more" />

                                                    </span>{' '}
                                                    {/* {appLocalizer.dashboard_string.dashboard86} */}
                                                    {appLocalizer.dashboard_string.dashboard75}
                                                
                                                </a>
                                            }
                                        </div>
                                </li>
                               
                                        
                            </ul>
                            <div className='show-btn-wrapper'>
                                            {this.state.money_back_show_more_compared ?

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_compared: false
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        {/* <i className="mvx-font icon-up-round-arrow" /> */}
                                                        <i className="mvx-font icon-eye-see-more" />

                                                    </span>{' '}
                                                    {/* {appLocalizer.dashboard_string.dashboard87} */}
                                                    {appLocalizer.dashboard_string.dashboard74}

                                                </a>

                                                :

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_compared: true
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        {/* <i className="mvx-font icon-down-round-arrow" /> */}
                                                        <i className="mvx-font icon-eye-see-more" />

                                                    </span>{' '}
                                                    {/* {appLocalizer.dashboard_string.dashboard86} */}
                                                    {appLocalizer.dashboard_string.dashboard75}
                                                
                                                </a>
                                            }
                                        </div>
                        </div>

                        <div className="mvx-text-center">
                            <div className='mvx-money-header-wrap'>
                            <h1>
                                <span className="mvx-gra-txt">{appLocalizer.dashboard_string.dashboard63}</span>{' '}
                                {appLocalizer.dashboard_string.dashboard64}
                            </h1> 
                            <p>
                                {appLocalizer.dashboard_string.dashboard65}
                            </p>
                            </div>
                            <ul className="mvx-money-table-holder">
                                <li className='mvx-money-table-coloumn' >
                                    <ul>
                                        <li>{appLocalizer.dashboard_string.dashboard66}</li>
                                        <li>
                                            <div className="m-price">
                                                <h1>$399 </h1> &nbsp;<p><s>/$599</s> </p>
                                            </div>
                                        </li>
                                        <li className="mvx-btn btn-border">
                                            <a
                                                href="#"
                                            >
                                                {appLocalizer.dashboard_string.dashboard69}
                                            </a>
                                        </li>
                                        <div className='mvx-price-component'>
                                            <span>        
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard70}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard71}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-form-radio" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard72}{' '}

                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard73}
                                                </p>
                                                        
                                            </span>
                                            {this.state.money_back_show_more_yearly ?
                                                <span>    
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard70}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard71}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-form-radio" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard72}{' '}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard73}
                                                    </p>
                                                </span>
                                            : ''}

                                           
                                        </div>
                                       
                                    </ul>
                                    <div className='show-responsive-money-btn-wrapper'>
                                             {this.state.money_back_show_more_yearly ?

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_yearly: false
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        <i className="mvx-font icon-eye-see-more" />
                                                    </span>{' '}
                                                    {appLocalizer.dashboard_string.dashboard74}
                                                </a>

                                                :

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_yearly: true
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        <i className="mvx-font icon-eye-see-more" />
                                                    </span>{' '}
                                                    {appLocalizer.dashboard_string.dashboard75}
                                                </a>
                                                }
                                        </div>
                                </li>
                               
                                <li className='mvx-spr-sever-rcmnd mvx-money-table-coloumn'>
                                    <span className="mvx-recommend-tag">
                                        {appLocalizer.dashboard_string.dashboard76}
                                    </span>
                                    <ul>
                                        <li>{appLocalizer.dashboard_string.dashboard77}</li>
                                        <li>
                                            <div className="m-price">
                                                <h1>$499 </h1> &nbsp;<p><s>/$599</s> </p>
                                            </div>
                                        </li>
                                        <li className="mvx-btn btn-red">
                                            <a
                                                href="#"
                                            >
                                                {appLocalizer.dashboard_string.dashboard69}
                                            </a>
                                        </li>
                                        <div className='mvx-price-component'>
                                            <span>    
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard70}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard71}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-form-radio" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard72}{' '}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard73}
                                                </p>
                                            </span>

                                            {this.state.money_back_show_more_yearly ?
                                                <span>    
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard70}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard71}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-form-radio" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard72}{' '}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard73}
                                                    </p>
                                                </span>
                                            : ''}
                                           
                                        </div>
                                    
                                    </ul>
                                    <div className='show-responsive-money-btn-wrapper'>
                                             {this.state.money_back_show_more_yearly ?

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_yearly: false
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        <i className="mvx-font icon-eye-see-more" />
                                                    </span>{' '}
                                                    {appLocalizer.dashboard_string.dashboard74}
                                                </a>

                                                :

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_yearly: true
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        <i className="mvx-font icon-eye-see-more" />
                                                    </span>{' '}
                                                    {appLocalizer.dashboard_string.dashboard75}
                                                </a>
                                                }
                                        </div>
                                </li>
                            

                                <li  className='mvx-money-table-coloumn'>
                                    <ul>
                                        <li>{appLocalizer.dashboard_string.dashboard80}</li>
                                        <li>
                                            <div className="m-price">
                                                <h1>$299 </h1>&nbsp;<p><s>/$599</s> </p>
                                            </div>
                                        </li>
                                        <li className="mvx-btn btn-border">
                                            <a
                                                href="#"
                                            >
                                                {appLocalizer.dashboard_string.dashboard69}
                                            </a>
                                        </li>
                                        <div className='mvx-price-component'>
                                            
                                            <span>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard70}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard71}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-form-radio" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard72}{' '}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    {appLocalizer.dashboard_string.dashboard73}
                                                </p>
                                            </span>

                                            {this.state.money_back_show_more_yearly ?
                                                <span>    
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard70}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard71}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-form-radio" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard72}{' '}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        {appLocalizer.dashboard_string.dashboard73}
                                                    </p>
                                                </span>
                                            : ''}

                                        </div>
                                       
                                    </ul>
                                    <div className='show-responsive-money-btn-wrapper'>
                                             {this.state.money_back_show_more_yearly ?

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_yearly: false
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        <i className="mvx-font icon-eye-see-more" />
                                                    </span>{' '}
                                                    {appLocalizer.dashboard_string.dashboard74}
                                                </a>

                                                :

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_yearly: true
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        <i className="mvx-font icon-eye-see-more" />
                                                    </span>{' '}
                                                    {appLocalizer.dashboard_string.dashboard75}
                                                </a>
                                                }
                                        </div>
                                </li>
                              
                            </ul>
                                           
                                         <div className='show-money-btn-wrapper'>
                                             {this.state.money_back_show_more_yearly ?

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_yearly: false
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        <i className="mvx-font icon-eye-see-more" />
                                                    </span>{' '}
                                                    {appLocalizer.dashboard_string.dashboard74}
                                                </a>

                                                :

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_yearly: true
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        <i className="mvx-font icon-eye-see-more" />
                                                    </span>{' '}
                                                    {appLocalizer.dashboard_string.dashboard75}
                                                </a>
                                                }
                                        </div>

                        </div>

                        <div className="mvx-upgrade-pro-section pro-bg">
                            <div className="mvx-dashboard-top-icon">
                                <span>{appLocalizer.dashboard_string.dashboard2}</span>
                            </div>
                            <h1>{appLocalizer.dashboard_string.dashboard83}</h1>
                            <p>
                                {appLocalizer.dashboard_string.dashboard84}
                            </p>
                            <a href="#" className="mvx-btn btn-red">
                                {appLocalizer.dashboard_string.dashboard3}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}
export default MVX_Dashboard;