[[!GoodNewsUpdateProfile?
    &validate=`
        email:email:required,
        nospam:blank`
    &groupsOnly=`1`
]]
<!--
    Samples of other available configuration parameters:
    (Please read the documentation for a full list of parameters)
    
    &defaultGroups=`1`
    &includeGroups=`4,6`
    &defaultCategories=`3,36,40,48`
    &gongroups.vTextRequired=`Please choose at least one mailing group.`
    &goncategories.vTextRequired=`Please choose at least one category of your interest.`
-->

<div class="container">
    <div class="header">
        <h1>[[++site_name]]</h1>
    </div>
    <div class="main">
        <h2>Edit your user and mailing profile</h2>
        <p>
            You can edit your user and mailing profile here.
        </p>
        <form id="profileform" class="gon-form" action="[[~[[*id]]]]" method="post">
            <input type="hidden" name="nospam" value="[[!+nospam]]">
            [[!+update_success:is=`1`:then=`
                <p class="successMsg">Your user and mailing profile was updated successfully!</p>
            `]]
            [[!+error.message:notempty=`
                <p class="errorMsg">[[!+error.message]]</p>
            `]]
            <fieldset>
                <legend>Personal Data</legend>
                <p class="fieldbg[[!+error.fullname:notempty=` fielderror`]]">
                    <label for="fullname">
                        First and Last Name (optional)
                        [[!+error.fullname]]
                    </label>
                    <input type="text" name="fullname" id="fullname" value="[[!+fullname]]" placeholder="Please enter your first and last name">
                </p>
                <p class="fieldbg[[!+error.email:notempty=` fielderror`]]">
                    <label for="email">
                        E-Mail Address
                        [[!+error.email]]
                    </label>
                    <input type="email" name="email" id="email" value="[[!+email]]" required>
                </p>
                <p class="fieldbg[[!+error.address:notempty=` fielderror`]]">
                    <label for="address">
                        Address
                        [[!+error.street]]
                    </label>
                    <input type="text" name="address" id="address" value="[[!+address]]">
                </p>
                <p class="fieldbg[[!+error.city:notempty=` fielderror`]]">
                    <label for="city">
                        City
                        [[!+error.city]]
                    </label>
                    <input type="text" name="city" id="city" value="[[!+city]]">
                </p>
                <p class="fieldbg[[!+error.zip:notempty=` fielderror`]]">
                    <label for="country">
                        Zip
                        [[!+error.zip]]
                    </label>
                    <input type="text" name="zip" id="zip" value="[[!+zip]]">
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
            [[!+config_error:is=`1`:then=`
            <p class="errorMsg">
                Snippet configuration error: Please check your GoodNewsUpdateProfile Snippet configuration!
            </p>
            `]]
            <p>
                <button type="submit" role="button" name="goodnews-updateprofile-btn" value="Update" class="button green">Update profile</button>
            </p>
        </form>
    </div>
    <div class="aside">
        <p><em>Please note: We respect your privacy and will never give your data to third 
        parties, nor would we ever spam you.</em></p>
    </div>
    <div class="footer">
        <p>&copy; Copyright [[++site_name]]</p>
    </div>
</div>
