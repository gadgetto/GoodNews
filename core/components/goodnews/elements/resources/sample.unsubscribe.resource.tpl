[[!GoodNewsUnSubscription]]
<!--
    Samples of other available configuration parameters:
    (Please read the documentation for a full list of parameters)
    
    &removeUserData=`1`
-->

[[!+authorization_failed:is=`1`:then=`
<div class="container">
    <div class="header">
        <h1>[[++site_name]]</h1>
    </div>
    <div class="main">
        <p class="errorMsg">No subscriptions for this e-mail address found!</p>
    </div>
    <div class="footer">
        <p>&copy; Copyright [[++site_name]]</p>
    </div>
</div>
`]]

[[!+authorization_success:is=`1`:then=`
<div class="container">
    <div class="header">
        <h1>[[!++site_name]]</h1>
    </div>
    <div class="main">
        <h2>One-click unsubscription</h2>
        [[!+error.message:notempty=`
            <p class="errorMsg">[[!+error.message]]</p>
        `]]
        <p>
            To immediately unsubscribe from our newsletter service please click the <strong>Unsubscribe now</strong> button below.
        </p>
        <form id="profileform" class="gon-form" action="[[~[[*id]]]]?sid=[[!+sid]]" method="post">
            <p>
                <button type="submit" role="button" name="goodnews-unsubscribe-btn" value="Unsubscribe" class="button green">Unsubscribe now</button>
            </p>
        </form>
    </div>
    <div class="footer">
        <p>&copy; Copyright [[!++site_name]]</p>
    </div>
</div>
`]]

[[!+unsubscribe_success:is=`1`:then=`
<div class="container">
    <div class="header">
        <h1>[[++site_name]]</h1>
    </div>
    <div class="main">
        <h2>One-click unsubscription</h2>
        <p class="successMsg">Your newsletter subscription for the registered e-mail address [[+email]] was cancelled!</p>
        <p>
            We’re sorry your leaving us now and would be pleased to welcome you back soon as a subscriber of our newsletter!
        </p>
        <p>
            If you have further questions, please feel free to contact us under the following e-mail address: 
            <a href="mailto:[[!++emailsender]]">[[!++emailsender]]</a>
        </p>
    </div>
    <div class="footer">
        <p>&copy; Copyright [[++site_name]]</p>
    </div>
</div>
`]]
