import React, { useEffect, useState } from "react";
import CommissionField from "../components/CommissionField";
import ToggleRectangle from "../../../components/AdminLibrary/Inputs/ToggleRectangle";
import SelectInput from "../../../components/AdminLibrary/Inputs/SelectInput";
import Button from "../../../components/AdminLibrary/Inputs/Button";
import { getApiLink } from "../../../services/apiService";
import axios from "axios";

const CommissionSetup = (props) => {
    const [ revenueSharingMode, setRevenueSharingMode ] = useState('');
    const [ commissionType, setCommissionType ] = useState('');
    const [ commission, setFixedCommission ] = useState();
    const [ percentCommission, setPercentCommission ] = useState();

    const revenueOptions = [
        { key: "admin", value: "revenue_sharing_mode_admin", label: "Admin fees", name: "revenue_sharing_mode" },
        { key: "vendor", value: "revenue_sharing_mode_vendor", label: "Vendor Commissions", name: "revenue_sharing_mode" }
    ];

    const commissionOptions = [
        { value: 'fixed', label: 'Fixed Amount' },
        { value: 'percent', label: 'Percentage' },
        { value: 'fixed_with_percentage', label: '%age + Fixed (per transaction)' },
        { value: 'fixed_with_percentage_qty', label: '%age + Fixed (per unit)' },
    ];

    useEffect(() => {
        axios({
            method : "get",
            url    : getApiLink('get_commission_setting'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
        }).then((response) => {
            const { revenue_sharing_mode, commission_type, fixed, percentage } = response.data;

            setRevenueSharingMode(revenue_sharing_mode);
            setCommissionType(commission_type)
            setFixedCommission(fixed)
            setPercentCommission(percentage);
        })
        .catch((error) => {
            console.error('Error fetching commission settings:', error);
        });
    }, []);

    const handleRevenueSharingModeChange = (e) => {
        setRevenueSharingMode(e.target.value);
    }

    const handleCommissionTypeChange = (option) => {
        setCommissionType(option.value);
    }

    const handleCommissionValueChange = (value, type) => {
        if(type === 'default_commission'){
            setFixedCommission(value);
        }else if(type === 'default_percentage'){
            setPercentCommission(value);
        }
    }

    const submitForm = (e) => {
        e.preventDefault();
        const data = {
            revenue_sharing_mode: revenueSharingMode,
            commission_type: commissionType,
            fixed: commission,
            percentage: percentCommission,
        };
        axios({
            method: "post",
            url: getApiLink('set_commission_setting'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
            data: data
        }).then((response) =>{
            console.log("Settings saved successfully:", response.data);
            props.onNext();
        }).catch((error) => {
            console.error("Error saving settings:", error.response ? error.response.data : error.message);
        });
    }

    const commissionFields = (type) => {
        switch(type){
            case 'fixed':
                return <CommissionField 
                    id="tr_default_commission" 
                    type="default_commission"  
                    value={commission} 
                    handler={handleCommissionValueChange} />;
            case 'percent':
                return <CommissionField 
                    id="tr_default_percentage" 
                    type="default_percentage"  
                    value={percentCommission} 
                    handler={handleCommissionValueChange} />
            case 'fixed_with_percentage':
                return (
                    <>
                    <CommissionField 
                        id="tr_fixed_with_percentage" 
                        type="default_commission"  
                        value={commission} 
                        handler={handleCommissionValueChange} />
                    <CommissionField 
                        id="tr_default_percentage" 
                        type="default_percentage"  
                        value={percentCommission} 
                        handler={handleCommissionValueChange} />
                    </>
                );
            case 'fixed_with_percentage_qty':
                return (
                    <>
                    <CommissionField 
                        id="tr_fixed_with_percentage_qty" 
                        type="default_commission"  
                        value={commission} 
                        handler={handleCommissionValueChange} />
                    <CommissionField 
                        id="tr_default_percentage" 
                        type="default_percentage"  
                        value={percentCommission} 
                        handler={handleCommissionValueChange} />
                    </>
                );
            default: 
                return null;
        }
    }

    return (
        <>
            <h1>Commission Setup</h1>
            <div className="mvx-setting-section-divider">&nbsp;</div>
            <form method="post">
                <table className="form-table">
                    <tr>
                        <th scope="row"><label htmlFor="revenue_sharing_mode">Revenue Sharing Mode</label></th>
                        <td>
                            <ToggleRectangle
                                inputClass="input-radio"
                                idPrefix="toggle"
                                options={revenueOptions}
                                value={revenueSharingMode}
                                onChange={handleRevenueSharingModeChange}
                            />
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label htmlFor="commission_type">Commission Type</label></th>
                        <td>
                            <SelectInput
                                options={commissionOptions}
                                value={commissionType}
                                onChange={handleCommissionTypeChange}
                                inputClass="wc-enhanced-select"
                            />
                        </td>
                    </tr>
                    {
                        commissionFields(commissionType)}
                </table>
                <p className="wc-setup-actions step">
                    <Button 
                        onClick={props.onNext}
                        type="button"
                        inputClass="button button-large button-next"
                        value="Skip this step"
                    />
                    <Button 
                        onClick={submitForm} 
                        type="submit" 
                        inputClass="button-primary button button-large button-next" 
                        value="Continue"
                    />
                </p>
            </form>
        </>
    );
}

export default CommissionSetup;