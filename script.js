

//CURRENCY.JS DO NOT TOUCH
(function(e,c){"object"===typeof exports&&"undefined"!==typeof module?module.exports=c():"function"===typeof define&&define.amd?define(c):e.currency=c()})(this,function(){function e(b,a){if(!(this instanceof e))return new e(b,a);a=Object.assign({},m,a);var f=Math.pow(10,a.precision);this.intValue=b=c(b,a);this.value=b/f;a.increment=a.increment||1/f;a.groups=a.useVedic?n:p;this.s=a;this.p=f}function c(b,a){var f=2<arguments.length&&void 0!==arguments[2]?arguments[2]:!0,c=a.decimal,g=a.errorOnInvalid;
    var d=Math.pow(10,a.precision);var h="number"===typeof b;if(h||b instanceof e)d*=h?b:b.value;else if("string"===typeof b)g=new RegExp("[^-\\d"+c+"]","g"),c=new RegExp("\\"+c,"g"),d=(d*=b.replace(/\((.*)\)/,"-$1").replace(g,"").replace(c,"."))||0;else{if(g)throw Error("Invalid Input");d=0}d=d.toFixed(4);return f?Math.round(d):d}var m={symbol:"$",separator:",",decimal:".",formatWithSymbol:!1,errorOnInvalid:!1,precision:2,pattern:"!#",negativePattern:"-!#"},p=/(\d)(?=(\d{3})+\b)/g,n=/(\d)(?=(\d\d)+\d\b)/g;
    e.prototype={add:function(b){var a=this.s,f=this.p;return e((this.intValue+c(b,a))/f,a)},subtract:function(b){var a=this.s,f=this.p;return e((this.intValue-c(b,a))/f,a)},multiply:function(b){var a=this.s;return e(this.intValue*b/Math.pow(10,a.precision),a)},divide:function(b){var a=this.s;return e(this.intValue/c(b,a,!1),a)},distribute:function(b){for(var a=this.intValue,f=this.p,c=this.s,g=[],d=Math[0<=a?"floor":"ceil"](a/b),h=Math.abs(a-d*b);0!==b;b--){var k=e(d/f,c);0<h--&&(k=0<=a?k.add(1/f):k.subtract(1/
            f));g.push(k)}return g},dollars:function(){return~~this.value},cents:function(){return~~(this.intValue%this.p)},format:function(b){var a=this.s,c=a.pattern,e=a.negativePattern,g=a.formatWithSymbol,d=a.symbol,h=a.separator,k=a.decimal;a=a.groups;var l=(this+"").replace(/^-/,"").split("."),m=l[0];l=l[1];"undefined"===typeof b&&(b=g);return(0<=this.value?c:e).replace("!",b?d:"").replace("#","".concat(m.replace(a,"$1"+h)).concat(l?k+l:""))},toString:function(){var b=this.s,a=b.increment;return(Math.round(this.intValue/
            this.p/a)*a).toFixed(b.precision)},toJSON:function(){return this.value}};return e});



//CODE I WROTE STARTS HERE
(function($) {

    $(document).ready(function() {

        //defining variables
        let ge = $("#graphicelements");
        let geform = $("#step2-graphicelements");
        let dy = $("#designyourself");
        let dyform = $("#step2-designyourself");
        let ordersummary = $("#price-summary");

        //updates displays initial price
        updatePrice();

        //when user chooses to design their own
        dy.click(function () {
            ordersummary.removeClass("hidden");
            dyform.removeClass("hidden");


            if ( ! geform.hasClass("hidden") ) {
                geform.addClass("hidden");
            }

        });
        //when user chooses ge designer
        ge.click(function() {

            geform.removeClass("hidden");

            if ( ! dyform.hasClass("hidden") ) {
                dyform.addClass("hidden");
               ordersummary.addClass("hidden");
            }
        });

        //every time the value of a form in the design yourself function changes, this function is called
        $(".influences-price").change(function() {
            //TODO: Validate that a number >= 0 was entered

            updatePrice();

        });

        //updates price on the price summary and sends it to the server
        function updatePrice() {

            //assigning various calculated prices to variables
            const baseprice = basePrice();
            const colorprice = colorPrice();
            const quantity = getQuantity();
             const bulkprice = getBulkPrice();
            const totalprice = (((baseprice.add(colorprice)).multiply(quantity)).subtract(bulkprice));

            //updating price summary
            $("#base-price").text(baseprice);
             $("#color-price").text(colorprice);
             $("#bulk-discount").text(bulkprice);
             $("#quantity-price").text(quantity);
             $("#total-price").text(totalprice);

            //TODO send total price to the server

        }

        //functions to calculate various parts of the total price
        function basePrice() {
            return currency( $(".woocommerce-Price-amount:eq(1)").first().text().slice(1) );
        }

        function colorPrice() {
            const pricePerColor = currency(1.00);
            return ( pricePerColor.multiply( parseInt( $("#colors-front").val() ) + parseInt( $("#colors-back").val() ) ) );
        }

        function getQuantity() {

            //TODO send individual quanitites to server
            let sum = 0;
            $(".bulk-quantity").each(function() {
                sum += parseInt($(this).val());
            });
            return sum;
        }

        function getBulkPrice() {
            //TODO: determine formula for actual bulk discount calculation
            return  currency((getQuantity() * 1));
        }


    });







})( jQuery );


