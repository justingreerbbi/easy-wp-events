<?php
if ( isset( $_POST['payment-submit'] ) ) {
	$stripe = new \Stripe\StripeClient( "sk_test_P9tqT4FFC1XEbwOV8VB4U1cw" );

}
$cart = get_transient( 'wp_easy_event_checkout_' . $_GET['cart'] );
?>
<!DOCTYPE html>
<html>
<head>
	<?php wp_head(); ?>
    <style>
        .wrapper {
            background: #f5f6fa;
        }

        .cart-contents {
            font-size: 0.8em;
        }

        .cart-total {
            font-size: 1.2em;
        }
    </style>
    <script>
        jQuery(document).ready(function () {
            //For Date formatted input
            var expDate = document.getElementById('exp');
            expDate.onkeyup = function (e) {
                if (this.value == this.lastValue) return;
                var caretPosition = this.selectionStart;
                var sanitizedValue = this.value.replace(/[^0-9]/gi, '');
                var parts = [];

                for (var i = 0, len = sanitizedValue.length; i < len; i += 2) {
                    parts.push(sanitizedValue.substring(i, i + 2));
                }
                for (var i = caretPosition - 1; i >= 0; i--) {
                    var c = this.value[i];
                    if (c < '0' || c > '9') {
                        caretPosition--;
                    }
                }
                caretPosition += Math.floor(caretPosition / 2);

                this.value = this.lastValue = parts.join('/');
                this.selectionStart = this.selectionEnd = caretPosition;
            }
        });
    </script>
</head>
<body>

<div class="container mt-5 mb-3">
    <div class="row">
        <div class="col-12">
            <h2>Checkout Form</h2>
            <p>Use the form below to complete your checkout.</p>
        </div>
    </div>
</div>

<div class="container p-4 wrapper">
    <div class="row">
        <div class="col-8">
            <form action="" id="payment-form">

                <div class="row">
                    <div class="col-7">
                        <h3 class="mb-3">Billing Address</h3>

                        <label for="fname">Full Name</label>
                        <input type="text" id="fname" name="fullname" class="form-control"/>

                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control"/>

                        <label for="adr">Address</label>
                        <input type="text" id="adr" name="address" class="form-control"/>

                        <label for="city">City</label>
                        <input type="text" id="city" name="city" class="form-control"/>

                        <div class="row">
                            <div class="col">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" class="form-control">
                            </div>
                            <div class="col">
                                <label for="zip">Zip</label>
                                <input type="number" id="zip" name="zip" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="col-5">
                        <h3 class="mb-3">Payment</h3>

                        <label for="fname">Accepted Cards</label>
                        <div class="icon-container">
                            <i class="fa fa-cc-visa" style="color:navy;"></i>
                            <i class="fa fa-cc-amex" style="color:blue;"></i>
                            <i class="fa fa-cc-mastercard" style="color:red;"></i>
                            <i class="fa fa-cc-discover" style="color:orange;"></i>
                        </div>

                        <label for="cname">Name on Card</label>
                        <input type="text" id="card[name]" name="card[name]" class="form-control"/>
                        <label for="ccnum">Credit card number</label>
                        <input type="text" id="" name="card[number]" class="form-control">

                        <div class="row">
                            <div class="col">
                                <label for="expyear">Exp Year</label>
                                <input type="text" id="exp" name="expdate" placeholder="MM/YY" minlength="5"
                                       maxlength="5">
                            </div>
                            <div class="col">
                                <label for="cvv">CVV</label>
                                <input type="password" id="cvv" name="card[cvv]" class="form-control" maxlength="3">
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary mt-3">CHECKOUT</button>
                        </div>

                    </div>
                </div>


            </form>
        </div>


        <div class="col-4">
            <div class="container cart-contents">
                <h4>Cart
                    <span class="price" style="color:black">
                      <i class="fa fa-shopping-cart"></i>
                      <b><?php print count( $cart['contents'] ); ?></b>
                    </span>
                </h4>
                <ul>
					<?php
					foreach ( $cart['contents'] as $item ) {
						print '<li><strong>' . $item['name'] . '</strong> (' . $item['tickets_sold'] . ')</li>';
					};
					?>
                </ul>
                <hr>
                <p class="cart-total">Total <strong><?php print $cart['total']; ?></strong></p>
            </div>
        </div>
    </div>
</div>

</div>

</body>
</html>