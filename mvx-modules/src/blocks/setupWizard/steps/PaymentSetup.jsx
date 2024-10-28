import React, { useEffect, useState } from "react";
import axios from "axios";
import { getApiLink } from "../../../services/apiService";
import MultiCheckBox from "../../../components/AdminLibrary/Inputs/MultiCheckbox";
import CheckBox from "../../../components/AdminLibrary/Inputs/CheckBox";
import RadioInput from "../../../components/AdminLibrary/Inputs/RadioInput";
import Button from "../../../components/AdminLibrary/Inputs/Button";

const PaymentSetup = (props) => {
    const [ disbursalModeAdmin, setDisbursalModeAdmin ] = useState(false);
    const [ disbursalModeVendor, setDisbursalModeVendor ] = useState(false);
    const [ paymentSchedule, setPaymentSchedule ] = useState('');
    const [ mvxIsModuleActive, setMvxIsModuleActive ] = useState({
        paypal_masspay: false,
        paypal_payout: false,
        direct_bank: false,
        stripe_masspay: false,
    });

    const paymentScheduleOptions = [
        { keyName: 'weekly', name: 'payment_schedule', value: 'weekly', label: 'Weekly'},
        { keyName: 'daily', name: 'payment_schedule', value: 'daily', label: 'Daily'},
        { keyName: 'monthly', name: 'payment_schedule', value: 'monthly', label: 'Monthly'},
        { keyName: 'fortnightly', name: 'payment_schedule', value: 'fortnightly', label: 'Fortnightly'},
        { keyName: 'hourly', name: 'payment_schedule', value: 'hourly', label: 'Hourly'},
    ];

    const gateways = [
        {
            key: 'paypal_masspay',
            value: 'paypal_masspay',
            label: 'Paypal Masspay',
            hints: 'Pay via paypal masspay',
        },
        {
            key: 'paypal_payout',
            value: 'paypal_payout',
            label: 'Paypal Payout',
            hints: 'Pay via paypal payout'
        },
        {
            key: 'direct_bank',
            value: 'direct_bank',
            label: 'Direct Bank Transfer',
            hints: ''
        },
        {
            key: 'stripe_masspay',
            value: 'stripe_masspay',
            label: 'Stripe Connect',
            hints: ''
        },
    ];

    useEffect(() => {
        axios({
            method : "get",
            url    : getApiLink('get_payment_setting'),
            headers: { "X-WP-Nonce": appLocalizer.nonce },
        }).then((response) => {
            const { mvx_disbursal_mode_admin, 
                payment_schedule, 
                mvx_disbursal_mode_vendor, 
                is_enable_gateway } = response.data;

            setDisbursalModeAdmin(mvx_disbursal_mode_admin);
            setDisbursalModeVendor(mvx_disbursal_mode_vendor);
            setPaymentSchedule(payment_schedule);
            setMvxIsModuleActive(is_enable_gateway);
        })
        .catch((error) => {
            console.error('Error fetching payment settings:', error);
        });
    }, [] );

    const moduleActiveChangeHandler = (event) => {
        const option = event.target.value;
        setMvxIsModuleActive(prevState => ({
            ...prevState,
            [option]: !prevState[option]
          }));
    }

    const disbursalModeAdminChangeHandler = () => {
        setDisbursalModeAdmin(prevState => !prevState);
    }

    const paymentScheduleChangeHandler = (event) => {
        setPaymentSchedule(event.target.value);
    }

    const disbursalModeVendorChangeHandler = () => {
        setDisbursalModeVendor(prevState => !prevState);
    }

    const submitForm = (e) => {
        e.preventDefault();
        const data = {
            mvx_disbursal_mode_admin: disbursalModeAdmin,
            mvx_disbursal_mode_vendor: disbursalModeVendor,
            is_module_active: mvxIsModuleActive,
            payment_schedule: paymentSchedule,
        };
        axios({
            method: "post",
            url: getApiLink('set_payment_setting'),
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
            <h1>Payments</h1>
            <div className="mvx-setting-section-divider">&nbsp;</div>
            <form method="post" className="wc-wizard-payment-gateway-form">
                <h3 className='mvx-pay-heading'>Allowed Payment Methods</h3>
                <MultiCheckBox
                    options={gateways}
                    value={Object.keys(mvxIsModuleActive).filter(key => mvxIsModuleActive[key] === true)}
                    onChange={moduleActiveChangeHandler}
                    inputClass="input-checkbox"
                    rightContent
                    rightContentClass="wc-wizard-service-name"
                    idPrefix="multi-checkbox"
                    hintInnerClass="wc-wizard-gateway-description"
                    inputInnerWrapperClass="wc-wizard-service-toggle disabled"
                    inputWrapperClass="wc-wizard-service-enable"
                />
                <table className="form-table">
                    <tr>
                        <th scope="row"><label htmlFor="mvx_disbursal_mode_admin">Disbursal Schedule</label></th>
                        <td>
                            <CheckBox
                                checked={disbursalModeAdmin} 
                                onClick={disbursalModeAdminChangeHandler}
                                name="mvx_disbursal_mode_admin" 
                                inputClass="input-checkbox" 
                                value="Enable" 
                                description="If checked, automatically vendors commission will disburse."
                            />
                        </td>
                    </tr>
                    {disbursalModeAdmin && <tr>
                        <th scope="row"><label htmlFor="payment_schedule">Set Schedule</label></th>
                        <td>
                            <RadioInput
                                inputClass="input-radio"
                                value={paymentSchedule}
                                options={paymentScheduleOptions}
                                onChange={paymentScheduleChangeHandler}
                                idPrefix="payment_schedule"
                                activeClass="active"
                            />
                        </td>
                    </tr>}
                    <tr>
                        <th scope="row"><label htmlFor="mvx_disbursal_mode_vendor">Withdrawal Request</label></th>
                        <td>
                            <CheckBox
                                onClick={disbursalModeVendorChangeHandler}
                                checked={disbursalModeVendor} 
                                name="mvx_disbursal_mode_vendor" 
                                inputClass="input-checkbox" 
                                value="Enable"
                                description="Vendors can request for commission withdrawal."
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

export default PaymentSetup;