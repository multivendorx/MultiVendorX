import React, { useEffect } from "react";

const SetupReady = () => {
    const siteUrl = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname.split('/')[1];

    useEffect(() => {
        if (!document.getElementById('twitter-wjs')) {
          const script = document.createElement('script');
          script.id = 'twitter-wjs';
          script.src = "https://platform.twitter.com/widgets.js";
          document.body.appendChild(script);
        }
      }, []);

    return (
        <>
            <div className="mvx-all-done-page-header-sec">
                <i className="mvx-font icon-yes"></i>
                <h1 className="mvx-title">Yay! All done!</h1>
                <a 
                href="https://twitter.com/share" 
                className="twitter-button" 
                data-url={siteUrl} 
                data-text="Hey Guys! Our new marketplace is now live and ready to be ransacked! Check it out at" 
                data-via="wc_marketplace" 
                data-size="large"
                >
                    <i className="mvx-font icon-twitter-setup-widget"></i> Tweet
                </a>
            </div>
            
            <div className="woocommerce-message woocommerce-tracker">
                <p>Your marketplace is ready. It's time to bring some sellers on your platform and start your journey. We wish you all the success for your business, you will be great!</p>
            </div>
            <div className="wc-setup-next-steps">
                <div className="wc-setup-next-steps-first">
                    <h2>Next steps</h2>
                    <ul>
                        <li className="setup-product"><a className="button button-primary btn-red" href={appLocalizer.redirect_url + 'admin.php?page=mvx#&submenu=settings&name=registration'}>Create your vendor registration form</a></li>
                    </ul>
                </div>
                <div className="wc-setup-next-steps-last">
                    <h2>Learn more</h2>
                    <ul>
                        <li> <i className="mvx-font icon-watch-setup-widget"></i><a href="https://www.youtube.com/c/MultivendorX">Watch the tutorial videos</a></li>
                        <li> <i className="mvx-font icon-help-setup-widget"></i><a href="https://multivendorx.com/knowledgebase/mvx-setup-guide/?utm_source=mvx_plugin&utm_medium=setup_wizard&utm_campaign=new_installation&utm_content=documentation">Looking for help to get started</a></li>
                        <li> <i className="mvx-font icon-Learn-more-setup-widget"></i><a href="https://multivendorx.com/best-revenue-model-marketplace-part-one/?utm_source=mvx_plugin&utm_medium=setup_wizard&utm_campaign=new_installation&utm_content=blog">Learn more about revenue models</a></li>
                    </ul>
                </div>
            </div>
        </>
    );
}

export default SetupReady;