import { useState } from "react";
import { __ } from "@wordpress/i18n";

const gateways = {
    paypal_masspay: {
        label: __('Paypal Masspay', 'multivendorx'),
        description: __('Pay via paypal masspay', 'multivendorx'),
        class: 'featured featured-row-last'
    },
    paypal_payout: {
        label: __('Paypal Payout', 'multivendorx'),
        description: __('Pay via paypal payout', 'multivendorx'),
        class: 'featured featured-row-first'
    },
    direct_bank: {
        label: __('Direct Bank Transfer', 'multivendorx'),
        description: __('', 'multivendorx'),
        class: ''
    },
    stripe_masspay: {
        label: __('Stripe Connect', 'multivendorx'),
        description: __('', 'multivendorx'),
        class: ''
    }
};
    

const Payments = (props) => {
    const { onNext } = props;
    
    const [enabledGateways, setEnabledGateways] = useState(
        Object.keys(gateways).reduce((acc, key) => ({ ...acc, [key]: false }), {})
    );
    const [disbursalMode, setDisbursalMode] = useState(false);
    const [paymentSchedule, setPaymentSchedule] = useState("");
    const [withdrawalRequest, setWithdrawalRequest] = useState(false);

    const handleGatewayToggle = (gatewayId) => {
        setEnabledGateways((prev) => ({
            ...prev,
            [gatewayId]: !prev[gatewayId],
        }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        console.log({ enabledGateways, disbursalMode, paymentSchedule, withdrawalRequest });
        onNext();
    };

    return (
        <section>
            <h1>Payments</h1>
            <div className="mvx-setting-section-divider">&nbsp;</div>
            <h3 className="mvx-pay-heading">Allowed Payment Methods</h3>
            <ul className="wc-wizard-services wc-wizard-payment-gateways">
                {Object.entries(gateways).map(([id, gateway]) => (
                    <li key={id} className={`wc-wizard-service-item wc-wizard-gateway ${gateway.class}`}>
                    <div className="wc-wizard-service-name">
                        <label>{gateway.label}</label>
                    </div>
                    <div className="wc-wizard-gateway-description">
                        <p>{gateway.description}</p>
                    </div>
                    <div className="wc-wizard-service-enable">
                        <span className="wc-wizard-service-toggle">
                        <input
                            type="checkbox"
                            checked={enabledGateways[id]}
                            onChange={() => handleGatewayToggle(id)}
                        />
                        </span>
                    </div>
                    </li>
                ))}
            </ul>
            <table className="form-table">
                <tbody>
                    <tr>
                    <th scope="row">
                        <label>Disbursal Schedule</label>
                    </th>
                    <td>
                        <input
                        type="checkbox"
                        checked={disbursalMode}
                        onChange={() => setDisbursalMode(!disbursalMode)}
                        />
                        <p className="description">If checked, vendors' commissions will be disbursed automatically.</p>
                    </td>
                    </tr>
                    {disbursalMode && (
                    <tr>
                        <th scope="row">
                            <label>Set Schedule</label>
                        </th>
                        <td>
                            {["weekly", "daily", "monthly", "fortnightly", "hourly"].map((schedule) => (
                                <label key={schedule}>
                                <input
                                    type="radio"
                                    name="payment_schedule"
                                    value={schedule}
                                    checked={paymentSchedule === schedule}
                                    onChange={() => setPaymentSchedule(schedule)}
                                />
                                {schedule.charAt(0).toUpperCase() + schedule.slice(1)}
                                </label>
                            ))}
                        </td>
                    </tr>
                    )}
                    <tr>
                    <th scope="row">
                        <label>Withdrawal Request</label>
                    </th>
                    <td>
                        <input
                        type="checkbox"
                        checked={withdrawalRequest}
                        onChange={() => setWithdrawalRequest(!withdrawalRequest)}
                        />
                        <p className="description">Vendors can request commission withdrawal.</p>
                    </td>
                    </tr>
                </tbody>
                </table>
                <p className="wc-setup-actions step">
                    <button className="previous-btn" onClick={onNext}>
                        Skip this step
                    </button>
                    <button className="next-btn" onClick={handleSubmit}>
                        Continue
                    </button>
                </p>
        </section>
    );
};

export default Payments;
