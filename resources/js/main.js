jQuery("[data-payhip-products-id]").click(function(e) {
    var target = e.target;
    var element;

    do {
        if (target.getAttribute('data-payhip-products-id')) {
            element = target;
            break;
        }

        target = target.parentElement;

    } while (target.parentElement)

    if (!element) {
        return;
    }

    var data = {};
    data.product = element.getAttribute('data-payhip-products-id');

    if (element.getAttribute('data-payhip-products-message')) {
        data.message = element.getAttribute('data-payhip-products-message');
    }

    Payhip.Checkout.open(data);
});