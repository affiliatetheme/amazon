var globalRequest = 0;
var asinContainer = new Array();


jQuery(document).ready(function() {
    // checkAdblock
    checkAdblock();

    // checkConnection
    if (jQuery('#amazon_public_key').val().length > 0 && jQuery('#amazon_secret_key').val().length > 0) {
        checkConnection();
    } else {
        return;
    }

    // searchAction
    jQuery('#search').bind('keyup', function(event) {
        if (event.keyCode == 13) {
            jQuery('#at-import-window input#page').val('1');
            searchAction();
        }
    });
    
    jQuery('#search-link').bind('click', function(event) {
        jQuery('#at-import-window input#page').val('1');
        searchAction();
    });

    // singleImportAction
    jQuery('.single-import-product:not(.noevent)').live('click', function(event) {
        singleImportAction(this);

        event.preventDefault();
    });

    // quickImportAction
    jQuery('.quick-import').live('click', function(event) {
        var id = jQuery(this).attr('data-asin');

        quickImportAction(id);

        event.preventDefault();
    });

    // massImportAction
    jQuery('.mass-import').live('click', function(event) {
        massImportAction(this);

        event.preventDefault();
    });

    // grabLink
    jQuery('#grab-link').bind('click', function(event) {
        grabLink(event);
        event.preventDefault();
    });


	jQuery("input[type=checkbox].unique").live('click', function() {
    	jQuery("input[type=checkbox].unique").each(function() {
			jQuery(this)[0].checked = false;
		});
		jQuery(this)[0].checked = true;
	});

    /*
     * Stuff
     */
    jQuery("input[type=checkbox].unique").live('click', function() {
        if(jQuery("input[type=checkbox].unique").length > 1) {
            jQuery("input[type=checkbox].unique").each(function() {
                jQuery(this)[0].checked = false;
            });
            jQuery(this)[0].checked = true;
        }
    });

	jQuery("input[type=checkbox].disable-this").live('click', function() {
		if(jQuery(this).attr('checked')){
			jQuery(this).closest('.image').css('opacity', '0.5');
		} else {
			jQuery(this).closest('.image').css('opacity', '1');
		}
	});

    // pagination
    jQuery('.next-page').bind('click', function(event) {
    	if(jQuery(this).attr('disabled') != "disabled") {
			var current_page = parseInt(jQuery('#page').val());
			var max_pages = parseInt(jQuery('#max-pages').val());

			jQuery(this).attr('disabled', true);

			if(current_page < max_pages) {
				jQuery('#page').val(current_page + 1);
			}
        	searchAction();
       }

       event.preventDefault();
    });
    jQuery('.prev-page').bind('click', function(event) {
		var current_page = parseInt(jQuery('#page').val());
		var max_pages = parseInt(jQuery('#max-pages').val());
		
		jQuery(this).attr('disabled', true);
		
		if(current_page <= max_pages) {
			jQuery('#page').val(current_page - 1);
		} 
        searchAction();

        event.preventDefault();
    });

    // clear API Log
    jQuery('.clear-api-log').click(function(e) {
        var btn = jQuery(this);
        var type = jQuery(this).data('type');
        var hash = jQuery(this).data('hash');

        jQuery(btn).attr('disabled', true).append(' <i class="fa fa-circle-o-notch fa-spin"></i>');

        jQuery.ajax({
            url: ajaxurl,
            dataType: 'json',
            type: 'GET',
            data: "action=at_api_clear_log&hash="+hash+"&type="+type,
            success: function(data){
                jQuery(btn).attr('disabled', false).find('i').remove();

                if(data['status'] == 'success') {
                    jQuery('table.apilog tbody').html('');
                }
            },
            error: function() {
                jQuery(btn).attr('disabled', false).find('i').remove();
            }
        });

        e.preventDefault();
    });

    // api Tabs
    jQuery("#at-api-tabs a.nav-tab").click(function(e){
        jQuery("#at-api-tabs a").removeClass("nav-tab-active");
        jQuery(".at-api-tab").removeClass("active");

        var a = jQuery(this).attr("id").replace("-tab","");
        jQuery("#"+a).addClass("active");
        jQuery(this).addClass("nav-tab-active");
    });

    jQuery("#asinsremlist").click(function(e){
        jQuery("#leavedasins").toggle("hidden");
    });

    jQuery(document).ready(function(e) {
        var a=window.location.hash.replace("#top#","");
        (""==a||"#_=_"==a) &&(a=jQuery(".at-api-tab").attr("id")),jQuery('#at-api-tabs a').removeClass('nav-tab-active'),jQuery('.at-api-tab').removeClass('active'),jQuery("#"+a).addClass("active"),jQuery("#"+a+"-tab").addClass("nav-tab-active");
    })

    // Buttons
    jQuery(function($) {
        $(document).ajaxStop(function() {
            jQuery('#search-link').attr('disabled', false).find('.fa-spin').remove();
            jQuery('.next-page, .prev-page').attr('disabled', false);
        });
    });

    // select, deselect all checkboxes
    jQuery('body').on('click', '.select-all', function() {
        if (jQuery(this).is(':checked')) {
            jQuery('div.product-images .disable-this').attr('checked', true);
        } else {
            jQuery('div.product-images .disable-this').attr('checked', false);
        }
    });
});

