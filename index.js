$('#startPayment').on('click', () => {
    // Password form filler
    window.login = $("<form action='http://www.bmejegy.hu/wp-login.php?action=postpass' method='POST' target='frame'></form>");
    login.append('<input type="hidden" name="post_password" value="' + data.password + '">');
    login.append('<input type="hidden" name="Submit" value="Enter">');

    // Product page form
    window.product = $('<form action="' + data.productLink + '" method="POST" target="frame"></form>');
    $.each(data.fields, (field, value) => {
        product.append('<input type="hidden" name="' + field + '" value="' + value + '">');
    });

    $('body').append(login).append(product);

    // Step 0: login
    // Step 1: submit product to cart
    // Step 2: redirect to checkout
    login.submit();

    let step = 0;

    let frameOnload = () => {
        step++;

        if (step === 1) {
            product.submit();
        } else {
            location.href = 'http://bmejegy.hu/penztar';
        }
    };

    let frame = $('iframe[name=frame]').get()[0];

    if (frame.attachEvent){
        frame.attachEvent("onload", frameOnload);
    } else {
        frame.onload = frameOnload;
    }
});