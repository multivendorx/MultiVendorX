import React, { useEffect, useState } from "react";
import CommissionField from "../components/CommissionField";
import { getApiLink } from "../../../services/apiService";
import axios from "axios";

const CommissionSetup = (props) => {
    const [ revenueSharingMode, setRevenueSharingMode ] = useState('');
    const [ commissionType, setCommissionType ] = useState('');
    const [ commission, setFixedCommission ] = useState();
    const [ percentCommission, setPercentCommission ] = useState();

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

    const handleCommissionTypeChange = (e) => {
        setCommissionType(e.target.value);
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
                            <label><input 
                            onClick={handleRevenueSharingModeChange}
                            type="radio" 
                            checked={revenueSharingMode === 'revenue_sharing_mode_admin'} 
                            id="revenue_sharing_mode" 
                            name="revenue_sharing_mode" 
                            className="input-radio" 
                            value="revenue_sharing_mode_admin" /> 
                                Admin fees
                            </label><br/>
                            <label><input
                            onClick={handleRevenueSharingModeChange} 
                            type="radio" 
                            checked={revenueSharingMode === 'revenue_sharing_mode_vendor'} 
                            id="revenue_sharing_mode" 
                            name="revenue_sharing_mode" 
                            className="input-radio" 
                            value="revenue_sharing_mode_vendor" /> 
                                Vendor Commissions
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label htmlFor="commission_type">Commission Type</label></th>
                        <td>
                            <select onChange={handleCommissionTypeChange} id="commission_type" name="commission_type" className="wc-enhanced-select">
                                <option value="fixed" selected={commissionType === 'fixed'}>Fixed Amount</option>
                                <option value="percent" selected={commissionType === 'percent'}>Percentage</option>
                                <option value="fixed_with_percentage" selected={commissionType === 'fixed_with_percentage'}>%age + Fixed (per transaction)</option>
                                <option value="fixed_with_percentage_qty" selected={commissionType === 'fixed_with_percentage_qty'}>%age + Fixed (per unit)</option>
                            </select>
                        </td>
                    </tr>
                    {
                        commissionFields(commissionType)}
                </table>
                <p className="wc-setup-actions step">
                    <a onClick={props.onNext} className="button button-large button-next">Skip this step</a>
                    <input onClick={submitForm} type="submit" className="button-primary button button-large button-next" value="Continue" name="save_step" />
                </p>
            </form>
        </>
    );
}

export default CommissionSetup;