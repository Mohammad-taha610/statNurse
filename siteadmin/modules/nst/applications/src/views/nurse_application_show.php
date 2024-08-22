@extends('master')

<script src="https://unpkg.com/vee-validate@3.0.0/dist/vee-validate.full.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<?php
use nst\member\NstMember;
use nst\member\NurseApplication;
use sa\system\saUser;

// decrypt social security number - https://www.php.net/openssl_decrypt
$nurse_json = json_decode($application['part_one']['nurse']);
/** @var NstMember $member */
if ($member) {
    /** @var saUser $user */
    $user = $member->getUsers()[0];
    $ss = $nurse_json->socialsecurity_number;
    $cipher = "AES-128-CTR";
    $key = $user->getUserKey();
    $decrypted_ss = openssl_decrypt($ss, $cipher, $key, 0, ord($key));
    $nurse_json->socialsecurity_number = $decrypted_ss;
    $application['part_one']['nurse'] = json_encode($nurse_json);
}
// end: decrypt social security number
?>
<script>
    window.form = <?= json_encode($application['part_one']) ?>;
    window.formTwo = <?= json_encode($application['part_two']) ?>;
    window.application_status = '<?= $application_status; ?>';
</script>

@section('site-container')
<div data-app>
    <v-app>
        <deprecated-nurse-app></deprecated-nurse-app>
    </v-app>
</div>

@asset::/siteadmin/system/js/nst_overlay.js
@asset::/applications/js/deprecated-nurse-app.js
@asset::/themes/nst/js/vue-mask.min.js
@show