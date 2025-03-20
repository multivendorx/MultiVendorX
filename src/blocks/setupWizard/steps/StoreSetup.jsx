import React, { useState } from "react";

const StoreSetup = (props) => {
    const { onNext } = props;
    const [vendorStoreUrl, setVendorStoreUrl] = useState("");
    const [singleProductMultipleVendor, setSingleProductMultipleVendor] = useState(false);
    const [businessType, setBusinessType] = useState("general_marketplace");
    const [productType, setProductType] = useState("simple_products");


    const handleBusinessTypeChange = (event)=>{
        setBusinessType(event.target.value);
    }
    const handleSubmit = (event) => {
        event.preventDefault();
        // Handle form submission logic here
        console.log("Store url : ",vendorStoreUrl);
        console.log("singleProductMultipleVendor : ",singleProductMultipleVendor);
        onNext();
    };

    return (
        <section>
            <h1>Store setup</h1>
            <div className="mvx-setting-section-divider">&nbsp;</div>
                <table className="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label htmlFor="vendor_store_url">Store URL</label>
                            </th>
                            <td className="mvx-store-setup">
                                <input
                                    type="text"
                                    id="vendor_store_url"
                                    name="vendor_store_url"
                                    placeholder="vendor"
                                    value={vendorStoreUrl}
                                    onChange={(e) => setVendorStoreUrl(e.target.value)}
                                />
                                <p className="description">
                                    Define vendor store URL ({appLocalizer.siteUrl}/[this-text]/[seller-name])
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label htmlFor="is_single_product_multiple_vendor">Single Product Multiple Vendors</label>
                            </th>
                            <td>
                                <input
                                    type="checkbox"
                                    id="is_single_product_multiple_vendor"
                                    name="is_single_product_multiple_vendor"
                                    className="input-checkbox"
                                    checked={singleProductMultipleVendor}
                                    onChange={() => setSingleProductMultipleVendor(!singleProductMultipleVendor)}
                                />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label htmlFor="business_type">Select Business Type</label>
                            </th>
                            <td>
                                <select name="business_type" id="business_type" onChange={handleBusinessTypeChange} value={businessType}>
                                    <option value="general_marketplace">General Marketplace (e.g., Amazon)</option>
                                    <option value="booking_marketplace">Booking Marketplace (e.g., Airbnb)</option>
                                    <option value="subscription_marketplace">Subscription Marketplace (e.g., Cratejoy)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label htmlFor="product_type">Choose Product Types</label>
                            </th>
                            <td>
                                <select name="product_type" id="product_type" onChange={(e)=>{setProductType(e.target.value)}} value={productType}>
                                    <option value="simple_products">Simple Products</option>
                                    <option value="variable_products">Variable Products (e.g., size, color variations)</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p className="wc-setup-actions step">
                    <button onClick={onNext} className="previous-btn">Skip this step</button>
                    <button onClick={handleSubmit} className="next-btn">Continue</button>
                </p>
        </section>
    );
};

export default StoreSetup;
