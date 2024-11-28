import React, { useState, useEffect } from "react";
import axios from "axios";
import { getApiLink } from "../../../services/apiService";
import CheckBox from "../../../components/AdminLibrary/Inputs/CheckBox";
import Button from "../../../components/AdminLibrary/Inputs/Button";

const CapabilitySetup = (props) => {
    const [ capabilitySettings, setCapabilitySettings ] = useState({
        is_submit_product               : false,
        is_published_product            : false,
        is_edit_delete_published_product: false,
        is_submit_coupon                : false,
        is_published_coupon             : false,
        is_edit_delete_published_coupon : false,
        is_upload_files                 : false,
    });

    useEffect(() => {
        axios({
            method : "get",
            url    : getApiLink('get_capability_setting'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
        }).then((response) => {
            const capability_settings = response.data;
            setCapabilitySettings(prevState => ({
                ...prevState,
                ...capability_settings,
            }));
        })
        .catch((error) => {
            console.error('Error fetching capability settings:', error);
        });
    }, []);

    const handleCheckboxClick = (id) => {
        setCapabilitySettings(prevState => ({
            ...prevState,
            [id]: !prevState[id],
        }));
    };

    const submitForm = (e) => {
        e.preventDefault();
        axios({
            method: "post",
            url: getApiLink('set_capability_setting'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
            data: capabilitySettings
        }).then((response) =>{
            console.log("Settings saved successfully:", response.data);
            props.onNext();
        }).catch((error) => {
            console.error("Error saving settings:", error.response ? error.response.data : error.message);
        });
    }

    return (
        <>
            <h1>Capability</h1>
            <div className="mvx-setting-section-divider">&nbsp;</div>
            <form method="post">
                <table className="form-table">
                    <tr>
                        <th scope="row"><label htmlFor="is_submit_product">Submit Products</label></th>
                        <td>
                            <CheckBox
                                onClick={() => handleCheckboxClick('is_submit_product')} 
                                checked={capabilitySettings.is_submit_product} 
                                name="is_submit_product" 
                                className="input-checkbox" 
                                value="Enable" 
                                description="Allow vendors to submit products for approval/publishing."
                                descClass="description"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label htmlFor="is_published_product">Publish Products</label></th>
                        <td>
                            <CheckBox
                                onClick={() => handleCheckboxClick('is_published_product')} 
                                checked={capabilitySettings.is_published_product} 
                                name="is_published_product" 
                                className="input-checkbox" 
                                value="Enable"                       
                                description="If checked, products uploaded by vendors will be directly published without admin approval."
                                descClass="description"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label htmlFor="is_edit_delete_published_product">Edit Publish Products</label></th>
                        <td>
                            <CheckBox 
                                onClick={() => handleCheckboxClick('is_edit_delete_published_product')} 
                                checked={capabilitySettings.is_edit_delete_published_product} 
                                name="is_edit_delete_published_product" 
                                className="input-checkbox" 
                                value="Enable"
                                description="Allow vendors to Edit published products."
                                descClass="description"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label htmlFor="is_submit_coupon">Submit Coupons</label></th>
                        <td>
                            <CheckBox
                                onClick={() => handleCheckboxClick('is_submit_coupon')} 
                                checked={capabilitySettings.is_submit_coupon} 
                                name="is_submit_coupon" 
                                className="input-checkbox" 
                                value="Enable"
                                description="Allow vendors to create coupons."
                                descClass="description"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label htmlFor="is_published_coupon">Publish Coupons</label></th>
                        <td>
                            <CheckBox
                                onClick={() => handleCheckboxClick('is_published_coupon')} 
                                checked={capabilitySettings.is_published_coupon} 
                                name="is_published_coupon" 
                                className="input-checkbox" 
                                value="Enable"
                                description="If checked, coupons added by vendors will be directly published without admin approval."
                                descClass="description"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label htmlFor="is_edit_delete_published_coupon">Edit Publish Coupons</label></th>
                        <td>
                            <CheckBox
                                onClick={() => handleCheckboxClick('is_edit_delete_published_coupon')} 
                                checked={capabilitySettings.is_edit_delete_published_coupon} 
                                name="is_edit_delete_published_coupon" 
                                className="input-checkbox" 
                                value="Enable"
                                description="Allow Vendor To edit delete published shop coupons."
                                descClass="description"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label htmlFor="is_upload_files">Upload Media Files</label></th>
                        <td>
                            <CheckBox
                                onClick={() => handleCheckboxClick('is_upload_files')} 
                                checked={capabilitySettings.is_upload_files} 
                                name="is_upload_files" 
                                className="input-checkbox" 
                                value="Enable"
                                description="Allow vendors to upload media files."
                                descClass="description"
                            />
                        </td>
                    </tr>

                </table>
                <p className="wc-setup-actions step">
                    <Button 
                        type="button" 
                        onClick={props.onNext} 
                        inputClass="button button-large button-next" 
                        value="Skip this step"
                    />
                    <Button 
                        type="submit" 
                        onClick={submitForm} 
                        inputClass="button-primary button button-large button-next" 
                        value="Continue"
                    />
                </p>
            </form>
        </>
    );
}

export default CapabilitySetup;