import { useState } from 'react';
// import { useFormik } from 'formik';
// import * as Yup from 'yup';
import { Box, Stepper, Step, StepLabel, Grid, TextField, Button } from '@mui/material';

import PersonalInfo from './Steps/PersonalInfo';
import AccountDetails from './Steps/AccountDetails';
import ReviewInfo from './Steps/ReviewInfo';

const steps = [' Account Details', 'Personal Info', 'Review and Submit'];

const Form = () => {
  const [activeStep, setActiveStep] = useState(0);

  const handleNext = () => {    
    setActiveStep((prevActiveStep) => prevActiveStep + 1);
  };
  
  const handleBack = () => {
   setActiveStep((prevActiveStep) => prevActiveStep - 1);
  };
  return (
    <div>
      <div className='firstStep'>
      <div className="mvx-settings-basic-input-class">
			<input
				className="mvx-setting-form-input"
				type="text"
				value={""}
				onChange={(e) => {
					this.onChange(e, target);
				}}

			/>
			<p
				className="mvx-settings-metabox-description"
				dangerouslySetInnerHTML={{ __html:'test' }}
			></p>
		</div>
      </div>
    </div>
  )
}

export default Form;