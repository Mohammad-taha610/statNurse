<?php
/**
 * @var string      $postRoute
 * @var string      $street_one
 * @var string      $street_two
 * @var string      $city
 * @var saState     $state
 * @var saState[]   $states
 * @var saCountry   $country
 * @var saCountry[] $countries
 * @var string      $postal_code
 * @var boolean     $is_primary
 * @var boolean     $is_active
 * @var string      $type
 */
use sa\system\saCountry;
use sa\system\saState;
use sacore\utilities\url;

?>

@asset::/member/profile/css/stylesheet.css

<?php
$notify = new \sacore\utilities\notification();
$notify->showNotifications();
?>

<div class="profile-address-edit">
    <h1>Manage Address</h1>

    <div class="form-group text-right">
        <a href="@url('member_addresses')" class="btn btn-primary">My Addresses</a>
    </div>

    <form role="form" method="post" action="<?= $postRoute ?>">

        <!-- street -->
        <div class="form-group">
            <label for="street">Street</label>
            <input type="text" name="street_one" value="<?= $street_one ?>" title="Street" class="form-control">
        </div>
        <!-- end street -->

        <!-- street two -->
        <div class="form-group">
            <label for="street_two">Unit, Appartment No, etc.</label>
            <input type="text" name="street_two" value="<?= $street_two ?>" title="Street Two" class="form-control">
        </div>
        <!-- end street two -->

        <!-- country -->
        <div class="form-group">
            <label for="country">Country</label>
            <select name="country" id="country" class="form-control">
                <option value="">Please select a country...</option>
                <?php foreach ($countries as $countryitem) { ?>
                    <?php if (!empty($addressId)) {
                        $isSelected = ($countryitem->getId() == $country->getId()) ? 'selected="selected"' : '';
                    }
                    ?>
                    <option <?=$isSelected?>
                        value="<?= $countryitem->getId() ?>">
                        <?= $countryitem->getName() ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <!-- end country -->

        <div class="row">
            <div class="col-md-6">
                <!-- city -->
                <div class="form-group">
                    <label for="city"> City</label>
                    <input type="text" name="city" value="<?= $city ?>" class="form-control" title="city">
                </div>
                <!-- end city -->
            </div>
            <div class="col-md-6">
                <!-- state -->
                <div class="form-group">
                    <label for="state"> State/Province</label>

                    <select name="state" id="state" title="State" class="form-control">
                        <option value="">Please select a state/province...</option>
                        <?php
                        if (!empty($states)) {
                            foreach ($states as $stateItem) {
                                 if (!empty($addressId)) {
                                    $isSelected = ($stateItem->getId() == $state->getId()) ? 'selected="selected"' : '' ;
                                }

                                ?>
                                <option <?= $isSelected ?>
                                    value="<?= $stateItem->getId() ?>"><?= $stateItem->getName() ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <!-- end state -->
            </div>
        </div>

        <!-- postal code -->
        <div class="form-group">
            <label for="postal_code">Postal Code</label>
            <input type="text" name="postal_code" class="form-control" value="<?= $postal_code ?>" title="Postal Code">
        </div>
        <!-- end postal code -->

        <!-- Type -->
        <div class="form-group">
            <p>What type of address is this?</p>

            <label>
                <input type="radio" name="type" value="personal" <?= $type == 'personal' ? 'checked' : '' ?>> Home
            </label>

            <label>
                <input type="radio" name="type" value="work" <?= $type == 'work' ? 'checked' : '' ?>> Work
            </label>

            <label>
                <input type="radio" name="type" value="secondary" <?= $type == 'secondary' ? 'checked' : '' ?>> Secondary
            </label>

            <label>
                <input type="radio" name="type" value="other" <?= $type == 'other' ? 'checked' : '' ?>> Other
            </label>
        </div>
        <!-- end type -->

        <!-- is primary -->
        <div class="form-group">
            <label for="is_primary">
                <input type="checkbox" name="is_primary" value="1" title="Primary Address" <?= $is_primary ? 'checked' : '' ?>> This is my primary address
            </label>
        </div>
        <!-- end is primary -->

        <!-- is active -->
        <div class="form-group">
            <label for="is_active">
                <input type="checkbox" name="is_active" value="1" title="Activate" <?= $is_active ? 'checked' : '' ?>> Activate this address
            </label>
        </div>
        <!-- end is active -->


        <div class="form-actions">
            <div class="col-md-offset-3 col-md-9">
                <button class="btn btn-info" type="submit">
                    <i class="fa fa-save bigger-110"></i>Save
                </button>

                <button class="btn" type="reset">
                    <i class="fa fa-undo bigger-110"></i>
                    Reset
                </button>
            </div>
        </div>

    </form>
</div>

@view::_member_profile_footer