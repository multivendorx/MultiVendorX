import React from "react";

const CommissionField = (props) => {
    return (
        <tr id={props.id} className="mvx_commission_type_fields">
            <th scope="row"><label htmlFor={props.type}>{props.type === 'default_commission' ? 'Commission Fixed' : 'Commission Percentage'}</label></th>
            <td>
                <input type="text" onChange={(event) => props.handler(event.target.value, props.type)} id={props.type} name={props.type} value={props.value} />
            </td>
        </tr>
    );
}

export default CommissionField;