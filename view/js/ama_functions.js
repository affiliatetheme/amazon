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
    jQuery('#grab-link').live('click', function(event) {
        grabLink(event);
        event.preventDefault();
    });

    // FeedWriteItem
    jQuery('#add-new-keyword button').bind('click', function(event) {
        FeedWriteItem(event);
        event.preventDefault();
    });

    // FeedDeleteItem
    jQuery('table.feed .delete-keyword').bind('click', function(event) {
        var id = jQuery(this).attr('data-id');
        FeedDeleteItem(event, id);
        event.preventDefault();
    });

    // FeedChangeStatus
    jQuery('table.feed .change-status').bind('click', function(event) {
        var id = jQuery(this).attr('data-id');
        var status = jQuery(this).attr('data-status');
        FeedChangeStatus(event, id, status);
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

    jQuery(".form-toggle").click(function(e){
        var button = jQuery(this);
        var item = jQuery(this).closest('.form-container').find('.form-toggle-item');

        if(item) {
            jQuery(item).toggle("fast", function() {
                if(jQuery(item).is(':visible')) {
                    jQuery(button).html(jQuery(button).data('hide-text'));
                } else {
                    jQuery(button).html(jQuery(button).data('show-text'));
                }
            });
        }

        e.preventDefault();
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
    jQuery('#search-link').attr('disabled', true).append(' <i class="fa fa-circle-o-notch fa-spin"></i>').after(' <small class="status-after" style="margin: 5px;display: inline-block;">' + amazon_vars.connection + '</small>');

    var value = 'Matrix'
    var cat = 'MoviesAndTV';
    var page = '1';
    var resultContainer = jQuery('#checkConnection');

    if (value.length < 3 && globalRequest == 1) {
        return;
    }

    globalRequest = 1;
    jQuery.ajax({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: "action=at_aws_search&q=" + value + "&category=" + cat +  "&page=" + page,
        success: function(data){
            var totalpages = data['rmessage']['totalpages'] / 10;
            globalRequest = 0;

            if(totalpages > 0) {
                resultContainer.fadeOut('fast', function() {
                    resultContainer.append('<div class="updated"><p class="success">' + amazon_vars.connection_ok + '</p></div>');
                    resultContainer.fadeIn('fast');
                    setCurrentTab('search');
                });
            } else {
                resultContainer.append('<div class="error"><p class="error">' + amazon_vars.connection_error + '</p></div>');
            }

            if(data['rmessage']['errormsg'] != "") {
                resultContainer.append('<div class="error"><p class="error">' + data['rmessage']['errormsg'] + '</p></div>');
            }

            jQuery('.status-after').remove();
        },
        error: function() {
            resultContainer.append('<div class="error"><p class="error">' + amazon_vars.connection_error + '</p></div>');
            resultContainer.fadeIn('fast');
        }
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

    jQuery('#search-link').attr('disabled', true).append(' <i class="fa fa-circle-o-notch fa-spin"></i>');

    var value = jQuery('.tabwrapper .at-api-tab#search input#search').val();
    var grabbedasins = jQuery('.tabwrapper .at-api-tab#search textarea#grabbedasins').val();
    var category = jQuery('.tabwrapper .at-api-tab#search select#category').val();
    var page = jQuery('.tabwrapper .at-api-tab#search input#page').val();
    var title = jQuery('.tabwrapper .at-api-tab#search input#title').val();
    var sort = jQuery('.tabwrapper .at-api-tab#search select#sort').val();
    var merchant = jQuery('.tabwrapper .at-api-tab#search select#merchant').val();
    var min_price = jQuery('.tabwrapper .at-api-tab#search input#min_price').val();
    var max_price = jQuery('.tabwrapper .at-api-tab#search input#max_price').val();
    var resultContainer = jQuery('#at-import-window table #results');

    globalRequest = 1;
    jQuery.ajax({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: "action=at_aws_search&q=" + value + "&category=" + category + "&title=" + title + "&grabbedasins=" + grabbedasins + "&page=" + page + "&sort=" + sort + "&merchant=" + merchant + "&min_price=" + min_price + "&max_price=" + max_price,
        success: function(data){
            
            var totalpages = data['rmessage']['totalpages'];

            if(category == 'All') {
                var totalpages = (totalpages <= 5 ? totalpages : 5);
            } else {
                var totalpages = (totalpages <= 10 ? totalpages : 10);
            }
            
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
                            html += '<tr class="item success" data-asin="' + data['items'][x].asin + '">';
                            html += '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-' + data['items'][x].asin + ' name="item[]" value="' + data['items'][x].asin + '" disabled="disabled"></th>';
                        } else {
                            if (data['items'][x].external == 1) {
                                html += '<tr class="item item-warning" data-asin="' + data['items'][x].asin + '">';
                            } else {
                                html += '<tr class="item" data-asin="' + data['items'][x].asin + '">';
                            }
                            html += '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-' + data['items'][x].asin + ' name="item[]" value="' + data['items'][x].asin + '"></th>';
                        }
                        html += '<td class="asin">' + data['items'][x].asin + '</td>';
                        html += '<td class="ean">' + data['items'][x].ean + '</td>';
                        if(data['items'][x].img !="assets/images/no.gif") {
                            html += '<td class="image"><img src="' + data['items'][x].img + '"></td>';
                        } else {
                            html += '<td class="image">' + amazon_vars.no_image + '</td>';
                        }
                        if (data['items'][x].external == 1) {
                            html += '<td class="title"><span style="color:#fff; font-size:12px; background:#c01313; border-radius:2px; padding:2px 4px; margin-right:3px ">' + amazon_vars.external_product + '</span><a href="' + data['items'][x].url + '" target="_blank">';
                            html += data['items'][x].title;
                            // prime
                            if(data['items'][x].prime == 1) {
                                html += ' <i class="at at-prime"></i>';
                            }
                            html += '</a></td>';
                        } else {
                            html += '<td class="title"><a href="' + data['items'][x].url + '" target="_blank">';
                            html += data['items'][x].title;
                            // prime
                            if(data['items'][x].prime == 1) {
                                html += ' <i class="at at-prime"></i>';
                            }
                            html += '</a></td>';
                        }

                        html += '<td class="price">' + data['items'][x].price + '<br>(' + amazon_vars.uvp + ': ' + data['items'][x].price_list + ')</td>';
                        html += '<td class="margin">';
                        if(data['items'][x].category_margin != 0) {
                            var margin_sale_val = (((data['items'][x].price_amount/119)*100)/100) * data['items'][x].category_margin;
                            var margin_sale = number_format(margin_sale_val, 2, ',', '.')

                            html += data['items'][x].category_margin + '%<br>(' + data['items'][x].currency.toUpperCase() + ' ' + margin_sale + ' / Sale)';
                        } else { html += 'kA'; }
                        html += '</td>';
                        html += '<td class="category">' + (data['items'][x].category != null ? data['items'][x].category : '-') + '</td>';
                        if(data['items'][x].exists != "false") {
                            html += '<td class="action"><a href="' + jQuery('#at-import-page').attr('data-url') + 'post.php?post=' + data['items'][x].exists + '&action=edit" target="_blank" title="' + amazon_vars.edit + '"><i class="fa fa-edit"></i></a></td>';
                        } else {
                            html += '<td class="action"><a href="' + jQuery('#at-import-page').attr('data-url') + 'admin-ajax.php?action=amazon_api_lookup&func=modal&asin=' + data['items'][x].asin + '&height=700&width=820" class="thickbox" title="' + amazon_vars.import + '"><i class="fa fa-plus-circle"></i></a> <a href="#" title="Quickimport" class="quick-import" data-asin="' + data['items'][x].asin + '"><i class="fa fa-bolt"></i></a></td>';
                        }
                        html += '</tr>';

                        resultContainer.append(html);
                        jQuery('table.products tfoot .taxonomy-select').fadeIn();
                    }
                } else {
                    html += '<tr class="item error" data-asin="">';
                    html += '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-1 name="item[]" value="0" disabled="disabled"></th>';
                    html += '<td colspan="7">' + amazon_vars.no_products_found + '</td>';
                    html += '</tr>';
                    resultContainer.append(html);
                    jQuery('table.products tfoot .taxonomy-select').fadeOut();
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
                jQuery(target).after('<a class="button button-primary" href="'+jQuery('#at-import-page').attr('data-url')+'post.php?post='+data['rmessage']['post_id']+'&action=edit"><i class="fa fa-pencil"></i> ' + amazon_vars.edit_product + '</a>');
                jQuery('body table.products tr[data-asin=' + asin + ']').addClass('success');
                jQuery('body table.products tr[data-asin=' + asin + '] .check-column input[type=checkbox]').attr('disabled', 'disabled');
                jQuery('body table.products tr[data-asin=' + asin + '] .action i').removeClass('fa-plus-circle').addClass('fa fa-edit').closest('a').removeClass('thickbox').attr('target', '_blank').attr('href', jQuery('#at-import-page').attr('data-url') + 'post.php?post='+data['rmessage']['post_id']+'&action=edit');
                jQuery('body table.products tr[data-asin=' + asin + '] .action .quick-import').remove();
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
    var data = {action : 'at_aws_import', asin : asin, func : 'quick-import', '_wpnonce' : nonce};
    var tax_data = {};

    /*
     * Check Taxonomies
     */
    var taxonomy_selects = jQuery('.tabwrapper .at-api-tab#search table.products tfoot .taxonomy-select');
    if(taxonomy_selects.length) {
        var tax_data = {};
        jQuery(taxonomy_selects).find('select').each(function(item) {
            var key = jQuery(this).attr('name');
            var value = jQuery(this).val();
            tax_data[key] = value;
        });
        jQuery(taxonomy_selects).find('input').each(function(item) {
            var key = jQuery(this).attr('name');
            var value = jQuery(this).val();

            if(value.length != 0 && key != undefined) {
                if(jQuery.isArray(tax_data[key])) {
                    tax_data[key].push(value);
                } else {
                    tax_data[key] = value;
                }
            }
        });

        jQuery.extend(data, tax_data);
    }

    jQuery(target).append(' <i class="fa fa-circle-o-notch fa-spin"></i>').addClass('noevent');

    jQuery.ajaxQueue({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: data,
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
                if(curr == 0) {
                    curr = 1;
                }

                var procentual = (100/max_items)*curr;
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

    // break if no product checked
    if(max_items < 1) {
        return false;
    }

    jQuery(ajax_loader).find('span.current').html('0');
    jQuery(ajax_loader).find('.progress-bar').css('width', '0%').html('0%').attr('data-item', 0);
    jQuery(ajax_loader).addClass('active').find('p').html(amazon_vars.import_count + ' ' + max_items);

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
        return;

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
        data: "action=at_aws_grab&url="+encodeURIComponent(url),
        success: function(data){
            jQuery('#grab-link .fa-spin').remove();
            jQuery('#grab-link').attr('disabled', false);
            var asins = data.asins;
            jQuery.each(asins, function( index, value ) {
                if (index != 0) {
                    jQuery('#grabbedasins').val(jQuery('#grabbedasins').val()+"\n"+value);
                }else {
                    jQuery('#grabbedasins').val(value);
                }
            });
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
 * FeedWriteItem
 */
var FeedWriteItem = function(e) {
    if(jQuery('#add-new-keyword input').val().length < 1)
        return;

    var url = jQuery('#add-new-keyword input').val();

    if(!at_amazon_validate_url(url)){
        return;
    }

    jQuery('#add-new-keyword button').attr('disabled', true).append(' <i class="fa fa-circle-o-notch fa-spin"></i>');
    jQuery('#feed-messages').html('');

    var keyword = jQuery('#feed #add-new-keyword input[name=keyword]').val();
    var category = jQuery('#feed #add-new-keyword input[name=category]').val();
    jQuery.ajax({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: "action=at_amazon_feed_write_ajax&keyword=" + encodeURIComponent(keyword) + "&category=" + encodeURIComponent(category),
        success: function(data){
            var status = data.status;
            if(status == 'ok') {
                jQuery('#feed-messages').html('<div class="alert alert-success"><strong>' + keyword + '</strong> ' + amazon_vars.feed_success + '</div>');
                location.reload();
            } else {
                jQuery('#feed-messages').html('<div class="alert alert-error"><strong>' + keyword + '<strong> ' + amazon_vars.feed_fail + '</div>');
            }
            jQuery('#add-new-keyword button .fa-spin').remove();
            jQuery('#add-new-keyword button').attr('disabled', false);
        },
        error: function(data) {
            jQuery('#add-new-keyword button .fa-spin').remove();
            jQuery('#add-new-keyword button').attr('disabled', false);
        }
    });
    e.preventDefault();
};

function at_amazon_validate_url(str) {
    var regex = /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;
    if(!regex .test(str)) {
        alert("Please enter valid URL.");
        return false;
    } else {
        return true;
    }
}

/*
 * Function
 * FeedDeleteItem
 */
var FeedDeleteItem = function(e, id) {
    jQuery('#feed-messages').html('');

    if(id == 'undefined') {
        return;
    }

    jQuery.ajax({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: "action=at_amazon_feed_delete_ajax&id=" + id,
        success: function(data){
            var status = data.status;
            if(status == 'ok') {
                jQuery('#feed-messages').html('<div class="alert alert-success">' + amazon_vars.feed_item_success + '</div>');
                location.reload();
            } else {
                jQuery('#feed-messages').html('<div class="alert alert-error">' + amazon_vars.feed_item_fail + '</div>');
            }
        }
    });
    e.preventDefault();
};

/*
 * Function
 * FeedChangeStatus
 */
var FeedChangeStatus = function(e, id, status) {
    jQuery('#feed-messages').html('');

    if(id == 'undefined') {
        return;
    }

    jQuery.ajax({
        url: ajaxurl,
        dataType: 'json',
        type: 'POST',
        data: "action=at_amazon_feed_change_status_ajax&id=" + id + "&status=" + status,
        success: function(data){
            var status = data.status;
            if(status == 'ok') {
                jQuery('#feed-messages').html('<div class="alert alert-success">' + amazon_vars.feed_update_success + '</div>');
                location.reload();
            } else {
                jQuery('#feed-messages').html('<div class="alert alert-error">' + amazon_vars.feed_update_fail + '</div>');
            }
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
            jQuery('#checkConnection').append('<div class="alert alert-danger">' + amazon_vars.adblocker_hint + '</div>');
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

/*
 * Select2 for Taxonomy-Selects
 */
jQuery(document).ready(function() {
    jQuery("table .taxonomy-select select").select2();
    jQuery("table .taxonomy-select .col-xs-6").unwrap().removeClass('col-xs-6');
});

/*
 * Feed Functions
 */
jQuery(document).ready(function() {
    jQuery('table.feed .handle').click(function() {
        jQuery(this).parent().parent().next().slideToggle("fast");
        jQuery(this).parent().parent().toggleClass('closed');
    });

    /*
     * Dynamic Field
     */
    jQuery('#at-import-window #category').change(function() {
        var value = jQuery(this).val();

        if(value) {
            jQuery('#at-import-window .form-dynamic-field').each(function() {
                var hide_on = jQuery(this).data('hide-on').split(',');

                if(jQuery.inArray(value, hide_on) !== -1) {
                    jQuery(this).hide();
                } else {
                    jQuery(this).show();
                }
            });
        }
    });

    jQuery('#amazon_images_external').click(function() {
        if(jQuery(this).is(':checked')) {
            jQuery('.toggle_amazon_images_external').show();
        } else {
            jQuery('.toggle_amazon_images_external').hide();
        }
    });

    jQuery('table.feed form.edit-feed-item').submit(function(e) {
        var form = jQuery(this);
        var action = 'at_amazon_feed_change_settings_ajax';
        var id = jQuery(this).parent().parent().prev().attr('data-id');
        var post_status = jQuery(this).find('select[name=post_status]').val();
        var images = jQuery(this).find('select[name=images]').val();
        var description = jQuery(this).find('select[name=description]').val();
        var category = jQuery(this).find('input[name=category]').val();
        var data = {action : action, id : id, post_status : post_status, images : images, description : description, category : category};
        var tax_data = {};

        /*
         * Check Taxonomies
         */
        var taxonomy_selects = jQuery(this).find('.taxonomy-select');
        if(taxonomy_selects.length) {
            var tax_data = {};
            jQuery(taxonomy_selects).find('select').each(function(item) {
                var key = jQuery(this).attr('name');
                var value = jQuery(this).val();
                tax_data[key] = value;
            });
            jQuery(taxonomy_selects).find('input').each(function(item) {
                var key = jQuery(this).attr('name');
                var value = jQuery(this).val();

                if(value != undefined && key != undefined) {
                    tax_data[key] = value;
                }
            });
            jQuery.extend(data, tax_data);
        }

        jQuery(form).find('#form-messages').html('');
        jQuery(form).find('button').attr('disabled', true).append(' <i class="fa fa-circle-o-notch fa-spin"></i>');

        jQuery.ajaxQueue({
            url: ajaxurl,
            dataType: 'json',
            type: 'POST',
            data: data,
            success: function(data){
                var status = data.status;

                if(status == 'ok') {
                    jQuery(form).find('#form-messages').html('<div class="alert alert-success">' + amazon_vars.feed_update_success + '</div>');
                    location.reload();
                } else {
                    jQuery(form).find('#form-messages').html('<div class="alert alert-error">' + amazon_vars.feed_update_fail + '</div>');
                }

                jQuery(form).find('.fa-spin').remove();
                jQuery(form).find('button').attr('disabled', false);
            },
            error : function() {
                jQuery(form).find('.fa-spin').remove();
                jQuery(form).find('button').attr('disabled', false);

                return false;
            },
        });

        return false;
    });


    jQuery('.notice[data-action="force-dismiss"] .notice-dismiss').live('click', function(e) {
        var option = jQuery(this).data('name');
        jQuery.ajax({
            url: ajaxurl,
            dataType: 'json',
            type: 'POST',
            data: "action=at_amazon_set_option&option=" + option + "&value=dismissed",
            success: function(data){}
        });
        e.preventDefault();
    });
});

