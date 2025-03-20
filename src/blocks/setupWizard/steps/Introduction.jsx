import React from 'react';

const Introduction = (props) => {
    const { onNext } = props;

    const goToAdminPage = ()=>{
        window.location.href = appLocalizer.adminUrl;
    }
    return (
        <section>
            <h2>Welcome to the MultivendorX family!</h2>
            <p>Thank you for choosing MultivendorX! This quick setup wizard will help you configure the basic settings and you will have your marketplace ready in no time. <strong>It’s completely optional and shouldn’t take longer than five minutes.</strong>'</p>
            <p>If you don't want to go through the wizard right now, you can skip and return to the WordPress dashboard. Come back anytime if you change your mind!</p>
            <p className='wc-setup-actions'>
                <button className='previous-btn' onClick={goToAdminPage}>Not right now</button>
                <button className='next-btn' onClick={onNext}>Let's go!</button>
            </p>
        </section>
    );
};

export default Introduction;