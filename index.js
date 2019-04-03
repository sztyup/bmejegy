// Comes from serializing `Form`
var data = {
    fields: [],
    productLink: 'https://bmejegy.hu/adsfasd',
    password: ''
};

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

    window.cartclear = $('<form action="http://www.bmejegy.hu/fiokom/customer-logout/" method="GET" target="frame"></form>');

    $('body').append(login).append(product).append(cartclear);

    cartclear.submit();

    let frame = $('iframe[name=frame]').get()[0];
    let step = 0;

    // Step 0: clear previous cart
    // Step 1: login form
    // Step 2: submit product to cart
    // Step 3: redirect to checkout
    let listener = () => {
        step++;

        switch (step) {
            case 1:
                login.submit();
                break;
            case 2:
                product.submit();
                break;
            case 3:
                location.href = 'http://bmejegy.hu/penztar';
                break;
        }
    };

    if (frame.attachEvent){
        frame.attachEvent("onload", listener);
    } else {
        frame.onload = listener;
    }
});