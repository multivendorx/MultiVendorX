import { useState } from "react";

const CommissionSetup = (props) => {
    const { onNext } = props;
    const [revenueSharingMode, setRevenueSharingMode] = useState("revenue_sharing_mode_vendor");
    const [commissionType, setCommissionType] = useState("fixed");
    const [defaultCommission, setDefaultCommission] = useState("");
    const [defaultPercentage, setDefaultPercentage] = useState("");
    const [fixedWithPercentage, setFixedWithPercentage] = useState("");
    const [fixedWithPercentageQty, setFixedWithPercentageQty] = useState("");

    const handleSubmit = () => {
        // Handle form submission logic
        console.log({ revenueSharingMode, commissionType, defaultCommission, defaultPercentage, fixedWithPercentage, fixedWithPercentageQty });
        onNext();
    };

    return (
        <section>
            <h1>Commission Setup</h1>
            <div className="mvx-setting-section-divider">&nbsp;</div>
            <table className="form-table">
                <tbody>
                <tr>
                    <th scope="row"><label>Revenue Sharing Mode</label></th>
                    <td>
                    <label>
                        <input
                        type="radio"
                        name="revenue_sharing_mode"
                        value="revenue_sharing_mode_admin"
                        checked={revenueSharingMode === "revenue_sharing_mode_admin"}
                        onChange={() => setRevenueSharingMode("revenue_sharing_mode_admin")}
                        />
                        Admin fees
                    </label>
                    <br />
                    <label>
                        <input
                        type="radio"
                        name="revenue_sharing_mode"
                        value="revenue_sharing_mode_vendor"
                        checked={revenueSharingMode === "revenue_sharing_mode_vendor"}
                        onChange={() => setRevenueSharingMode("revenue_sharing_mode_vendor")}
                        />
                        Vendor Commissions
                    </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Commission Type</label></th>
                    <td>
                    <select value={commissionType} onChange={(e) => setCommissionType(e.target.value)}>
                        <option value="fixed">Fixed Amount</option>
                        <option value="percent">Percentage</option>
                        <option value="fixed_with_percentage">%age + Fixed (per transaction)</option>
                        <option value="fixed_with_percentage_qty">%age + Fixed (per unit)</option>
                    </select>
                    </td>
                </tr>
                {commissionType === "fixed" && (
                    <tr>
                    <th scope="row"><label>Commission Value</label></th>
                    <td>
                        <input
                        type="text"
                        value={defaultCommission}
                        onChange={(e) => setDefaultCommission(e.target.value)}
                        />
                    </td>
                    </tr>
                )}
                {commissionType === "percent" && (
                    <tr>
                    <th scope="row"><label>Commission Percentage</label></th>
                    <td>
                        <input
                        type="text"
                        value={defaultPercentage}
                        onChange={(e) => setDefaultPercentage(e.target.value)}
                        />
                    </td>
                    </tr>
                )}
                {commissionType === "fixed_with_percentage" && (
                    <>
                    <tr>
                        <th scope="row"><label>Commission Percentage</label></th>
                        <td>
                        <input
                            type="text"
                            value={defaultPercentage}
                            onChange={(e) => setDefaultPercentage(e.target.value)}
                        />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Fixed Amount</label></th>
                        <td>
                        <input
                            type="text"
                            value={fixedWithPercentage}
                            onChange={(e) => setFixedWithPercentage(e.target.value)}
                        />
                        </td>
                    </tr>
                    </>
                )}
                {commissionType === "fixed_with_percentage_qty" && (
                    <>
                    <tr>
                        <th scope="row"><label>Commission Percentage</label></th>
                        <td>
                        <input
                            type="text"
                            value={defaultPercentage}
                            onChange={(e) => setDefaultPercentage(e.target.value)}
                        />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label>Fixed Amount (per unit)</label></th>
                        <td>
                        <input
                            type="text"
                            value={fixedWithPercentageQty}
                            onChange={(e) => setFixedWithPercentageQty(e.target.value)}
                        />
                        </td>
                    </tr>
                    </>
                )}
                </tbody>
            </table>
            <p className="wc-setup-actions step">
                <button className="previous-btn" onClick={onNext}>Skip this step</button>
                <button className="next-btn" onClick={handleSubmit}>Continue</button>
            </p>
        </section>
    );
};

export default CommissionSetup;
