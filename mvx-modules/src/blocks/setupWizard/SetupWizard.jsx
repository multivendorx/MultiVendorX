import React, { useState } from 'react';
import Intro from './steps/intro';
import StoreSetup from './steps/StoreSetup';
import CommissionSetup from './steps/CommissionSetup';
import PaymentSetup from './steps/PaymentSetup';
import CapabilitySetup from './steps/CapabilitySetup';
import DummydataSetup from './steps/DummydataSetup';
import SetupReady from './steps/SetupReady';
import Logo from '../../../../assets/images/widget-multivendorX.svg'

const SetupWizard = () => {
    const [currentStep, setCurrentStep] = useState(0);

	const onNext = () => {
		setCurrentStep(currentStep + 1);
	}

    const exitWizard = () => {
		window.location.href = appLocalizer.redirect_url;
	}

    const steps = [
        { component: <Intro onNext={onNext} exitWizard={exitWizard}/>, title: 'Introduction' },
        { component: <StoreSetup onNext={onNext}/>, title: 'Store Setup' },
        { component: <CommissionSetup onNext={onNext}/>, title: 'Commission Setup' },
        { component: <PaymentSetup onNext={onNext}/>, title: 'Payments' },
        { component: <CapabilitySetup onNext={onNext}/>, title: 'Capability' },
        { component: <DummydataSetup onNext={onNext}/>, title: 'Import Dummy Data' },
        { component: <SetupReady/>, title: 'Ready!' },
    ];

    return (
        <>
        <h1 id="wc-logo"><a href="https://multivendorx.com/"><img src={Logo} alt="MultivendorX" /></a></h1>
        <ol className="wc-setup-steps">
            {steps.map((step, index) =>
                <li key={index} className={currentStep >= index ? 'active' : ''}>{step.title}</li>
            )
            }
        </ol>
        <div className="wc-setup-content">{steps[currentStep].component}</div>
        {currentStep === steps.length - 1 && <a className="wc-return-to-dashboard" onClick={exitWizard}>Return to the WordPress Dashboard</a>}
        </>
    );
}

export default SetupWizard;