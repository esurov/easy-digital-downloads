!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=27)}([function(e,t){e.exports=jQuery},function(e,t,n){"use strict";(function(e){n.d(t,"a",(function(){return r}));var r=function(t){e(t)}}).call(this,n(0))},function(e,t,n){"use strict";n.d(t,"a",(function(){return o}));var r={disable_search_threshold:13,search_contains:!0,inherit_select_classes:!0,single_backstroke_delete:!1,placeholder_text_single:edd_vars.one_option,placeholder_text_multiple:edd_vars.one_or_more_option,no_results_text:edd_vars.no_results_text},o=function(e){var t=r;return e.data("search-type")&&delete t.disable_search_threshold,t}},function(e,t){e.exports=function(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}},,function(e,t,n){"use strict";var r=Backbone.Model.extend({defaults:{id:"",name:"",priceId:0,status:"",quantity:1,amount:0,subtotal:0,discount:0,tax:0,total:0,dateCreated:"",dateModified:"",eddUid:""},idAttribute:"eddUid"}),o=Backbone.Model.extend({defaults:{isAdding:!1,hasQuantity:!1,hasTax:!1,items:[]}});n.d(t,"a",(function(){return r})),n.d(t,"b",(function(){return o}))},function(e,t,n){"use strict";(function(e){function r(){var t=0,n=0,r=0,o=0,a=0;e(".orderitems tbody tr:not(.no-items)").each((function(){var n,r=e(this),i=1,d=0;n=parseFloat(r.find(".amount input").val()),r.find(".quantity").length&&(i=parseFloat(r.find(".quantity input").val())),t+=n*i,r.find(".tax").length&&(d=parseFloat(r.find(".tax input").val()),isNaN(d)||edd_vars.taxes_included||(n+=d,o+=d)),a+=n*i})),e(".orderadjustments tbody tr:not(.no-items)").each((function(){var t=e(this),i=0;switch(t.data("adjustment")){case"credit":i=parseFloat(t.find("input.credit-amount",t).val()),r+=i,a-=i;break;case"discount":i=parseFloat(t.find("input.discount-amount",t).val()),"percent"===t.find("input.discount-type").val()?e(".orderitems tbody tr:not(.no-items)").each((function(){var t=e(this).find(".amount .value").text(),r=1;e(this).find(".quantity").length&&(r=parseFloat(e(this).find(".quantity").text()));var d=parseFloat((t*=r)/100*i);if(e(this).find(".tax").length){var s=parseFloat(e(this).find(".tax .value").text()),c=parseFloat(s/100*i);o-=c,a-=c}n+=d,a-=d})):(r+=i,a-=i)}})),isNaN(a)&&(a=0),isNaN(t)&&(t=0),isNaN(o)&&(o=0),isNaN(n)&&(n=0),isNaN(r)&&(r=0),e(".edd-order-subtotal .value").html(t.toFixed(edd_vars.currency_decimals)),e(".edd-order-discounts .value").html(n.toFixed(edd_vars.currency_decimals)),e(".edd-order-adjustments .value").html(r.toFixed(edd_vars.currency_decimals)),e(".edd-order-taxes .value").html(o.toFixed(edd_vars.currency_decimals)),e(".edd-order-total .value").html(a.toFixed(edd_vars.currency_decimals))}n.d(t,"a",(function(){return r}))}).call(this,n(0))},,,,,function(e,t,n){"use strict";(function(e){n.d(t,"a",(function(){return c}));var r=n(3),o=n.n(r),a=n(5),i=n(2);function d(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function s(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?d(Object(n),!0).forEach((function(t){o()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):d(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}var c=wp.Backbone.View.extend({el:"#edd-admin-order-add-item-dialog",template:wp.template("edd-admin-order-form-add-item"),events:{"submit form":"onAddItem","change #download":"onChangeDownload","change #quantity":"onChangeQuantity","change #amount":"onChangeAmount","change #tax":"onChangeTax","change #subtotal":"onChangeSubtotal","change #auto-calculate":"onAutoCalculateToggle"},isAdjustingManually:!1,formatCurrency:function(e){return Math.round(e)},initialize:function(){e("#edd-admin-order-add-item-dialog").dialog({position:{my:"top center",at:"center center-25%"},width:"350px",modal:!0,resizable:!1,draggable:!1,autoOpen:!1}),this.item=new a.a({amountManual:0,taxManual:0,subtotalManual:0}),this.listenTo(this.item,"change:auto",this.render),this.listenTo(this.item,"change:download",this.render),this.listenTo(this.options.config,"change:hasTax",this.render),this.listenTo(this.options.config.get("items"),"add",this.closeDialog)},prepare:function(){var e=this.item,t=this.isAdjustingManually,n=this.formatCurrency,r=this.options.config;return s({},e.toJSON(),{config:s({},r.toJSON(),{isAdjustingManually:t}),amountCurrency:n(e.get("amount")*e.get("quantity")),taxCurrency:n(e.get("tax")*e.get("quantity")),subtotalCurrency:n(e.get("subtotal")*e.get("quantity"))})},render:function(){return wp.Backbone.View.prototype.render.apply(this),this.$el.find(".edd-select-chosen").each((function(){e(this).chosen(s({},Object(i.a)(e(this)),{width:"100%"}))})),this},onChangeDownload:function(e){var t=this,n=e.target,r=n.options,o=n.selectedIndex,a=this.options.config,i=r[o],d=i.value,s=d,c=0,u=s.split("_");s=parseInt(u[0]),u[1]&&(c=parseInt(u[1]));var l=i.text,f=this.item.get("quantity"),p=!1!==a.get("hasTax")?a.get("hasTax").country:"",h=!1!==a.get("hasTax")?a.get("hasTax").region:"";wp.ajax.send("edd-admin-order-get-item-amounts",{data:{id:s,priceId:c,quantity:f,country:p,region:h},success:function(e){var n=e.amount,r=e.subtotal,o=e.tax;t.item.set({eddUid:d,id:s,priceId:c,name:l,amount:n,tax:o,subtotal:r,amountManual:n,taxManual:o,subtotalManual:r}),t.item.trigger("change:download")}})},onChangeQuantity:function(e){this.item.set("quantity",parseInt(e.target.value))},onChangeAmount:function(e){this.item.set("amountManual",parseFloat(e.target.value))},onChangeTax:function(e){this.item.set("taxManual",parseFloat(e.target.value))},onChangeSubtotal:function(e){this.item.set("subtotalManual",parseFloat(e.target.value))},onAutoCalculateToggle:function(e){e.preventDefault(),this.isAdjustingManually=!e.target.checked,this.item.trigger("change:auto")},onAddItem:function(e){e.preventDefault();var t=this.item,n=this.options,r=this.isAdjustingManually,o=n.config;if(!0===r)t.set({amount:t.get("amountManual"),tax:t.get("taxManual"),subtotal:t.get("subtotalManual")});else{var a=t.get("quantity");t.set({tax:t.get("tax")*a,subtotal:t.get("subtotal")*a})}o.get("items").add(t)},openDialog:function(){return e("#edd-admin-order-add-item-dialog").dialog("open"),this},closeDialog:function(){return e("#edd-admin-order-add-item-dialog").dialog("close"),this.undelegateEvents(),this}})}).call(this,n(0))},function(e,t,n){"use strict";n(13),n(6)},function(e,t,n){"use strict";(function(e){var t=n(1),r=n(6);Object(t.a)((function(){var t=document.getElementById("edd-override-amounts");if(t){var n=document.querySelector('input[name="edd-order-download-is-overrideable"]');e(document).on("edd-admin-add-order-download",(function(e){_.each(document.querySelectorAll(".overridable input"),(function(e){return e.addEventListener("keyup",r.a)})),Object(r.a)(),t.disabled=1==n.value})),t.addEventListener("change",(function(){this.disabled=!0,n.value=1,_.each(document.querySelectorAll(".overridable input"),(function(e){return e.readOnly=!1}));var e=document.createElement("input");e.name="edd_add_order_override",e.value=!0,e.type="hidden",document.getElementById("edd-add-order-form").appendChild(e)}))}}))}).call(this,n(0))},,,,function(e,t,n){"use strict";var r=wp.Backbone.View.extend({el:"#edd-order-overview",render:function(){return this.views.add(new o(this.options)),this.views.add(new m(this.options)),this}}),o=wp.Backbone.View.extend({el:"#edd-order-overview-summary",render:function(){return this.views.add(new s(this.options)),this.views.add(new h(this.options)),this}}),a=n(3),i=n.n(a);function d(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}var s=wp.Backbone.View.extend({tagName:"tbody",className:"edd-order-overview-summary__items",initialize:function(){this.listenTo(this.options.config.get("items"),"add",this.onAdd),this.listenTo(this.options.config.get("items"),"remove",this.render)},render:function(){var e=this,t=this.options.config;return this.views.remove(),_.each(t.get("items").models,(function(t){return e.onAdd(t)})),this},onAdd:function(e){this.views.add(new l(function(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?d(Object(n),!0).forEach((function(t){i()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):d(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}({},this.options,{model:e})))}});function c(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function u(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?c(Object(n),!0).forEach((function(t){i()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):c(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}var l=wp.Backbone.View.extend({tagName:"tr",template:wp.template("edd-admin-order-item"),events:{"click .delete":"onDelete"},formatCurrency:function(e){return"$".concat(Math.round(e))},prepare:function(){var e=this.options,t=this.model,n=this.formatCurrency,r=e.config;return u({},t.toJSON(),{config:u({},r.toJSON()),amountCurrency:n(this.model.get("amount")),subtotalCurrency:n(this.model.get("subtotal")),taxCurrency:n(this.model.get("tax")),discountCurrency:n(this.model.get("discount"))})},onDelete:function(e){e.preventDefault(),this.options.config.get("items").remove(this.model)}});function f(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function p(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?f(Object(n),!0).forEach((function(t){i()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):f(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}var h=wp.Backbone.View.extend({tagName:"tbody",className:"edd-order-overview-summary__totals",template:wp.template("edd-admin-order-totals"),formatCurrency:function(e){return"$".concat(Math.round(e))},initialize:function(){this.listenTo(this.options.config.get("items"),"add remove",this.render),this.getSubtotal=this.getSubtotal.bind(this),this.getTax=this.getTax.bind(this),this.getTotal=this.getTotal.bind(this)},prepare:function(){var e=this.options,t=this.formatCurrency,n=this.getSubtotal,r=this.getTax,o=this.getTotal,a=e.config,i=!0===a.get("hasQuantity")?2:1;return{config:p({},a.toJSON(),{colspan:i}),subtotal:t(n()),tax:t(r()),total:t(o())}},getSubtotal:function(){var e=0;return _.each(this.options.config.get("items").models,(function(t){return e+=+t.get("subtotal")})),e},getTax:function(){var e=0;return _.each(this.options.config.get("items").models,(function(t){return e+=+t.get("tax")})),e},getTotal:function(){return this.getSubtotal()+this.getTax()}}),m=wp.Backbone.View.extend({el:"#edd-order-overview-actions",events:{"click #add-item":"addItem"},addItem:function(e){e.preventDefault(),new g.a(this.options).openDialog().render()}}),g=n(11);n.d(t,"e",(function(){return r})),n.d(t,"f",(function(){return o})),n.d(t,"d",(function(){return s})),n.d(t,"c",(function(){return l})),n.d(t,"g",(function(){return h})),n.d(t,"a",(function(){return m})),n.d(t,"b",(function(){return g.a}))},function(e,t,n){"use strict";var r=n(5),o=Backbone.Collection.extend({model:r.a});n.d(t,"a",(function(){return o}))},,,,,,,,,function(e,t,n){"use strict";n.r(t),function(e){n(28),n(12),n(29),n(33);var t=n(1);Object(t.a)((function(){e("#edd-add-order-form").on("submit",(function(){if(e("#publishing-action .spinner").css("visibility","visible"),e("#edd-order-submit").prop("disabled",!0),e(".orderitems tr.no-items").is(":visible")?e("#edd-add-order-no-items-error").slideDown():e("#edd-add-order-no-items-error").slideUp(),e(".order-customer-info").is(":visible")?e("#edd-add-order-customer-error").slideDown():e("#edd-add-order-customer-error").slideUp(),e(".notice:not(.updated)").is(":visible"))return e("#publishing-action .spinner").css("visibility","hidden"),e("#edd-order-submit").prop("disabled",!1),!1}))}))}.call(this,n(0))},function(e,t,n){"use strict";(function(e){var t=n(1),r=n(17),o=n(18),a=n(5);Object(t.a)((function(){if(window.eddAdminOrderOverview){var t=eddAdminOrderOverview,n=t.isAdding,i=t.hasTax,d=t.hasQuantity,s=t.items,c=new o.a;c.set(s);var u=new a.b({isAdding:"1"===n,hasTax:"0"!==i&&i,hasQuantity:"1"===d,items:c}),l=new r.e({config:u});l.render(),function(){var t=document.getElementById("edd_order_address_country"),n=document.getElementById("edd_order_address_region");function r(){var e=t.options[t.selectedIndex].value,r=n.options?n.options[n.selectedIndex].value:n.value,o=document.getElementById("edd_get_tax_rate_nonce").value;wp.ajax.send("edd_get_tax_rate",{data:{nonce:o,country:e,region:r},success:function(t){var n=t.tax_rate;n*=100,l.options.config.set("hasTax",{country:e,region:r,rate:n})},error:function(){l.options.config.set("hasTax",!1)}})}t&&n&&(e(t).on("change",_.debounce(r,250)),e(n).on("change",r),e(n).on("keyup",_.debounce(r,250)))}()}}))}).call(this,n(0))},function(e,t,n){"use strict";n(30),n(31),n(32)},function(e,t,n){"use strict";(function(e){var t=n(2),r=n(1),o=(n(12),{});Object(r.a)((function(){e('select[name="edd-payment-address[0][country]"]').change((function(){var n=e(this),r={action:"edd_get_shop_states",country:n.val(),nonce:n.data("nonce"),field_name:"edd-payment-address[0][region]"};return e.post(ajaxurl,r,(function(n){var r=e("#edd-order-address-state-wrap select, #edd-order-address-state-wrap input");e("#edd-order-address-state-wrap .chosen-container").remove(),"nostates"===n?r.replaceWith('<input type="text" name="edd-payment-address[0][region]" value="" class="edd-edit-toggles medium-text"/>'):(r.replaceWith(n),e("#edd-order-address-state-wrap select").each((function(){var n=e(this);n.chosen(Object(t.a)(n))})))})),!1})),e(".edd-payment-change-customer-input").on("change",(function(){var n={action:"edd_customer_addresses",customer_id:e(this).val(),nonce:e("#edd_add_order_nonce").val()};return e.post(ajaxurl,n,(function(n){var r=n.success,a=n.data;r?(o=a,a.html?(e(".customer-address-select-wrap").html(a.html).show(),e(".customer-address-select-wrap select").each((function(){var n=e(this);n.chosen(Object(t.a)(n))}))):e(".customer-address-select-wrap").html("").hide()):e(".customer-address-select-wrap").html("").hide()}),"json"),!1})),e(document.body).on("change",".customer-address-select-wrap .add-order-customer-address-select",(function(){var t=e(this).val(),n=e("#edd-add-order-form select#edd_order_address_country"),r=o.addresses[t];e('#edd-add-order-form input[name="edd_order_address[address]"]').val(r.address),e('#edd-add-order-form input[name="edd_order_address[address2]"]').val(r.address2),e('#edd-add-order-form input[name="edd_order_address[postal_code]"]').val(r.postal_code),e('#edd-add-order-form input[name="edd_order_address[city]"]').val(r.city),n.val(r.country).trigger("chosen:updated"),e('#edd-add-order-form input[name="edd_order_address[address_id]"]').val(t);var a={action:"edd_get_shop_states",country:n.val(),nonce:e(".add-order-customer-address-select").data("nonce"),field_name:"edd_order_address_region"};return e.post(ajaxurl,a,(function(t){e("select#edd_order_address_region").find("option:gt(0)").remove(),"nostates"!==t&&e(t).find("option:gt(0)").appendTo("select#edd_order_address_region"),e("select#edd_order_address_region").trigger("chosen:updated"),e("select#edd_order_address_region").val(r.region).trigger("chosen:updated")})),!1})),e(".edd-order-address-country").on("change",(function(){var t=e(this),n={action:"edd_get_shop_states",country:t.val(),nonce:t.data("nonce"),field_name:"edd-order-address-country"};e.post(ajaxurl,n,(function(t){e("select.edd-order-address-region").find("option:gt(0)").remove(),"nostates"!==t&&e(t).find("option:gt(0)").appendTo("select.edd-order-address-region"),e("select.edd-order-address-region").trigger("chosen:updated")}))}))}))}).call(this,n(0))},function(e,t,n){"use strict";(function(e){var t=n(1);Object(t.a)((function(){e("#edd-customer-details").on("click",".edd-payment-change-customer, .edd-payment-change-customer-cancel",(function(t){t.preventDefault();var n=e(this).hasClass("edd-payment-change-customer"),r=e(this).hasClass("edd-payment-change-customer-cancel");n?(e(".order-customer-info").hide(),e(".change-customer").show(),setTimeout((function(){e(".edd-payment-change-customer-input").css("width","300")}),1)):r&&(e(".order-customer-info").show(),e(".change-customer").hide())})),e("#edd-customer-details").on("click",".edd-payment-new-customer, .edd-payment-new-customer-cancel",(function(t){t.preventDefault();var n=e(this).hasClass("edd-payment-new-customer"),r=e(this).hasClass("edd-payment-new-customer-cancel");n?(e(".order-customer-info").hide(),e(".new-customer").show()):r&&(e(".order-customer-info").show(),e(".new-customer").hide());n=e("#edd-new-customer");e(".new-customer").is(":visible")?n.val(1):n.val(0)}))}))}).call(this,n(0))},function(e,t,n){"use strict";(function(e){var t=n(1);Object(t.a)((function(){var t=e(".edd-order-resend-receipt-addresses");e(document.body).on("click","#edd-select-receipt-email",(function(e){e.preventDefault(),t.slideDown()})),e(document.body).on("change",".edd-order-resend-receipt-email",(function(){var t=e("input:radio.edd-order-resend-receipt-email:checked").val();e("#edd-select-receipt-email").data("email",t)})),e(document.body).on("click","#edd-select-receipt-email",(function(){if(confirm(edd_vars.resend_receipt)){var t=e(this).prop("href")+"&email="+e(this).data("email");window.location=t}})),e(document.body).on("click","#edd-resend-receipt",(function(){return confirm(edd_vars.resend_receipt)}))}))}).call(this,n(0))},function(e,t,n){"use strict";(function(e){var t=n(1);Object(t.a)((function(){e(".edd-advanced-filters-button").on("click",(function(t){t.preventDefault(),e("#edd-advanced-filters").toggleClass("open")})),e(".edd_countries_filter").on("change",(function(){var t=e(this),n={action:"edd_get_shop_states",country:t.val(),nonce:t.data("nonce"),field_name:"edd_regions_filter"};return e.post(ajaxurl,n,(function(t){e("select.edd_regions_filter").find("option:gt(0)").remove(),"nostates"!==t&&e(t).find("option:gt(0)").appendTo("select.edd_regions_filter"),e("select.edd_regions_filter").trigger("chosen:updated")})),!1}))}))}).call(this,n(0))}]);
//# sourceMappingURL=edd-admin-orders.js.map