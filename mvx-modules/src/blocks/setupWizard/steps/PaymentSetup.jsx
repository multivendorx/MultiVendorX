import React, { useEffect, useState } from "react";
import axios from "axios";
import { getApiLink } from "../../../services/apiService";

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

    const gateways = [
        {
            id: 'paypal_masspay',
            label: 'Paypal Masspay',
            description: 'Pay via paypal masspay',
            class: 'featured featured-row-last',
        },
        {
            id: 'paypal_payout',
            label: 'Paypal Payout',
            description: 'Pay via paypal payout',
            class: 'featured featured-row-first',
        },
        {
            id: 'direct_bank',
            label: 'Direct Bank Transfer',
            description: '',
            class: '',
        },
        {
            id: 'stripe_masspay',
            label: 'Stripe Connect',
            description: '',
            class: '',
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

    const moduleActiveChangeHandler = (id) => {
        setMvxIsModuleActive(prevState => ({
            ...prevState,
            [id]: !prevState[id]
          }));
    }

    const disbursalModeAdminChangeHandler = () => {
        setDisbursalModeAdmin(prevState => !prevState);
    }

    const paymentScheduleChangeHandler = (value) => {
        setPaymentSchedule(value);
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

    const gatewayFields = () => {
        return ( gateways.map((gateway) => {
            return (
                <li key={gateway.id} className={"wc-wizard-service-item wc-wizard-gateway " + gateway.class}>
                    <div className="wc-wizard-service-name">
                        <label htmlFor={gateway.id}>
                            {gateway.label}
                        </label>
                    </div>
                    <div className="wc-wizard-gateway-description">
                        {gateway.description}
                    </div>
                    <div className="wc-wizard-service-enable">
                        <span className="wc-wizard-service-toggle disabled">
                            <input 
                            id={gateway.id} 
                            type="checkbox" 
                            checked={mvxIsModuleActive[gateway.id]} 
                            onClick={() => moduleActiveChangeHandler(gateway.id)}
                            name={"payment_method_" + gateway.id} 
                            className="input-checkbox" 
                            value="Enable" />
                        </span>
                    </div>
                </li>
            );
        }));
    }

    return (
        <>
            <h1>Payments</h1>
            <div className="mvx-setting-section-divider">&nbsp;</div>
            <form method="post" className="wc-wizard-payment-gateway-form">
                <h3 className='mvx-pay-heading'>Allowed Payment Methods</h3>
                <ul className="wc-wizard-services wc-wizard-payment-gateways">
                {gatewayFields()}
                </ul>
                <table className="form-table">
                    <tr>
                        <th scope="row"><label htmlFor="mvx_disbursal_mode_admin">Disbursal Schedule</label></th>
                        <td>
                            <input 
                            type="checkbox" 
                            checked={disbursalModeAdmin} 
                            onClick={disbursalModeAdminChangeHandler}
                            id="mvx_disbursal_mode_admin" 
                            name="mvx_disbursal_mode_admin" 
                            className="input-checkbox" 
                            value="Enable" />
                            <p className="description">If checked, automatically vendors commission will disburse.</p>
                        </td>
                    </tr>
                    {disbursalModeAdmin && <tr>
                        <th scope="row"><label htmlFor="payment_schedule">Set Schedule</label></th>
                        <td>
                            <label><input type="radio" onChange={(event) => paymentScheduleChangeHandler(event.target.value)} checked={paymentSchedule === 'weekly'} name="payment_schedule" className="input-radio" value="weekly" /> Weekly</label><br/>
                            <label><input type="radio" onChange={(event) => paymentScheduleChangeHandler(event.target.value)} checked={paymentSchedule === 'daily'} name="payment_schedule" className="input-radio" value="daily" /> Daily</label><br/>
                            <label><input type="radio" onChange={(event) => paymentScheduleChangeHandler(event.target.value)} checked={paymentSchedule === 'monthly'} name="payment_schedule" className="input-radio" value="monthly" /> Monthly</label><br/>
                            <label><input type="radio" onChange={(event) => paymentScheduleChangeHandler(event.target.value)} checked={paymentSchedule === 'fortnightly'} name="payment_schedule" className="input-radio" value="fortnightly" /> Fortnightly</label><br/>
                            <label><input type="radio" onChange={(event) => paymentScheduleChangeHandler(event.target.value)} checked={paymentSchedule === 'hourly'} name="payment_schedule" className="input-radio" value="hourly" /> Hourly</label>
                        </td>
                    </tr>}
                    <tr>
                        <th scope="row"><label htmlFor="mvx_disbursal_mode_vendor">Withdrawal Request</label></th>
                        <td>
                            <input 
                            type="checkbox" 
                            onClick={disbursalModeVendorChangeHandler}
                            checked={disbursalModeVendor} 
                            id="mvx_disbursal_mode_vendor" 
                            name="mvx_disbursal_mode_vendor" 
                            className="input-checkbox" 
                            value="Enable" />
                            <p className="description">Vendors can request for commission withdrawal.</p>
                        </td>
                    </tr>
                </table>
                <p className="wc-setup-actions step">
                    <a onClick={props.onNext} className="button button-large button-next">Skip this step</a>
                    <input type="submit" onClick={submitForm} className="button-primary button button-large button-next" value="Continue" name="save_step" />
                </p>
            </form>
        </>
    );
}

export default PaymentSetup;