/*
 * Function:
 * checkConnection
 */
var checkConnection = function() {
    jQuery('#search-link').attr('disabled', true).append(' <i class="fa fa-circle-o-notch fa-spin"></i>').after(' <small class="status-after" style="margin: 5px;display: inline-block;">Verbindungsaufbau...</small>');

    var value = 'Matrix'
    var cat = 'DVD';
    var country = 'DE';
    var condition = '';
    var page = '1';
    var track = value + " - " + cat + " - " + country;
    var resultContainer = jQuery('#checkConnection');

    if (value.length < 3 && globalRequest == 1) {
        return;
    }

    globalRequest = 1;
    jQuery.ajax({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: "action=amazon_api_search&q="+value+"&category="+cat+"&country="+country+"&condition="+condition+"&page="+page,
        success: function(data){
            var totalpages = data['rmessage']['totalpages'];
            globalRequest = 0;
            if(totalpages > 0) {
                resultContainer.fadeOut('fast', function() {
                    resultContainer.append('<div class="updated"><p class="success">Verbindung erfolgreich hergestellt.</p></div>');
                    resultContainer.fadeIn('fast');
                    setCurrentTab('search');
                });
            } else {
               // resultContainer.fadeOut('fast', function() {
                    resultContainer.append('<div class="error"><p class="error">Eine Verbindung zu Amazon konnte nicht hergestellt werden. Bitte prüfe deinen Public Key, Secret Key und deine Partner ID.</p></div>');
                //});
            }

            if(data['rmessage']['errormsg'] != "") {
                resultContainer.append('<div class="error"><p class="error">'+data['rmessage']['errormsg']+'</p></div>');
            }

            jQuery('.status-after').remove();
        },
        error: function() {
            //resultContainer.fadeOut('fast', function() {
                resultContainer.append('<div class="error"><p class="error">Eine Verbindung zu Amazon konnte nicht hergestellt werden. Bitte prüfe deinen Public Key, Secret Key und deine Partner ID.</p></div>');
                resultContainer.fadeIn('fast');
            //});
        },
    });
};

/*
 * Function
 * searchAction
 */
