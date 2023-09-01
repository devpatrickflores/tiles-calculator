define([
    "jquery",
    'Magento_Catalog/js/price-utils'
], function ($, priceUtils) {
    'use strict';

    var priceFormat = {
        decimalSymbol: '.',
		pattern: "$%s"
    };
    var customboxprice = $('#pricepersqm').text(); 
    var CalculatorInit = {
        CalculatorInit: function (config) {
            this.config = config;
            if(config && config.isflooring) {
                var self = this;
                this.coverage           = parseFloat(config.coverage);
                this.sqmInputId    = "#flooring_input";
                this.price = config.price * parseFloat(config.coverage);
				this.fullprice = config.price;
                this.specialprice = config.specialprice;
                this.taxPercent = config.tax_percent * 1;
                this.currencyRate = config.currency_rate * 1;
                this.includeTax = config.include_tax * 1;
                this.unitPrice = config.unit_price;
                this.wastage    = config.wastage * 1;
                this.taxDisplay = config.tax_display_type;
                this.newTier = config.new_tier;
                this.sample_price = config.sample_price;

                self.load();
            }



        },
        load : function(){
          	$('.tile-width, .tile-height').on('input', function() {
              calculateAndDisplayArea();
            });

            $('.use-qty').on('click', function() {
               var value = $('.subtotal-tile-area').val();
			   $('#flooring_input').val(value).change();
       		   self.getFromSqmInput();
            });

			$('.tiles-or').on('click', function() {
			   $('.tiles-area-calculator').toggleClass("hide");
            });

            function calculateAndDisplayArea() {
              var width = parseFloat($('.tile-width').val());
              var height = parseFloat($('.tile-height').val());

              if (!isNaN(width) && !isNaN(height)) {
                var area = Math.round(width * height);
                $('.subtotal-tile-area').val(area);
              } else {
                $('.subtotal-tile-area').val('');
              }
            }

            var self = this;
            // hide normal tier prices block if new tier is active
            if(this.newTier == 1){
                $('.prices-tier').hide();
            }

            var sample_price = self.sample_price;

            var sqmInput = $(this.sqmInputId);
            sqmInput.keyup(function(){
                sqmInput.val(sqmInput.val().replace(/[^0-9.]/g,''));
                self.getFromSqmInput();
            });

            $('#qty').keyup(function(){
                $('#qty').val($('#qty').val().replace(/[^0-9]/g,''));
                self.getFromQties()
            });


            if($('#wastage') !== undefined){
                $('#wastage').click(function () {
                    self.getFromSqmInput();
                });
            }

            if($('#free_sample') !== undefined){
                $('#free_sample').click(function () {
                    if ($(this).is(':checked')) {

                        sqmInput.val(null);
                        sqmInput.prop('disabled',true);
                        $('#wastage').prop('disabled', true);
                        $('#qty').val(1);
                        $('#qty').prop('disabled',true);
                        if($('#ActualSquareFeetContainer') != undefined){
                            $('#ActualSquareFeetContainer').html(0);
                        }

                        if($('#CartonPriceContainer') != undefined){
                            $('#CartonPriceContainer').html(priceUtils.formatPrice(0,priceFormat));
                        }
                        if($('#TotalPriceContainer') != undefined){
                            $('#TotalPriceContainer').html(priceUtils.formatPrice(self.sample_price,priceFormat));
                        }
                        if($('#TotalCartonContainer') != undefined){
                            $('#TotalCartonContainer').html(0) ;
                        }
                    }else {
                        sqmInput.prop('disabled',false);
                        $('#wastage').prop('disabled', false);
                        $('#qty').prop('disabled',false);
                        if(parseInt($('#qty').val()) == 0){
                            $('#qty').val(1);
                        }
                        self.getFromQty();
                    }

                });
            }


            if($('#ActualSquareFeetContainer') != undefined){
                $('#ActualSquareFeetContainer').html(this.coverage);
            }
            
         
            if($('#CartonPriceContainer') != undefined){
                $('#CartonPriceContainer').html(priceUtils.formatPrice(this.price,priceFormat));
            }
            if($('#TotalPriceContainer') != undefined){
                $('#TotalPriceContainer').html(priceUtils.formatPrice(this.price,priceFormat)) ;
            }



            $('.product-info-main  .product-info-price  .price-box').each(function(el){

            });
            $('.product-info-main  .product-info-price  .price-notice').each(function(el){

            });


            self.getFromQty();

            if(this.config.options){
                $.each(this.config.options, function (index, option){
                    $(this).click( function(){
                        sqmInput.prop('enabled',true);
                        $('#qty').prop('enabled',true);
                        if(parseInt($('#qty').val()) == 0){
                            $('#qty').val(1);
                        }
                        self.getFromQty();

                    });

                });


            }

            if(this.config.checkbox){
                $.each(this.config.checkbox, function (idx, option){

                    $(this).click(function(){

                        self.getFromSqmInput();

                    });

                });
            }

            //for configurable product selection
            $('.super-attribute-select').each(function (el) {
                $(this).change(function () {
                    self.getFromSqmInput();
                })
            });
            //for configurable product selection

            //for custom option selection
            $('.product-custom-option').each(function (el) {
                $(this).change(function () {

                    self.getFromSqmInput();
                })
            });
            //for custom option selection

        },
        getFormattedNumber : function(n){
            if (!n) return 0;
            return (Math.round(n * 100)/100).toString();
        },
        getFromSqmInput : function(){
            var val = parseFloat($(this.sqmInputId).val());
            var coverage = this.coverage;

            if (!val || val <= 0){
                val = coverage;
            }

            var unitPrice = this.unitPrice;

            var wastage = 0;
            if($('#wastage').is(":checked")){
                wastage = this.wastage;
            }


            if(wastage > 0){
                val = val * (1 + wastage/100);
            }
            var boxes = parseInt(val / coverage);
            var check = val / coverage;
            if (boxes < check) boxes = boxes + 1;
            if (boxes < 1){
                boxes = 1;
            }
            $('#qty').val(boxes);
            this.getFromQty();
        },
        getFromQty : function(){
            var  boxes = parseInt($('#qty').val());
            if (boxes < 1) {
                boxes = 1;
                $('#qty').val(boxes);
            }

            var actual = this.getFormattedNumber(this.coverage * boxes);
            if($('#ActualSquareFeetContainer') != undefined){
                $('#ActualSquareFeetContainer').html(actual);
            }

            this.getPrice();
        },
        getFromQties : function(){
            var  boxes = parseInt($('#qty').val());
            if (boxes < 1) {
                boxes = 1;
                $('#qty').val(boxes);
            }

            var actual = this.getFormattedNumber(this.coverage * boxes);
            if($('#ActualSquareFeetContainer') != undefined){
                $('#ActualSquareFeetContainer').html(actual);
            }

            $(this.sqmInputId).val(actual);
            this.getPrice();
        },
        getPrice : function(){
            var val     = parseInt($('#qty').val());
            if($('#TotalCartonContainer') != undefined){
                $('#TotalCartonContainer').html(val) ;
            }

            var price   = parseFloat(this.price * 1);
            var specialprice   = parseFloat(this.specialprice * 1);
            var coverage = parseFloat(this.coverage * 1);
            var tprice = 0;
            var rate = this.currencyRate;
            var inclTax = this.includeTax;
            var displayTax = this.taxDisplay;
            var config = this.config;

            var unitPrice = this.unitPrice;
            if(unitPrice){
                val = val * coverage;
            }

            if(rate == 0){
                rate = 1;
            }
            var taxPercent = parseFloat(this.taxPercent * 1);

            var includeTax = this.includeTax;



            if (this.config.prices){
                $.each(this.config.prices, function (indx, item){
                   var qty = parseFloat(item.price_qty);
                    if (val >= qty){
                        tprice = parseFloat(item.website_price);
                    }
                });
                if (tprice && (tprice < price)){
                    price = tprice;
                }

            }


            //for configurable product selection
            //get the selected simple product price
            var confPriceAdd = 0;                                      
            $('.super-attribute-select').each(function (el) {
                var selectedPrice = parseFloat($('option:selected', this).attr('price'));
                confPriceAdd = confPriceAdd + selectedPrice;
            });
            //for configurable product selection

            //for custom option selection
            $('select.product-custom-option').each(function (el) {
                var selectedPrice = parseFloat($('option:selected', this).attr('price'));
                confPriceAdd = confPriceAdd + selectedPrice;

            });

            //for custom option selection

         

            if(confPriceAdd) {
                //price = price + confPriceAdd;                              
                specialprice = specialprice + confPriceAdd;
            }
                var boxPrice = rate * price;
                 var specialboxprice = rate * specialprice;
                
                if(unitPrice){
                    boxPrice = rate * price * coverage;
                    specialboxprice = rate * specialprice * coverage;
                   
                    }
                
            //boxPrice = boxPrice + 0.01;                                    
       
            var sqmPrice = rate * (price/coverage);
            
             var specialsqmPrice = rate * (specialprice/coverage);
      
            if(unitPrice){
                sqmPrice = rate * price;
                 specialsqmPrice = rate * specialprice;
               
            }                                  
                                             

            if($('#CartonPriceContainer') != undefined){
                $('#CartonPriceContainer').html(priceUtils.formatPrice(this.price,priceFormat));
            }

            if($('#sqFtActualprice') != undefined){
                $('#sqFtActualprice').html(priceUtils.formatPrice(sqmPrice,priceFormat));
            }

            if($('#PricePerSqMetreContainer') != undefined){
                $('#PricePerSqMetreContainer').html(priceUtils.formatPrice(sqmPrice,priceFormat));
            }


            var added = 0;

            if(this.config.options){
                $.each(this.config.options, function (indx, option){

                    if($(this).checked == true){
                        added = parseFloat(option.price);
                    }

                });
            }

            var total = 0;
            var specialpricetotal = 0;
         
            total = rate * (price + added);
            specialpricetotal = rate * (specialprice + added);
          
            total = total * val;
            specialpricetotal = specialpricetotal * val;
           
            var totalExclTax = 0;
            var totalInclTax = 0;
             var specialpricetotalExclTax = 0;
            var specialpricetotalInclTax = 0;
            if(inclTax == 1){
         
                totalInclTax = total;
                specialpricetotalInclTax = specialpricetotal;
                totalExclTax = total * 100/(taxPercent +100 );
                specialpricetotalExclTax = specialpricetotal * 100/(taxPercent +100 );
            }else {
           
            
                totalInclTax = total * (1+taxPercent/100);
                specialpricetotalInclTax = specialpricetotal * (1+taxPercent/100);
                totalExclTax = total;
                specialpricetotalExclTax = specialpricetotal;
            }
        
        var qty = $('#qty').val();
        
        boxPrice = priceUtils.formatPrice(boxPrice,priceFormat);
        
        var boxPrice = boxPrice.substring(1);
        
        totalInclTax = boxPrice * qty;

            var excludeTax = priceUtils.formatPrice(totalExclTax,priceFormat);
            var includeTax = priceUtils.formatPrice(totalInclTax,priceFormat);
            
            var specialpricetotalExclTax = priceUtils.formatPrice(specialpricetotalExclTax,priceFormat);
            var specialpricetotalInclTax = priceUtils.formatPrice(specialpricetotalInclTax,priceFormat);


            if($('#TotalPriceContainer') != undefined){
                if(displayTax == 2){
                    $('#TotalPriceContainer').html(excludeTax);
                }else {
                    $('#TotalPriceContainer').html(excludeTax);
                }


            }
            if($('#TotalPriceInclTaxContainer') != undefined){
                $('#TotalPriceInclTaxContainer').html(includeTax);
            }
            
         
            //$(".product-info-main .product-info-price .price-box .price").each(function(j) {
                //$(this).html(includeTax);
            //});
          
        
            $(".product-info-main  .product-info-price .price-box .price-including-tax .price").each(function(j) {
                $(this).html(includeTax);
            });
            
            $(".product-info-main .product-info-price .price-box .old-price .price").each(function(j) {
                $(this).html(specialpricetotalInclTax);
            });
            $(".product-info-main  .product-info-price .price-box .price-excluding-tax .price").each(function(j) {
                $(this).html(excludeTax);
            });


            var check = priceUtils.formatPrice(rate * (price + added),priceFormat);
            this.addTierRowClass(check);CartonPriceContainer

        },
        addTierRowClass : function(bs){

            $("#tieredPriceTable tr").css({"background":"none", "font-weight": "normal"});
            $("#tieredPriceTable tr:contains("+bs+")").css({"background":"#FDFCDC", "font-weight": "bold"});

            $(".prices-tier li").css({"background":"none", "font-weight": "normal"});
            $(".prices-tier li:contains("+bs+")").css({"background":"#FDFCDC", "font-weight": "bold"});

        },


    };


    return CalculatorInit;
});
