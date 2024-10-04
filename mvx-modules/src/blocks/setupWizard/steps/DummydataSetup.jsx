import React, { useState, useEffect } from "react";
import { getApiLink } from "../../../services/apiService";
import axios from "axios";

const DummydataSetup = (props) => {
    const [ marketplaceType, setMarketplaceType ] = useState('niche');
    const [ isSubmitActive, setIsSubmitActive ] = useState(false);
    const [ isPluginActive, setIsPluginActive ] = useState({
        niche: true,
        booking : false,
        subscription : false,
        rental : false,
        auction : false,
    });

    useEffect(() => {
        axios({
            method : "get",
            url    : getApiLink('get_active_plugins'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
        }).then((response) => {
            const activePlugins = response.data;

            setIsPluginActive(prevState => ({
                ...prevState,
                ...activePlugins,
            }));
        })
        .catch((error) => {
            console.error('Error fetching active plugins:', error);
        });
    }, [] );

    const handleMarketplaceTypeChange = (e) => {
        const value = e.target.value;
        setMarketplaceType(value);
        
        if( !isPluginActive[value] ){
            setIsSubmitActive(true);
        }else{
            setIsSubmitActive(false);
        }
    }

    const submitForm = (e) => {
        e.preventDefault();
        const data = {
            marketplace_type: marketplaceType,
        };
        axios({
            method: "post",
            url: getApiLink('import_dummy_data'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
            data: data
        }).then((response) =>{
            console.log("Data imported successfully:", response.data);
            // props.onNext();
        }).catch((error) => {
            console.error("Error importing data:", error.response ? error.response.data : error.message);
        });
    }

    return (
        <>
            <h1>Import Dummy Data</h1>
            <div className="mvx-setting-section-divider">&nbsp;</div>
            <form method="post">
                <table className="form-table">
                    <tr>
                        <th scope="row"><label htmlFor="marketplace_type">Marketplace Type</label></th>
                        <td>
                            <select onChange={handleMarketplaceTypeChange} name="marketplace_type" id="marketplace_type" className="wc-enhanced-select">
                                <option selected={marketplaceType === 'niche'} value="niche">Niche Marketplace</option>
                                <option selected={marketplaceType === 'booking'} value="booking" >Booking Marketplace</option>
                                <option selected={marketplaceType === 'subscription'} value="subscription">Subscription Marketplace</option>
                                <option selected={marketplaceType === 'rental'} value="rental">Rental Marketplace</option>
                                <option selected={marketplaceType === 'auction'} value="auction">Auction Marketplace</option>
                            </select>
                            <p className="description">Dummy data will be imported based on the marketplace type.</p>
                            {marketplaceType === 'booking' && !isPluginActive.booking && <p id="booking-error" className="woocommerce-error">WooCommerce Bookings pulgin is required to continue.</p>}
                            {marketplaceType === 'subscription' && !isPluginActive.subscription && <p id="subscription-error" className="woocommerce-error">WooCommerce Subscriptions pulgin is required to continue.</p>}
                            {marketplaceType === 'rental' && !isPluginActive.rental && <p id="rental-error" className="woocommerce-error">WooCommerce Booking & Rental pulgin is required to continue.</p>}
                            {marketplaceType === 'auction' && !isPluginActive.auction && <p id="auction-error" className="woocommerce-error">WooCommerce Simple Auction pulgin is required to continue.</p>}
                            
                        </td>
                    </tr>
                    
                </table>
                <p className="wc-setup-actions step">
                    <a onClick={props.onNext} className="button button-large button-next">Skip this step</a>
                    <input onClick={submitForm} disabled={isSubmitActive} type="submit" className="button-primary button button-large button-next" value="Continue" name="save_step" />
                </p>
            </form>
        </>
    );
}

export default DummydataSetup;