var searchAction = function() {
    if(jQuery('#search-link').prop('disabled')) {
        return
    }

    var q = jQuery('#at-import-window input#search').val();
    var grabber = jQuery('#at-import-window input#grabbedasins').val();

    jQuery('#search-link').attr('disabled', true).append(' <i class="fa fa-circle-o-notch fa-spin"></i>');

    var value = jQuery('#at-import-window input#search').val();
    var grabbedasins = jQuery('#at-import-window textarea#grabbedasins').val();
    var cat = jQuery('#at-import-window select#category').val();
    var country = jQuery('#at-import-window select#amazon_country').val();
    var page = jQuery('#at-import-window input#page').val();
    var condition = '';
    var track = value + " - " + cat + " - " + country;
    var resultContainer = jQuery('#at-import-window table #results');

    globalRequest = 1;
    jQuery.ajax({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: "action=amazon_api_search&q="+value+"&grabbedasins="+grabbedasins+"&category="+cat+"&country="+country+"&condition="+condition+"&page="+page,
        success: function(data){
            var totalpages = '5';
            jQuery('#max-pages').val(totalpages);
            if(totalpages == 1) {
                jQuery('.page-links').hide();
            } else if(totalpages > 1) {
                jQuery('.page-links').show();
                if(page == 1) { jQuery('.page-links .prev-page').hide(); } else if(page > 1) { jQuery('.page-links .prev-page').show(); }
                if(page >= totalpages) { jQuery('.page-links .next-page').hide(); } else { jQuery('.page-links .next-page').show(); }
            }

            resultContainer.fadeOut('fast', function() {
                resultContainer.html('');

                if(data['items']) {
                    for (var x in data['items']) {
                        removeItemFromList(data['items'][x].asin);

                        if (!data['items'][x].price)
                            data['items'][x].price = 'kA';

                        if (!data['items'][x].img)
                            data['items'][x].img = 'assets/images/no.gif';

                        var html = '';

                        if(data['items'][x].exists != "false") {
                            html += '<tr class="item success" data-asin="'+data['items'][x].asin+'">';
                            html += '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-'+data['items'][x].asin+' name="item[]" value="'+data['items'][x].asin+'" disabled="disabled"></th>';
                        } else {
                            if (data['items'][x].external == 1) {
                                html += '<tr class="item item-warning" data-asin="'+data['items'][x].asin+'">';
                            } else {
                                html += '<tr class="item" data-asin="'+data['items'][x].asin+'">';
                            }
                            html += '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-'+data['items'][x].asin+' name="item[]" value="'+data['items'][x].asin+'"></th>';
                        }
                        html += '<td class="asin">'+data['items'][x].asin+'</td>';
                        if(data['items'][x].img !="assets/images/no.gif") {
                            html += '<td class="image"><img src="'+data['items'][x].img+'"></td>';
                        } else {
                            html += '<td class="image">Kein Bild vorhanden</td>';
                        }
                        if (data['items'][x].external == 1) {
                            html += '<td class="title"><span style="color:#fff; font-size:12px; background:#c01313; border-radius:2px; padding:2px 4px; margin-right:3px ">externes Produkt!</span><a href="'+data['items'][x].url+'" target="_blank">'+data['items'][x].Title+'</a></td>';
                        } else {
                            html += '<td class="title"><a href="'+data['items'][x].url+'" target="_blank">'+data['items'][x].Title+'</a></td>';
                        }

                        html += '<td class="rating">'+data['items'][x].average_rating+' / 5</td>';
                        html += '<td class="price">'+data['items'][x].price+'<br>(UVP: ' + data['items'][x].price_list + ')</td>';
                        html += '<td class="margin">';
                        if(data['items'][x].cat_margin != 0) {
                            var margin_sale_val = (((data['items'][x].price_amount/119)*100)/100) * data['items'][x].cat_margin;
                            var margin_sale = number_format(margin_sale_val, 2, ',', '.')

                            html += data['items'][x].cat_margin+'%<br>('+data['items'][x].currency+' '+margin_sale+' / Sale)';
                        } else { html += 'kA'; }
                        html += '</td>';
                        html += '<td class="category">'+data['items'][x].category+'</td>';
                        if(data['items'][x].exists != "false") {
                            html += '<td class="action"><a href="' + jQuery('#at-import-page').attr('data-url') + 'post.php?post=' + data['items'][x].exists + '&action=edit" target="_blank" title="Editieren"><i class="fa fa-edit"></i></a></td>';
                        } else {
                            html += '<td class="action"><a href="'+jQuery('#at-import-page').attr('data-url')+'admin-ajax.php?action=amazon_api_lookup&func=modal&asin='+data['items'][x].asin+'&height=700&width=820" class="thickbox" title="Importieren"><i class="fa fa-plus-circle"></i></a> <a href="#" title="Quickimport" class="quick-import" data-asin="'+data['items'][x].asin+'"><i class="fa fa-bolt"></i></a></td>';
                        }
                        html += '</tr>';

                        resultContainer.append(html);

                    }
                } else {
                    html += '<tr class="item error" data-asin="">';
                    html += '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-1 name="item[]" value="0" disabled="disabled"></th>';
                    html += '<td colspan="8">Es wurden keine Produkte gefunden. Bitte definiere deine Suche neu.</td>';
                    html += '</tr>';
                    resultContainer.append(html);
                }

                resultContainer.fadeIn('fast');
            });
        }
    });

    globalRequest = 0;
};


function removeItemFromList(asin) {

    asinContainer.push(asin);
    replaced = jQuery('#at-import-window textarea#grabbedasins').val();

    for (var rAsin in asinContainer) {
        console.log(asinContainer[rAsin]);

        replaced = replaced.replace(asinContainer[rAsin], '');
    }

    replaced = replaced.replace(new RegExp('^\s*[\r\n]','gm'), "");

    jQuery("#leavedasins").val(replaced);
}
/*
 * Function
 * singleImportAction
 */
