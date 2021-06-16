<?php
$cart = get_transient( 'wp_easy_event_checkout_' . $_GET['cart'] );
if ( $cart === false ) {
	wp_safe_redirect( site_url() );
	exit;
}

if ( ! empty( $_POST['card'] ) ) {
	$stripe   = new EWP_Event_Stripe_Gateway();
	$checkout = $stripe->createCharge( $_POST, $cart );
}
?>
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Event Checkout</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' rel='stylesheet'>
    <style>

        body {
            background-color: #f5eee7;
            font-family: "Poppins", sans-serif;
            font-weight: 300
        }

        .container {
            height: 100vh
        }

        .card {
            border: none
        }

        .card-header {
            padding: .5rem 1rem;
            margin-bottom: 0;
            background-color: rgba(0, 0, 0, .03);
            border-bottom: none
        }

        .btn-light:focus {
            color: #212529;
            background-color: #e2e6ea;
            border-color: #dae0e5;
            box-shadow: 0 0 0 0.2rem rgba(216, 217, 219, .5)
        }

        .form-control {
            height: 50px;
            border: 2px solid #eee;
            border-radius: 6px;
            font-size: 14px
        }

        .form-control:focus {
            color: #495057;
            background-color: #fff;
            border-color: #039be5;
            outline: 0;
            box-shadow: none
        }

        .input {
            position: relative
        }

        .input i {
            position: absolute;
            top: 16px;
            left: 11px;
            color: #989898
        }

        .input input {
            text-indent: 25px
        }

        .card-text {
            font-size: 13px;
            margin-left: 6px
        }

        .certificate-text {
            font-size: 12px
        }

        .billing {
            font-size: 11px
        }

        .super-price {
            top: 0px;
            font-size: 22px
        }

        .super-month {
            font-size: 11px
        }

        .line {
            color: #bfbdbd
        }

        .free-button {
            background: #1565c0;
            height: 52px;
            font-size: 15px;
            border-radius: 8px
        }

        .payment-card-body {
            flex: 1 1 auto;
            padding: 24px 1rem !important
        }</style>
    <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
    <script type='text/javascript'
            src='https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
    <script>
        $(document).ready(function () {
            $('#cr_no').on('keyup', function (e) {
                var val = $(this).val();
                var newval = '';
                val = val.replace(/\s/g, '');

                // iterate to letter-spacing after every 4 digits
                for (var i = 0; i < val.length; i++) {
                    if (i % 4 == 0 && i > 0) newval = newval.concat(' ');
                    newval = newval.concat(val[i]);
                }

                // format in same input field
                $(this).val(newval);
            });
        });

        function formatExpDate(e) {
            var inputChar = String.fromCharCode(event.keyCode);
            var code = event.keyCode;
            var allowedKeys = [8];
            if (allowedKeys.indexOf(code) !== -1) {
                return;
            }

            event.target.value = event.target.value.replace(
                /^([1-9]\/|[2-9])$/g, '0$1/' // 3 > 03/
            ).replace(
                /^(0[1-9]|1[0-2])$/g, '$1/' // 11 > 11/
            ).replace(
                /^([0-1])([3-9])$/g, '0$1/$2' // 13 > 01/3
            ).replace(
                /^(0?[1-9]|1[0-2])([0-9]{2})$/g, '$1/$2' // 141 > 01/41
            ).replace(
                /^([0]+)\/|[0]+$/g, '0' // 0/ > 0 and 00 > 0
            ).replace(
                /[^\d\/]|^[\/]*$/g, '' // To allow only digits and `/`
            ).replace(
                /\/\//g, '/' // Prevent entering more than 1 `/`
            );
        }
    </script>
