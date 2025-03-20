import { useState } from "react";
import { __ } from "@wordpress/i18n";

const capabilitiesData = {
    'is_submit_product':
    {
        label: __('Submit Products', 'multivendorx'),
        description: __('Allow vendors to submit products for approval/publishing.', 'multivendorx'),
        checked: false
    },
    'is_published_product':
    {
        label: __('Publish Products', 'multivendorx'),
        description: __('If checked, products uploaded by vendors will be directly published without admin approval.', 'multivendorx'),
        checked: false
    },
    'is_edit_delete_published_product':
    {
        label: __('Edit Publish Products', 'multivendorx'),
        description: __('Allow vendors to edit published products.', 'multivendorx'),
        checked: false
    },
    'is_submit_coupon':
    {
        label: __('Submit Coupons', 'multivendorx'),
        description: __('Allow vendors to create coupons.', 'multivendorx'),
        checked: false
    },
    'is_published_coupon':
    {
        label: __('Publish Coupons', 'multivendorx'),
        description: __('If checked, coupons added by vendors will be directly published without admin approval.', 'multivendorx'),
        checked: false
    },
    'is_edit_delete_published_coupon':
    {
        label: __('Edit Publish Coupons', 'multivendorx'),
        description: __('Allow vendors to edit or delete published shop coupons.', 'multivendorx'),
        checked: false
    },
    'is_upload_files':
    {
        label: __('Upload Media Files', 'multivendorx'),
        description: __('Allow vendors to upload media files.', 'multivendorx'),
        checked: false
    },
    'approve_vendor':
    {
        label: __('Auto-Approval Enabled?', 'multivendorx'),
        description: __('- Yes: Vendors approved automatically.\n- No: Manual approval required.', 'multivendorx'),
        checked: false
    }
}
const Capability = (props) => {
    const {onNext} = props;
    const [capabilities, setCapabilities] = useState(capabilitiesData);

    const handleChange = (e) => {
        const { name, checked } = e.target;
        setCapabilities((prev) => ({
            ...prev,
            [name]: { 
                ...prev[name], 
                checked: checked  // Store boolean directly
            }
        }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        console.log(capabilities);
        onNext();
    };

    return (
        <section>
        <h1>Capability</h1>
        <div className="mvx-setting-section-divider">&nbsp;</div>
            <table className="form-table">
            {Object.entries(capabilities).map(([key, value]) => (
                <tr key={key}>
                <th scope="row">
                    <label htmlFor={key}>{value.label}</label>
                </th>
                <td>
                    <input
                    type="checkbox"
                    id={key}
                    name={key}
                    className="input-checkbox"
                    value="Enable"
                    checked={value.checked}
                    onChange={handleChange}
                    />
                    <p className="description">{value.description}</p>
                </td>
                </tr>
            ))}
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

export default Capability;