var singleImportAction = function(target) {
    jQuery(target).append(' <i class="fa fa-circle-o-notch fa-spin"></i>').addClass('noevent');
    var data = jQuery( 'form#import-product' ).serialize();
    jQuery.ajax({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: data,
        success: function(data){
            jQuery(target).find('i').remove();
            var asin = jQuery('#TB_ajaxContent #asin').val();

            if(data['rmessage']['success'] == "false") {
                jQuery(target).after('<div class="error">'+data['rmessage']['reason']+'</div>');
                jQuery(target).append(' <i class="fa fa-exclamation-triangle"></i>').attr('disabled', true);
            } else if(data['rmessage']['success'] == "true") {
                jQuery(target).hide();
                jQuery(target).after('<a class="button button-primary" href="'+jQuery('#at-import-page').attr('data-url')+'post.php?post='+data['rmessage']['post_id']+'&action=edit"><i class="fa fa-pencil"></i> Produkt bearbeiten</a>');
                jQuery('body table.products tr[data-asin=' + asin + ']').addClass('success');
                jQuery('body table.products tr[data-asin=' + asin + '] .check-column input[type=checkbox]').attr('disabled', 'disabled');
                jQuery('body table.products tr[data-asin=' + asin + '] .action i').removeClass('fa-plus-circle').addClass('fa fa-edit').closest('a').removeClass('thickbox').attr('target', '_blank').attr('href', jQuery('#at-import-page').attr('data-url') + 'post.php?post='+data['rmessage']['post_id']+'&action=edit');
            }
        }
    });
};

/*
 * Function
 * quickImportAction
 */
var quickImportAction = function(id, mass, i, max_items) {
    mass = mass || false;
    i = i || "1";
    max_items = max_items || "0";

    var target = jQuery('#results .item[data-asin='+id+']').find(".action a.quick-import");
    var ajax_loader = jQuery('.at-ajax-loader');
    var asin = jQuery(target).attr('data-asin');
    var nonce = jQuery('#at-import-page').attr('data-nonce');

    jQuery(target).append(' <i class="fa fa-circle-o-notch fa-spin"></i>').addClass('noevent');

    jQuery.ajaxQueue({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: {action : 'amazon_api_import', asin : asin, func : 'quick-import', '_wpnonce' : nonce},
        success: function(data){
            jQuery(target).find('i').remove();

            if(data['rmessage']['success'] == "false") {
                jQuery(target).after('<div class="error">'+data['rmessage']['reason']+'</div>');
                jQuery(target).append(' <i class="fa fa-exclamation-triangle"></i>').attr('disabled', true);
            } else if(data['rmessage']['success'] == "true") {
                jQuery(target).hide();
                jQuery('body table.products tr[data-asin=' + asin + ']').addClass('success');
                jQuery('body table.products tr[data-asin=' + asin + '] .check-column input[type=checkbox]').attr('disabled', 'disabled');
                jQuery('body table.products tr[data-asin=' + asin + '] .action i').removeClass('fa-plus-circle').addClass('fa fa-edit').closest('a').removeClass('thickbox').attr('target', '_blank').attr('href', jQuery('#at-import-page').attr('data-url') + 'post.php?post='+data['rmessage']['post_id']+'&action=edit');
            }

            if(mass == true) {
                var curr = parseInt(jQuery(ajax_loader).find('.progress-bar').attr('data-item'));
                var procentual = (100/max_items)*curr;
                console.log(curr+ ' / ' + procentual);
                var procentual_fixed =  procentual.toFixed(2);
                jQuery(ajax_loader).find('.progress-bar').css('width', procentual+'%').html(procentual_fixed+'%');
                jQuery(ajax_loader).find('.progress-bar').attr('data-item', curr+1);
                jQuery(ajax_loader).find('.current').html(curr+1);

                if(i >= max_items) {
                    jQuery(ajax_loader).removeClass('active');
                }
            }
        },
        error : function() {
            return
        }
    });
};

/*
 * Function
 * massImportAction
 */
