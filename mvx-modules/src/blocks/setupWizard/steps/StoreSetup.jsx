import React, { useEffect, useState } from "react";
import axios, { Axios } from 'axios';
import { getApiLink } from "../../../services/apiService";

const StoreSetup = (props) => {
    const [ vendorSlug, setVendorSlug ] = useState('');
    const [ isSingleProductMultipleVendor, setIsSingleProductMultipleVendor ] = useState(false);
    const [ siteUrl, setSiteUrl ] = useState('');
    
    useEffect( () => {
        axios({
            method : "get",
            url    : getApiLink('get_store'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
        }).then((response) => {
            const { vendor_slug, is_single_product_multiple_vendor, site_url  } = response.data;

            setVendorSlug(vendor_slug);
            setIsSingleProductMultipleVendor(is_single_product_multiple_vendor === 'Enable');
            setSiteUrl(site_url);
        })
        .catch((error) => {
            console.error('Error fetching store settings:', error);
        });
    }, [])

    function handleStoreUrlChange(event){
        setVendorSlug(event.target.value);
    }

    function handleIsSingleProductMultipleVendorChange(event){
        setIsSingleProductMultipleVendor(!isSingleProductMultipleVendor);
    }

    function submitForm(e){
        e.preventDefault();
        const data = {
            vendor_store_url: vendorSlug,
            is_single_product_multiple_vendor: isSingleProductMultipleVendor,
        };
        axios({
            method: "post",
            url: getApiLink('set_store'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
            data: data
        }).then((response) =>{
            console.log("Settings saved successfully:", response.data);
            props.onNext();
        }).catch((error) => {
            console.error("Error saving settings:", error.response ? error.response.data : error.message);
        });
    }

    return (
        <>
            <h1>Store setup</h1>
            <div className="mvx-setting-section-divider">&nbsp;</div>
            <form method="post">
                <table className="form-table">
                    <tr>
                        <th scope="row"><label for="vendor_store_url">Store URL</label></th>
                        <td className="mvx-store-setup">
                            <input 
                            onChange={handleStoreUrlChange} 
                            type="text" id="vendor_store_url" 
                            name="vendor_store_url" placeholder="vendor" 
                            value={vendorSlug} />
                            <p className="description">Define vendor store URL ({siteUrl}/[this-text]/[seller-name])</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="is_single_product_multiple_vendor">Single Product Multiple Vendors</label></th>
                        <td>
                            <input 
                            onChange={handleIsSingleProductMultipleVendorChange} 
                            type="checkbox" 
                            checked={isSingleProductMultipleVendor} 
                            id="is_single_product_multiple_vendor" 
                            name="is_single_product_multiple_vendor" 
                            className="input-checkbox" 
                             />
                        </td>
                    </tr>
                </table>
                <p className="wc-setup-actions step">
                    <a onClick={props.onNext} className="button button-large button-next">Skip this step</a>
                    <input 
                    onClick={submitForm} 
                    type="submit" 
                    className="button-primary button button-large button-next" 
                    value="Continue" 
                    name="save_step" />
                </p>
            </form>
        </>
    );
}

export default StoreSetup;