</head>
<body class='snippet-body'>
<div class="container d-flex justify-content-center mt-5 mb-5">
    <form method="post" action="">
        <div class="row g-3">
            <h1>Checkout</h1>
            <p>Use the form below to complete the checkout process.</p>

			<?php if ( isset( $checkout->error ) ): ?>
                <div class="alert alert-danger" role="alert">
                    <strong><?php print_r( strtoupper( $checkout->error_message ) ); ?></strong>
                </div>
			<?php endif; ?>
            <div class="col-md-6">

                <h5 class="mt-4">Information</h5>

                <!-- User Input -->
                <div class="card">
                    <div class="card-body payment-card-body">

                        <div class="row mt-3 mb-3">
                            <div class="col-md-6"><span class="font-weight-normal card-text">First Name*</span>
                                <div class="">
                                    <input type="text" name="firstname" class="form-control" placeholder=""
                                           required="required" value="<?php ewp_field( 'firstname' ); ?>">
                                </div>
                            </div>
                            <div class="col-md-6"><span class="font-weight-normal card-text">Last Name*</span>
                                <div class="">
                                    <input type="text" name="lastname" class="form-control" placeholder=""
                                           required="required" value="<?php ewp_field( 'lastname' ); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <span class="font-weight-normal card-text">Email*</span>
                            <div class="">
                                <input type="text" name="email" class="form-control" placeholder="" required="required"
                                       value="<?php ewp_field( 'email' ); ?>">
                            </div>
                        </div>

                        <div class="row mt-3 mb-3">
                            <span class="font-weight-normal card-text">Address*</span>
                            <div class="">
                                <input type="text" name="address" class="form-control" placeholder=""
                                       required="required" value="<?php ewp_field( 'address' ); ?>">
                            </div>
                        </div>

                        <div class="row mt-3 mb-3">
                            <div class="col-md-6"><span class="font-weight-normal card-text">City*</span>
                                <div class="">
                                    <input type="text" name="city" class="form-control" placeholder=""
                                           required="required" value="<?php ewp_field( 'city' ); ?>">
                                </div>
                            </div>
                            <div class="col-md-3"><span class="font-weight-normal card-text">State*</span>
                                <div class="">
                                    <input type="text" name="state" class="form-control" placeholder="" maxlength="2"
                                           required="required" value="<?php ewp_field( 'state' ); ?>">
                                </div>
                            </div>
                            <div class="col-md-3"><span class="font-weight-normal card-text">State*</span>
                                <div class="">
                                    <input type="number" name="zipcode" class="form-control" placeholder=""
                                           maxlength="5" required="required" value="<?php ewp_field( 'zipcode' ); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3 mb-3">
                            <span class="font-weight-normal card-text">Name of Guests*</span>
                            <div class="">
                                <textarea type="text" name="name_of_guests" class="form-control" placeholder=""
                                          required="required"
                                          value="<?php ewp_field( 'name_of_guests' ); ?>"><?php ewp_field( 'name_of_guests' ); ?></textarea>
                            </div>
                        </div>


                    </div>
                </div>
                <!-- /User Input -->

                <h5 class="mt-4">Payment Method</h5>
                <div class="card mb-3">
                    <div class="accordion" id="accordionExample">

                        <div class="card">
                            <div class="card-header p-0">
                                <h2 class="mb-0">
                                    <button onclick="return false;"
                                            class="btn btn-light btn-block text-left p-3 rounded-0">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span>Credit card</span>
                                            <div class="icons">
                                                <img src="https://i.imgur.com/2ISgYja.png" width="30">
                                                <img src="https://i.imgur.com/W1vtnOV.png" width="30">
                                                <img src="https://i.imgur.com/35tC99g.png" width="30">
                                                <img src="https://i.imgur.com/2ISgYja.png" width="30">
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                 data-parent="#">
                                <div class="card-body payment-card-body">
                                    <div class="row mt-0 mb-3">
                                        <span class="font-weight-normal card-text">Name of Card*</span>
                                        <div class="">
                                            <input type="text" class="form-control" name="card[name]" placeholder=""
                                                   required="required" <?php ewp_field( 'card[name]' ); ?>>
                                        </div>
                                    </div>
                                    <span class="font-weight-normal card-text">Card Number*</span>
                                    <div class="input"><i class="fa fa-credit-card"></i>
                                        <input type="text" class="form-control" name="card[num]"
                                               placeholder="0000 0000 0000 0000" id="cr_no" required="required"
											<?php ewp_field( 'card[num]' ); ?>>
                                    </div>
                                    <div class="row mt-3 mb-3">
                                        <div class="col-md-6"><span
                                                    class="font-weight-normal card-text">Expiry Date*</span>
                                            <div class="input"><i class="fa fa-calendar"></i>
                                                <input type="text" class="form-control" name="card[exp]"
                                                       placeholder="MM/YY" id="card_exp" required="required"
                                                       onkeyup="formatExpDate(event)">
                                            </div>
                                        </div>
                                        <div class="col-md-6"><span class="font-weight-normal card-text">CVC/CVV*</span>
                                            <div class="input"><i class="fa fa-lock"></i>
                                                <input type="text" class="form-control" name="card[cvc]"
                                                       placeholder="000" maxlength="999" required="required">
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-muted certificate-text"><i class="fa fa-lock"></i> Your transaction is secured with ssl certificate</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h5 class="mt-4">Summary</h5>
                <div class="card">
                    <div class="d-flex justify-content-between p-3">
                        <div class="d-flex flex-column"><strong>Donation Amount </strong>
                            <a href="#" class="billing">for <?php print get_the_title( $cart['event'] ); ?></a>
                        </div>
                        <div class="mt-1">
                            <sup class="super-price">$<?php print ewp_event_cart_total(); ?></sup>
                            <span class="super-month">...</span>
                        </div>
                    </div>
                    <hr class="mt-0 line">
                    <div class="p-3">
						<?php foreach ( $cart['contents'] as $item ): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?php print $item['name']; ?> <small>(x<?php print $item['tickets_sold']; ?>)</small></span>
                                <span><?php print ewp_event_price( $item['total'] ); ?></span>
                            </div>
						<?php endforeach; ?>
                        <!--<div class="d-flex justify-content-between mb-2"><span>Refferal Bonouses</span> <span>-$2.00</span>
						</div>
						<div class="d-flex justify-content-between"><span>Vat <i class="fa fa-clock-o"></i></span> <span>-20%</span>
						</div>-->
                    </div>
                    <hr class="mt-0 line">
                    <div class="p-3 d-flex justify-content-between">
                        <div class="d-flex flex-column"><small>Today you pay</small>
                            <!--<small>Donation in the amount of</small>-->
                        </div>
                        <span>$<?php print ewp_event_cart_total(); ?></span>
                    </div>
                    <div class="p-3">
                        <input type="hidden" name="sub_total" value="<?php print $cart['total']; ?>"/>
                        <button type="submit" class="btn btn-primary btn-block free-button"
                                onclick="return confirm('You are about to checkout for $<?php print ewp_event_cart_total(); ?>')">
                            CHECKOUT
                        </button>
                        <div class="text-center mt-3">
                            <a href="<?php echo get_the_permalink( $cart['event'] ); ?>">Go back to Event Details</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3 text-center">
                    <hr/>
                    Powered By <strong><a href="#">Easy WP Events</a></strong>
                </div>
            </div>

        </div>
    </form>
</div>
</body>
</html>