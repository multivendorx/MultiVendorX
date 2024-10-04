import React from "react";

const WooCommerceInstaller = () => {
    const handleSubmit = (event) => {
        event.preventDefault();

        axios({
            method: "get",
            url: getApiLink('install_woocommerce'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
        }).then((response) =>{
            console.log("WooCommerce installed successfully:", response.data);
            window.location.href = response.redirect;
        }).catch((error) => {
            console.error("Error installing WooCommerce:", error.response ? error.response.data : error.message);
        });
    };

    return (
        <div className="mvx-install-woocommerce">
            <p>MultivendorX requires WooCommerce plugin to be active!</p>
            <form onSubmit={handleSubmit} name="mvx_install_woocommerce">
            <button type="submit" className="button button-primary">
                Install WooCommerce
            </button>
            </form>
        </div>
    );
}

export default WooCommerceInstaller;