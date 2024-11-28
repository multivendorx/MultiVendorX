import React from "react";
import Button from "../../../components/AdminLibrary/Inputs/Button";

const Intro = (props) => {
    return (
        <>
        <h1>Welcome to the MultivendorX family!</h1>
        <p>Thank you for choosing MultivendorX! This quick setup wizard will help you configure the basic settings and you will have your marketplace ready in no time. <strong>It’s completely optional and shouldn’t take longer than five minutes.</strong></p>
        <p>If you don't want to go through the wizard right now, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!</p>
        <p className="wc-setup-actions step">
            <Button inputClass="button button-large" type="button" value="Not right now" onClick={props.exitWizard}/>
            <Button inputClass="btn red-btn button button-large button-next" type="button" value="Let's go!" onClick={props.onNext}/>
        </p>
        </>
    );
}

export default Intro;