var massImportAction = function(target) {
    var max_items = jQuery('#results .item:not(".success") .check-column input:checkbox:checked').length;
    var ajax_loader = jQuery('.at-ajax-loader');

    var i = 1;

    jQuery(ajax_loader).find('.progress-bar').css('width', '0%').html('0%').attr('data-item', '1');
    jQuery(ajax_loader).addClass('active').find('p').html('Importiere Produkt <span class="current">1</span> von '+max_items);

    jQuery('#results .item:not(".success") .check-column input:checkbox:checked').each(function () {
        var id = jQuery(this).val();
        quickImportAction(id, true, i, max_items);
        i++;
    });
};

/*
 * Function
 * grabLink
 */
var grabLink = function(e) {
    if(jQuery('#grabburl').val().length < 5)
        return;

    if(jQuery('#grab-link').prop('disabled'))
        return

    jQuery('#grab-link').attr('disabled', true).append(' <i class="fa fa-circle-o-notch fa-spin"></i>');

    var url = jQuery('#grabburl').val();
    if (url.length > 1 && isUrlValid(url) == false && globalRequest == 1) {
        jQuery('#grab-link .fa-spin').remove();
        jQuery('#grab-link').attr('disabled', false);
        return;
    }

    globalRequest = 1;
    jQuery.ajax({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: "action=amazon_api_grab&url="+encodeURIComponent(url),
        success: function(data){
            var asins = data.asins;
            jQuery.each(asins, function( index, value ) {
                if (index != 0) {
                    jQuery('#grabbedasins').val(jQuery('#grabbedasins').val()+"\n"+value);
                }else {
                    jQuery('#grabbedasins').val(value);
                }
            });
            jQuery('#grab-link .fa-spin').remove();
            jQuery('#grab-link').attr('disabled', false);
        },
        error: function(data) {
            jQuery('#grab-link .fa-spin').remove();
            jQuery('#grab-link').attr('disabled', false);
        }
    });
    e.preventDefault();
};

/*
 * Function
 * checkAdblock
 */
var checkAdblock = function() {
    setTimeout(function() {
        if(!document.getElementsByClassName) return;
        var ads = document.getElementsByClassName('afs_ads'),
            ad  = ads[ads.length - 1];

        if(!ad
            || ad.innerHTML.length == 0
            || ad.clientHeight === 0) {
            jQuery('#checkConnection').append('<div class="alert alert-danger">Bitte deaktiviere deinen Adblocker um alle Funktionen der API zu nutzen!</div>');
        } else {
            ad.style.display = 'none';
        }

    }, 2000);
}

/*
 * Function
 * isUrlValid
 */
function isUrlValid(url) {
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
}

/*
 * Function
 * number_format
 */
function number_format(number, decimals, dec_point, thousands_sep) {
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function (n, prec) {
      var k = Math.pow(10, prec);
      return '' + (Math.round(n * k) / k)
        .toFixed(prec);
    };
	//Fix for IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if ((s[1] || '').length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1).join('0');
	}
	return s.join(dec);
}

/*
 * Tabs
 */
jQuery(document).ready(function(e) {
	jQuery("#at-api-tabs a.nav-tab").click(function(e){
		jQuery("#at-api-tabs a").removeClass("nav-tab-active");
		jQuery(".at-api-tab").removeClass("active");

		var a = jQuery(this).attr("id").replace("-tab","");
		jQuery("#"+a).addClass("active");
		jQuery(this).addClass("nav-tab-active");	
	});

	var a=window.location.hash.replace("#top#","");
	(""==a||"#_=_"==a) &&(a=jQuery(".at-api-tab").attr("id")),jQuery('#at-api-tabs a').removeClass('nav-tab-active'),jQuery('.at-api-tab').removeClass('active'),jQuery("#"+a).addClass("active"),jQuery("#"+a+"-tab").addClass("nav-tab-active");
});

function setCurrentTab(item) {
	var a=window.location.hash.replace("#top#","");
	
	if(a == "") {
		jQuery('#at-api-tabs a').removeClass('nav-tab-active');
		jQuery('.at-api-tab').removeClass('active')
		jQuery("#"+item).addClass("active");
		jQuery("#"+item+"-tab").addClass("nav-tab-active");
	}
}

/*
 * Function
 * jQuery Queue
 */
(function($) {
    var ajaxQueue = $({});
    $.ajaxQueue = function(ajaxOpts) {
        var oldComplete = ajaxOpts.complete;
        ajaxQueue.queue(function(next) {
            ajaxOpts.complete = function() {
                if (oldComplete) oldComplete.apply(this, arguments);
                next();
            };
            $.ajax(ajaxOpts);
        });
    };
})(jQuery);