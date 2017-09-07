[[!GoodNewsSubscription?
    &submittedResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Subscription Mail Sent`]]`
    &activationResourceId=`[[!GoodNewsGetResourceID? &pagetitle=`GoodNews Subscription Confirm`]]`
    &activationEmailTpl=`sample.GoodNewsActivationEmailTpl`
    &activationEmailSubject=`Thank you for joining our newsletter service at [[++site_name]]`
    &validate=`
        email:email:required,
        nospam:blank`
    &defaultGroups=`1`
]]
<!--
    Samples of other available configuration parameters:
    (Please read the documentation for a full list of parameters)
    
    &groupsOnly=`1`
    &includeGroups=`4,6`
    &defaultCategories=`3,36,40,48`
    &gongroups.vTextRequired=`Please choose at least one mailing group.`
    &goncategories.vTextRequired=`Please choose at least one category of your interest.`
-->

<div class="subscriptionbox">
    <h3>Subscribe to our newsletter</h3>
    <p>
        Sign up for our occasional newsletter and get news and updates 
        delivered to your inbox. And don't worry, you can unsubscribe instantly 
        or change your preferences at any time.
    </p>
    <form id="profileform" action="[[~[[*id]]]]" method="post">
        <input type="hidden" name="nospam" value="[[!+nospam]]">
        [[!+error.message:notempty=`
            <p class="errorMsg">[[!+error.message]]</p>
        `]]
        <fieldset>
            <legend>Personal Data</legend>
            <p class="fieldbg[[!+error.email:notempty=` fielderror`]]">
                <label for="email">
                    E-Mail Address
                    [[!+error.email]]
                </label>
                <input type="email" name="email" id="email" value="[[!+email]]" placeholder="Please enter your e-mail address" required>
            </p>
            <p class="fieldbg[[!+error.fullname:notempty=` fielderror`]]">
                <label for="fullname">
                    First and Last Name (optional)
                    [[!+error.fullname]]
                </label>
                <input type="text" name="fullname" id="fullname" value="[[!+fullname]]" placeholder="Please enter your first and last name">
            </p>
        </fieldset>
        [[!+fields_hidden:is=`1`:then=`[[!+grpcatfieldsets]]`:else=`
            <fieldset>
                <legend>Newsletter Topics</legend>
                <p class="fieldbg">
                    <label class="singlelabel">
                        Please choose the newsletter topics you are interested in.
                        [[!+error.gongroups]]
                        [[!+error.goncategories]]
                    </label>
                    <input type="hidden" name="gongroups[]" value="">
                    <input type="hidden" name="goncategories[]" value="">
                </p>
                [[!+grpcatfieldsets]]
            </fieldset>
        `]]
        <p>
            <button type="submit" role="button" name="goodnews-subscription-btn" value="Subscribe">Subscribe now</button>
        </p>
    </form>
    <p>
        <em>Please note: We respect your privacy and will never give your data to third 
        parties, nor would we ever spam you.</em>
    </p>
</div>
    