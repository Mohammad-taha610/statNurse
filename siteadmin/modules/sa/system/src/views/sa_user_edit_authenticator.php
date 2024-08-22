<div id="edit-google-authenticator" class="tab-pane">
    <div class="form-group">
        <h4 class="header blue bolder smaller">Google Authenticator</h4>

        <div class="row">
            <div class="col-md-12">
                Go to the Google Play store or the Apple App Store to get the Google Authenticator App.
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                To register the app please follow the instructions below:
            </div>
        </div>

        <div class="row" style="margin-top: 20px">
            <div class="col-md-12">

                <?php if ($google_auth_secret) { ?>
                    A code has been issued.  To invalidate the code, revoke all registered apps and issue a new code click "Issue A New Code". Then scan the QR code or enter the code in the Google Authenticator App.  <br /><br />
                <?php } else { ?>
                    To register the Google Authenticator, click "Issue A New Code", and then scan the QR code or enter the code in the Google Authenticator App. <br /><br />
                <?php } ?>

                <button type="button" id="btn-issue-ga-code" class="btn btn-primary">Issue A New Code</button>

                <div id="ga_information" style="margin-top: 25px"></div>
            </div>
        </div>
    </div>
</div>