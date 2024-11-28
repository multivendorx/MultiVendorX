import React from "react";
import BasicInput from "../../../components/AdminLibrary/Inputs/BasicInput";

const CommissionField = (props) => {
    return (
        <tr id={props.id} className="mvx_commission_type_fields">
            <th scope="row"><label htmlFor={props.type}>{props.type === 'default_commission' ? 'Commission Fixed' : 'Commission Percentage'}</label></th>
            <td>
                <BasicInput onChange={(event) => props.handler(event.target.value, props.type)} id={props.type} name={props.type} value={props.value} />
            </td>
        </tr>
    );
}

export default CommissionField;