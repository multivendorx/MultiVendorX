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
                            Welcome to MultiVendorX
                        </div>
                        <div className="mvx-slider-content-main-wrapper">
                            <div className="mvx-dashboard-slider">
                                <div className='mvx-dashboard-slider-cmp-wrap'>
                                <div className="mvx-dashboard-top-icon">
                                    <span>Pro</span>
                                </div>
                                <div className="mvx-pro-txt">
                                    <h3>Upgrade to Pro</h3>
                                    <p>
                                        Activate your Pro License and gain access to better marketplace features 
                                    </p>
                                    <a href="#" className="mvx-btn btn-red">
                                    Active License
                            </a>
                                </div>
                                </div>
                            </div>

                            <div className="mvx-dashboard-slider mvx-flex-content">
                            <div className="mvx-dashboard-slider-cmp-wrap">

                                <div className="mvx-dashboard-top-icon">
                                    <span>Pro</span>
                                </div>
                                <div className="mvx-pro-txt">
                                    <h3>
                                        Dynamic Shipping
                                    </h3>
                                    <p>
                                        Grow your business accross the globe with multiple shipping options
                                    </p>
                                    <a href="#" className="mvx-btn btn-red">
                                        Active License
                                    </a>
                                </div>
                            </div>
                            </div>

                            <div className="mvx-dashboard-slider mvx-flex-content">
                            <div className="mvx-dashboard-slider-cmp-wrap">
                                <div className="mvx-dashboard-top-icon">
                                    <span>Pro</span>
                                </div>
                                <div className="mvx-pro-txt">
                                    <h3>
                                        Live Chat
                                    </h3>
                                    <p>
                                        Build better relations with customers through live chat
                                    </p>
                                    <a href="#" className="mvx-btn btn-red">
                                        Active License
                                    </a>
                                </div>
                                </div>
                            </div>

                            <div className="mvx-dashboard-slider mvx-flex-content">
                            <div className="mvx-dashboard-slider-cmp-wrap">
                                <div className="mvx-dashboard-top-icon">
                                    <span>Pro</span>
                                </div>
                                <div className="mvx-pro-txt">
                                    <h3>
                                        Marketplace Membership
                                    </h3>
                                    <p>
                                        Invite unlimited vendors to your marketplace by creating appealing membership packages
                                    </p>
                                    <a href="#" className="mvx-btn btn-red">    Active License
                                    </a>
                                </div>
                                </div>
                            </div>

                            <div className="message-banner-sliding">
                                <a href="#" className="p-prev">
                                    <i className="mvx-font icon-left-arrow" />
                                </a>
                                <span>1 of 4</span>
                                <a href="#" className="p-next">
                                    <i className="mvx-font icon-right-arrow" />
                                </a>
                            </div>
                        </div>
                        <div className="mvx-setup-documentation">
                            <div className="mvx-setup-marketing-white-box">
                                <h2 className="mvx-block-title">
                                    This is what you get
                                </h2>
                                <ul className="mvx-table-ul">
                                    <li className="mvx-align-items-center hover-border-box">
                                        <div className="mvx-allign-li-txt">
                                            <span>
                                                <i className="mvx-font icon-chart-line" />
                                            </span>{' '}
                                            Set up seller Registration Form Fields
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
                                            Set up payments
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
                                            Set up taxes
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
                                            Set up shipping
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
                                            Set up commissions
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
                                            Set up product capabilities
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
                                            Set up allowed product types
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
                                            Set up marketing tools
                                            <p>2 minutes</p>
                                        </div>
                                        <div className="li-action">
                                            <a
                                                href="#"
                                                className="chckbx-border"
                                            >
                                                Setup
                                            </a>
                                        </div>
                                    </li>
                                    <li className="mvx-align-items-center hover-border-box">
                                        <div className="mvx-allign-li-txt">
                                            <span>
                                                <i className="mvx-font icon-chart-line" />
                                            </span>{' '}
                                            Set up marketing tools
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
                                            <h2>Documentation Forum</h2>
                                            <div>
                                                <p>
                                                    Learn more about marketplace features and settings by accessing our documentation forum.
                                                </p>
                                            </div>
                                            <a href="#">
                                                Visit Documentation Forum{' '}
                                                <span className="mvx-font icon-link-right-arrow" />
                                            </a>
                                        </figcaption>
                                    </div>

                                    <div className="mvx-documentation-support-forum right-forum">
                                        <figure>
                                            <i className="mvx-font icon-support-forum" />
                                        </figure>
                                        <figcaption>
                                            <h2>Support Forum</h2>
                                            <div>
                                                <p>
                                                    Lost somewhere or have a query to make? Join us on our support forum and flag your issue.
                                                </p>
                                            </div>
                                            <a href="#">
                                                Join Support Forum{' '}
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
                                                    Add Vendor
                                                </a>
                                            </li>
                                            <li>
                                                <a href={`?page=mvx#&submenu=commission`}>
                                                    <figure>
                                                        <i className="mvx-font icon-vendor-personal" />
                                                    </figure>
                                                    Commission
                                                </a>
                                            </li>
                                            <li>
                                                <a href={`post-new.php?post_type=product`}>
                                                    <figure>
                                                        <i className="mvx-font icon-vendor-personal" />
                                                    </figure>
                                                    Add Product
                                                </a>
                                            </li>
                                            <li>
                                                <a href={`?page=mvx#&submenu=payment`}>
                                                    <figure>
                                                        <i className="mvx-font icon-vendor-personal" />
                                                    </figure>
                                                    Payment
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
                                    <span>Pro</span>
                                </div>
                            </div>
                            <h1>Get more by Switching to Pro</h1>
                            <p>
                                Stop waiting for new opportunity to grow your business instead pounce onto the pack of premium features.
                            </p>
                            <a href="#" className="mvx-btn btn-red">
                                Upgrade to Pro
                            </a>
                        </div>

                        <div className="mvx-text-center">
                            <h1>
                                Here Is What You Get In Pro Compared to Free
                            </h1>
                            <ul className="mvx-compare-table-holder">
                                <li className="mvx-compare-table-row">
                                    <ul>
                                        <li/>
                                        <li>Support</li>
                                        <li>2 Premium Modules</li>
                                        <li>Store Widgets</li>
                                        <li>Premium</li>
                                        <li>Modules</li>
                                        <li>Support</li>
                                        <li>2 Premium Modules</li>
                                        <li>Store Widgets</li>
                                        <li>Premium</li>
                                        <li>Modules</li>
                                        {this.state.money_back_show_more_compared ?
                                            <li>Modules</li>
                                            : ''
                                        }
                                           <li className='mvx-show-compare'>
                                            {this.state.money_back_show_more_compared ?

                                                <a className="show-link" onClick={(e) =>
                                                                (
                                                                    this.setState({
                                                                        money_back_show_more_compared: false
                                                                    })
                                                                )
                                                            }>
                                                    <span>
                                                        <i className="mvx-font icon-up-round-arrow" />
                                                    </span>{' '}
                                                    Show Less
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
                                                        <i className="mvx-font icon-down-round-arrow" />
                                                    </span>{' '}
                                                    Show More
                                                </a>
                                            }
                                        </li>
                                    </ul>
                                </li>

                                <li className="mvx-compare-table-row">
                                    <ul>
                                        <li>Free</li>
                                        <li>Ticket Based Support</li>
                                        <li>
                                            <i className="mvx-font icon-no red" />
                                        </li>
                                        <li>
                                            <i className="mvx-font icon-no red" />
                                        </li>
                                        <li>Five Venders</li>
                                        <li>
                                            <i className="mvx-font icon-yes blue" />
                                        </li>
                                        <li>
                                            <i className="mvx-font icon-no red" />
                                        </li>
                                        <li>
                                            <i className="mvx-font icon-no red" />
                                        </li>
                                        <li>
                                            <i className="mvx-font icon-no red" />
                                        </li>
                                        <li>Five Venders</li>
                                        <li>
                                            <i className="mvx-font icon-yes blue" />
                                        </li>

                                        {this.state.money_back_show_more_compared ?
                                            <li>
                                                <i className="mvx-font icon-yes blue" />
                                            </li>
                                            : ''
                                        }
                                        
                                    </ul>
                                </li>

                                <li className="mvx-compare-table-row mvx-recomend">
                                    <span className="recommend-tag">
                                        Recommend
                                    </span>
                                    <ul>
                                        <li>Pro</li>
                                        <li>Ticket Based Support</li>
                                        <li>
                                            <i className="mvx-font icon-no red" />
                                        </li>
                                        <li>
                                            <i className="mvx-font icon-no red" />
                                        </li>
                                        <li>Five Venders</li>
                                    
                                        <li>
                                            <i className="mvx-font icon-no red" />
                                        </li>
                                        <li>Ticket Based Support</li>
                                        <li>
                                            <i className="mvx-font icon-no red" />
                                        </li>
                                        <li>
                                            <i className="mvx-font icon-no red" />
                                        </li>
                                        <li>Five Venders</li>
                                        <li>
                                            <i className="mvx-font icon-yes blue" />
                                        </li>


                                        {this.state.money_back_show_more_compared ?
                                            <li>
                                                <i className="mvx-font icon-yes blue" />
                                            </li>
                                            : ''
                                        }
                                    </ul>
                                </li>
                               
                                        
                            </ul>
                         
                        </div>

                        <div className="mvx-text-center">
                            <div className='mvx-money-header-wrap'>
                            <h1>
                                <span className="mvx-gra-txt">30 Days</span>{' '}
                                Money-Back Guarantee
                            </h1> 
                            <p>
                                Make an investment for a better marketplace by saying 'yes' to your desired plans.
                            </p>
                            </div>
                            <ul className="mvx-money-table-holder">
                                <li className='mvx-money-table-coloumn' >
                                    <ul>
                                        <li>Yearly</li>
                                        <li>
                                            <div className="m-price">
                                                <h1>$399 </h1> &nbsp;<p><s>/$599</s> </p>
                                            </div>
                                        </li>
                                        <li className="mvx-btn btn-border">
                                            <a
                                                href="#"
                                            >
                                                Buy Now
                                            </a>
                                        </li>
                                        <div className='mvx-price-component'>
                                            <span>        
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    10 Sites
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    50+ Modules
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-form-radio" />{' '}
                                                    Unlimited Support{' '}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    Lifetime Updates
                                                </p>
                                                        
                                            </span>
                                            {this.state.money_back_show_more_yearly ?
                                                <span>    
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        10 Sites
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        50+ Modules
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-form-radio" />{' '}
                                                        Unlimited Support{' '}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        Lifetime Updates
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
                                                    See Less Details
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
                                                    See More Details
                                                </a>
                                                }
                                        </div>
                                </li>
                               
                                <li className='mvx-spr-sever-rcmnd mvx-money-table-coloumn'>
                                    <span className="mvx-recommend-tag">
                                        Super saver
                                    </span>
                                    <ul>
                                        <li>Lifetime</li>
                                        <li>
                                            <div className="m-price">
                                                <h1>$499 </h1> &nbsp;<p><s>/$599</s> </p>
                                            </div>
                                        </li>
                                        <li className="mvx-btn btn-red">
                                            <a
                                                href="#"
                                            >
                                                Buy Now
                                            </a>
                                        </li>
                                        <div className='mvx-price-component'>
                                            <span>    
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    10 Sites
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    50+ Modules
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-form-radio" />{' '}
                                                    Unlimited Support{' '}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    Lifetime Updates
                                                </p>
                                            </span>

                                            {this.state.money_back_show_more_yearly ?
                                                <span>    
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        10 Sites
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        50+ Modules
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-form-radio" />{' '}
                                                        Unlimited Support{' '}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        Lifetime Updates
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
                                                    See Less Details
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
                                                    See More Details
                                                </a>
                                                }
                                        </div>
                                </li>
                            

                                <li  className='mvx-money-table-coloumn'>
                                    <ul>
                                        <li>Monthly</li>
                                        <li>
                                            <div className="m-price">
                                                <h1>$299 </h1>&nbsp;<p><s>/$599</s> </p>
                                            </div>
                                        </li>
                                        <li className="mvx-btn btn-border">
                                            <a
                                                href="#"
                                            >
                                                Buy Now
                                            </a>
                                        </li>
                                        <div className='mvx-price-component'>
                                            
                                            <span>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    10 Sites
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    50+ Modules
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-form-radio" />{' '}
                                                    Unlimited Support{' '}
                                                </p>
                                                <p>
                                                    <i className="mvx-font icon-documentation-forum" />{' '}
                                                    Lifetime Updates
                                                </p>
                                            </span>

                                            {this.state.money_back_show_more_yearly ?
                                                <span>    
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        10 Sites
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        50+ Modules
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-form-radio" />{' '}
                                                        Unlimited Support{' '}
                                                    </p>
                                                    <p>
                                                        <i className="mvx-font icon-documentation-forum" />{' '}
                                                        Lifetime Updates
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
                                                    See Less Details
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
                                                    See More Details
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
                                                    See Less Details
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
                                                    See More Details
                                                </a>
                                                }
                                        </div>

                        </div>

                        <div className="mvx-upgrade-pro-section pro-bg">
                            <div className="mvx-dashboard-top-icon">
                                <span>Pro</span>
                            </div>
                            <h1>Ready to get started?</h1>
                            <p>
                                Remember you are just one-click away from your newly optimised marketplace.
                            </p>
                            <a href="#" className="mvx-btn btn-red">
                                Upgrade to Pro
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}
export default MVX_Dashboard;