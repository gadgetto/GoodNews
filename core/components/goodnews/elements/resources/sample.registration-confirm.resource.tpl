[[!GoodNewsConfirmSubscription?
    &sendSubscriptionEmail=`1`
    &unsubscribeResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Unsubscribe`]]`
    &profileResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Subscription Update`]]`
    &subscriptionEmailSubject=`Your subscription to our newsletter service at [[++site_name]] was successful!`
]]

[[-
    Please read the documentation for a full list of configuration parameters.
]]

<div class="container">
    <header>
        <h1>[[++site_name]]</h1>
    </header>
    <main>
        <div class="formsuccess">
            You successfully finished your registration on our website!
        </div>
        <p>
            Please login with the credentials you submitted via registration form.
        </p>
        <p>
            <strong>Please note:</strong> If you additionally subscribed to our newsletter service, each mail will contain links to immediately cancel or edit your newsletter profile.
        </p>
        <p>
            <em>Best wishes,<br>
            Your [[++site_name]] Team!</em>
        </p>
    </main>
    <footer>
        <p>&copy; Copyright [[++site_name]] | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Terms and Conditions`]]]]">Terms and Conditions</a> | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Privacy Policy`]]]]">Privacy Policy</a></p>
    </footer>
</div>
