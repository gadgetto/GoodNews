[[!GoodNewsUnSubscription?
    &removeUserData=`1`
]]

[[-
    Samples of other available configuration parameters:
    (Please read the documentation for a full list of parameters)
    
    &removeUserData=`0`
    
    GDPR compliance: If set to `1` it will completely remove all user data from database after unsubscription.
    (This does not remove or deactivate MODX users with MODX user groups assigned or sudo! Those user will only have related GoodNews data removed.)
]]

[[!+authorization_failed:is=`1`:then=`
<div class="container">
    <header>
        <h1>[[++site_name]]</h1>
    </header>
    <main>
        <div class="formerror">
            No subscriptions for this e-mail address found!
        </div>
    </main>
    <footer>
        <p>&copy; Copyright [[++site_name]] | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Terms and Conditions`]]]]">Terms and Conditions</a> | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Privacy Policy`]]]]">Privacy Policy</a></p>
    </footer>
</div>
`]]

[[!+authorization_success:is=`1`:then=`
<div class="container">
    <header>
        <h1>[[++site_name]]</h1>
    </header>
    <main>
        <h2>One-click unsubscription</h2>
        [[!+error.message:notempty=`
            <div class="formerror">
                [[!+error.message]]
            </div>
        `]]
        <p>
            To immediately unsubscribe from our newsletter service please click the <strong>Unsubscribe now</strong> button below.
        </p>
        <form action="[[~[[*id]]]]?sid=[[!+sid]]" method="post">
            <p>
                <button type="submit" name="goodnews-unsubscribe-btn" value="Unsubscribe">Unsubscribe now</button>
            </p>
        </form>
    </main>
    <footer>
        <p>&copy; Copyright [[++site_name]] | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Terms and Conditions`]]]]">Terms and Conditions</a> | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Privacy Policy`]]]]">Privacy Policy</a></p>
    </footer>
</div>
`]]

[[!+unsubscribe_success:is=`1`:then=`
<div class="container">
    <header>
        <h1>[[++site_name]]</h1>
    </header>
    <main>
        <h2>One-click unsubscription</h2>
        <div class="formsuccess">
            Your newsletter subscription for the registered e-mail address [[+email]] was cancelled!
        </div>
        <p>
            We’re sorry your leaving us now and would be pleased to welcome you back soon as a subscriber of our newsletter!
        </p>
        <p>
            If you have further questions, please feel free to contact us under the following e-mail address: 
            <a href="mailto:[[!++emailsender]]">[[!++emailsender]]</a>
        </p>
    </main>
    <footer>
        <p>&copy; Copyright [[++site_name]] | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Terms and Conditions`]]]]">Terms and Conditions</a> | <a href="[[!~[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Privacy Policy`]]]]">Privacy Policy</a></p>
    </footer>
</div>
`]]
