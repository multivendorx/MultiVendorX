import React from "react";

const Intro = (props) => {
    return (
        <>
        <h1>Welcome to the MultivendorX family!</h1>
        <p>Thank you for choosing MultivendorX! This quick setup wizard will help you configure the basic settings and you will have your marketplace ready in no time. <strong>It’s completely optional and shouldn’t take longer than five minutes.</strong></p>
        <p>If you don't want to go through the wizard right now, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!</p>
        <p className="wc-setup-actions step">
            <a onClick={props.exitWizard} className="button button-large">Not right now</a>
            <a onClick={props.onNext} className="btn red-btn button button-large button-next">Let's go!</a>
        </p>
        </>
    );
}

export default Intro;