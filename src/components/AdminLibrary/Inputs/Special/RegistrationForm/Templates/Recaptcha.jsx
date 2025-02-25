import { useState, useEffect } from 'react';
import Recapcha from '../../../../../../assets/images/recaptcha.png';

const Recaptach = (props) => {
    const { formField, onChange } = props;

    return (
        <>
            <div className={`main-input-wrapper ${!formField.sitekey ? 'recaptcha' : ''}`}>
                <p>reCAPTCHA has been successfully added to the form.</p>
            </div>
        </>
    )
}

export default Recaptach;