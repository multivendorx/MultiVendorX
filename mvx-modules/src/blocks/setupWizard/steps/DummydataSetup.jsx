import React, { useState, useEffect } from "react";
import { getApiLink } from "../../../services/apiService";
import axios from "axios";
import Button from "../../../components/AdminLibrary/Inputs/Button";
import SelectInput from "../../../components/AdminLibrary/Inputs/SelectInput";

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

    const marketplaceOptions = [
        { value: 'niche', label: 'Niche Marketplace' },
        { value: 'booking', label: 'Booking Marketplace' },
        { value: 'subscription', label: 'Subscription Marketplace' },
        { value: 'rental', label: 'Rental Marketplace' },
        { value: 'auction', label: 'Auction Marketplace' },
    ];

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

    const handleMarketplaceTypeChange = (option) => {
        const value = option.value;
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
                            <SelectInput
                                options={marketplaceOptions}
                                value={marketplaceType}
                                onChange={handleMarketplaceTypeChange}
                                inputClass="wc-enhanced-select"
                                description="Dummy data will be imported based on the marketplace type."
                                descClass="description"
                            />
                            {marketplaceType === 'booking' && !isPluginActive.booking && <p id="booking-error" className="woocommerce-error">WooCommerce Bookings pulgin is required to continue.</p>}
                            {marketplaceType === 'subscription' && !isPluginActive.subscription && <p id="subscription-error" className="woocommerce-error">WooCommerce Subscriptions pulgin is required to continue.</p>}
                            {marketplaceType === 'rental' && !isPluginActive.rental && <p id="rental-error" className="woocommerce-error">WooCommerce Booking & Rental pulgin is required to continue.</p>}
                            {marketplaceType === 'auction' && !isPluginActive.auction && <p className="woocommerce-error">WooCommerce Simple Auction pulgin is required to continue.</p>}
                            
                        </td>
                    </tr>
                    
                </table>
                <p className="wc-setup-actions step">
                    <Button type="button" onClick={props.onNext} inputClass="button button-large button-next" value="Skip this step"/>
                    <input onClick={submitForm} disabled={isSubmitActive} type="submit" className="button-primary button button-large button-next" value="Continue"/>
                </p>
            </form>
        </>
    );
}

export default DummydataSetup;