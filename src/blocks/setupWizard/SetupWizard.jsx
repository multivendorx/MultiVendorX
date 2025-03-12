import React, { useState, useEffect } from 'react';
import { __ } from "@wordpress/i18n";
import './SetupWizard.scss';
import Logo from '../../assets/images/Brand.png'
import Introduction from './steps/Introduction';
import StoreSetup from './steps/StoreSetup';
import CommissionSetup from './steps/CommissionSetup';
import Payments from './steps/Payments';
import Capability from './steps/Capability';
import Ready from './steps/Ready';

const SetupWizard = () => {

	const [currentStep, setCurrentStep] = useState(0);

	const onNext = () => {
		setCurrentStep(currentStep + 1);
	}

    const steps = [
        { component: <Introduction onNext={onNext}/>, title: 'Intro' },
        { component: <StoreSetup onNext={onNext}/>, title: 'Store Setup' },
        { component: <CommissionSetup onNext={onNext}/>, title: 'Commission Setup' },
        { component: <Payments onNext={onNext}/>, title: 'Payments' },
        { component: <Capability onNext={onNext}/>, title: 'Capability' },
        { component: <Ready />, title: 'Ready!' },
    ];


	return (
		<>
		<main className='setup-wizard-main-wrapper'>
			<img src={Logo} alt="Logo" />
			<nav className='step-count'>
				<ul>
					{steps.map((step, index)=>{
						return <li key={index} className={currentStep >= index ? 'active' : ''}>{step.title}</li>
					})}
				</ul>
			</nav>
            <main className='setup-container'>{steps[currentStep].component}</main>
        </main>
		</>

	);
}
export default SetupWizard;