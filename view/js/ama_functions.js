jQuery(document).ready(function() {
    var globalRequest = 0;
    
	jQuery("input[type=checkbox].unique").live('click', function() {
    	jQuery("input[type=checkbox].unique").each(function() {
			jQuery(this)[0].checked = false;
		});
		jQuery(this)[0].checked = true;
	});
	
	jQuery("input[type=checkbox].disable-this").live('click', function() {
		if(jQuery(this).attr('checked')){
			jQuery(this).parent().parent().parent().parent().parent().css('opacity', '0.5');
		} else {
			jQuery(this).parent().parent().parent().parent().parent().css('opacity', '1');
		}
	});

	//search
    jQuery('#search').bind('keyup', function(event) {
        if (event.keyCode == 13) {
            searchAction();
        }
    });
    
    jQuery('#search-link').bind('click', function(event) {
        searchAction();
    });
        
    //pagination
	//todo: reset hidden page number on new search
    jQuery('#next-page').bind('click', function(event) {
		var current_page = parseInt(jQuery('#page').val());
		var max_pages = parseInt(jQuery('#max-pages').val());
		
		jQuery(this).attr('disabled', true);
		
		if(current_page < max_pages) {
			jQuery('#page').val(current_page + 1);
		} 
    	searchAction();
    });
    
    jQuery('#prev-page').bind('click', function(event) {
		var current_page = parseInt(jQuery('#page').val());
		var max_pages = parseInt(jQuery('#max-pages').val());
		
		jQuery(this).attr('disabled', true);
		
		if(current_page <= max_pages) {
			jQuery('#page').val(current_page - 1);
		} 
        searchAction();
    });
	
	//mass import
    jQuery('.import-product:not(.noevent)').live('click', function(event) {
       massImportAction(this);
        
       event.preventDefault();
    });
    

    //single import
    jQuery('.single-import-product:not(.noevent)').live('click', function(event) {
	   singleImportAction(this);
      
       event.preventDefault();
    });
    
   var singleImportAction = function(target) {
   		jQuery(target).append(' <i class="fa fa-circle-o-notch fa-spin"></i>').addClass('noevent');
    	var data = jQuery( 'form#import-product' ).serialize();
		jQuery.ajax({
        	url: "admin-ajax.php",
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
		    		jQuery(target).after('<a class="button button-primary" href="'+jQuery('#affiliatetheme-page').attr('data-url')+'post.php?post='+data['rmessage']['post_id']+'&action=edit">Produkt bearbeiten</a>');
		    		jQuery(target).after('<div class="updated"><a href="'+jQuery('#affiliatetheme-page').attr('data-url')+'post.php?post='+data['rmessage']['post_id']+'&action=edit">Produkt</a> wurde erfolgreich angelegt.</div>');
		    		jQuery('body table.produkte tr[data-asin='+asin+']').addClass('success');
					jQuery('body table.produkte tr[data-asin='+asin+'] .check-column input[type=checkbox]').attr('disabled', 'disabled');
					jQuery('body table.produkte tr[data-asin='+asin+'] .aktion i').removeClass('fa-plus-circle').addClass('fa-check-circle');
		    	}
		    }
		});
    };
    
    var massImportAction = function(target) {
    	jQuery(target).html('<i class="fa fa-circle-o-notch fa-spin"></i>').addClass('noevent');
    	setTimeout(function() {
    		jQuery(target).html('<i class="fa fa-check-circle"></i>').parent().parent().addClass('success');
    	}, 1500); 
    };

    var searchAction = function() {
        jQuery('#search-link').attr('disabled', true).append(' <i class="fa fa-circle-o-notch fa-spin"></i>');
        
        var value = jQuery('.amazon-api-cont #search').val();
        var cat = jQuery('.amazon-api-cont #category').val();
        var country = jQuery('#amazon_country').val();
        var condition = jQuery('.amazon-api-cont #condition').val();
        var page = jQuery('.amazon-api-cont #page').val();
        var track = value + " - " + cat + " - " + country;
        var resultContainer = jQuery('.amazon-api-cont #results');

        if (value.length < 3 && globalRequest == 1) {
            return;
        }

        globalRequest = 1;
        jQuery.ajax({
            url: "admin-ajax.php",
            dataType: 'json',
            type: 'GET',
            data: "action=amazon_api_search&q="+value+"&category="+cat+"&country="+country+"&condition="+condition+"&page="+page,
            success: function(data){
            	var totalpages = '5'; // TODO:data['rmessage']['totalpages'];
            	//jQuery('#info-title').html('<h4>Es wurden '+totalpages+' Seiten gefunden.</h4>');
            	jQuery('#max-pages').val(totalpages);
            	if(totalpages == 1) {
            		jQuery('#page-links').hide();
            	} else if(totalpages > 1) {
            		jQuery('#page-links').show();
            		if(page == 1) { jQuery('#page-links #prev-page').hide(); } else if(page > 1) { jQuery('#page-links #prev-page').show(); }
            		if(page >= totalpages) { jQuery('#page-links #next-page').hide(); }
            	} 
            	
                globalRequest = 0;
                resultContainer.fadeOut('fast', function() {
                    resultContainer.html('');

					if(data['items']) {
	                    for (var x in data['items']) {
	                        if (!data['items'][x].price)
	                            data['items'][x].price = 'kA';
	
	                        if (!data['items'][x].img)
	                            data['items'][x].img = 'assets/images/no.gif';
	                            
	                        var html = '';
							
							 if(data['items'][x].exists == "true") {
	                        	html += '<tr class="item success" data-asin="'+data['items'][x].asin+'">';
	                        	html += '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-'+data['items'][x].asin+' name="item[]" value="'+data['items'][x].asin+'" disabled="disabled"></th>';
	                        } else {
	                        	html += '<tr class="item" data-asin="'+data['items'][x].asin+'">';
	                        	html += '<th scope="row" class="check-column"><input type="checkbox" id="cb-select-'+data['items'][x].asin+' name="item[]" value="'+data['items'][x].asin+'"></th>';
	                        }
	                        html += '<td class="asin">'+data['items'][x].asin+'</td>';
	                        if(data['items'][x].img !="assets/images/no.gif") {
	                       	 	html += '<td class="image"><img src="'+data['items'][x].img+'"></td>';
	                       	} else {
	                       		html += '<td class="image">Kein Bild vorhanden</td>';
	                       	}
	                        html += '<td class="title"><a href="'+data['items'][x].url+'" target="_blank">'+data['items'][x].Title+'</a></td>';
	                        html += '<td class="description">'+data['items'][x].edi_content+'</td>';
	                        html += '<td class="rating">'+data['items'][x].average_rating+' / 5</td>';
	                        html += '<td class="price">'+data['items'][x].price+'</td>';
	                        html += '<td class="category">'+data['items'][x].category+'</td>';
	                       	if(data['items'][x].exists == "true") {
	                       		html += '<td class="aktion"><a href="#" class="noevent" title="Importieren"><i class="fa fa-check-circle"></i></a></td>';
		                   	} else {
		                       	html += '<td class="aktion"><a href="'+jQuery('#affiliatetheme-page').attr('data-url')+'admin-ajax.php?action=amazon_api_lookup&func=modal&asin='+data['items'][x].asin+'&height=700&width=820" class="thickbox" title="Importieren"><i class="fa fa-plus-circle"></i></a></td>';
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
                    jQuery('#search-link .fa-spin').remove();
                    jQuery('#search-link').attr('disabled', false);
                });

            }
        });
    };
    
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
            url: "admin-ajax.php",
            dataType: 'json',
            type: 'GET',
            data: "action=amazon_api_search&q="+value+"&category="+cat+"&country="+country+"&condition="+condition+"&page="+page,
            success: function(data){
            	var totalpages = data['rmessage']['totalpages'];
            	globalRequest = 0;
            	if(totalpages > 0) {
	               
	                resultContainer.fadeOut('fast', function() {
	                 	resultContainer.append('<div class="updated"><p class="success">Verbindung erfolgreich hergestellt.</p></div>');
	                    resultContainer.fadeIn('fast');
	                });
	            } else {
	            	resultContainer.fadeOut('fast', function() {
	                 	resultContainer.append('<div class="error"><p class="error">Eine Verbindung zu Amazon konnte nicht hergestellt werden. Bitte prüfe deinen Public Key, Secret Key und deine Partner ID.</p></div>');
	                 	jQuery('#affiliatetheme-settings .inside').slideToggle();
	                 	var btn = jQuery('#affiliatetheme-settings  .toggle-settings');
	                 	if(btn.html() == '(anzeigen)') {
							jQuery(btn).html('(ausblenden)');
						} else {
							jQuery(btn).html('(anzeigen)');
						}
	                    resultContainer.fadeIn('fast');
	                });
	            }
	            				
				if(data['rmessage']['errormsg'] != "") {
					resultContainer.append('<div class="error"><p class="error">'+data['rmessage']['errormsg']+'</p></div>');
				}
            
	            jQuery('#search-link').attr('disabled', false);
	            jQuery('.status-after').remove();
            },
			error: function() {
				resultContainer.fadeOut('fast', function() {
					resultContainer.append('<div class="error"><p class="error">Eine Verbindung zu Amazon konnte nicht hergestellt werden. Bitte prüfe deinen Public Key, Secret Key und deine Partner ID.</p></div>');
					jQuery('#affiliatetheme-settings .inside').slideToggle();
					var btn = jQuery('#affiliatetheme-settings  .toggle-settings');
					if(btn.html() == '(anzeigen)') {
						jQuery(btn).html('(ausblenden)');
					} else {
						jQuery(btn).html('(anzeigen)');
					}
					resultContainer.fadeIn('fast');
				});
			},
        });
    };
    checkConnection();
    
    jQuery(function($) {
		$(document).ajaxStop(function() {
			 jQuery('#search-link .fa-spin').remove();
			 jQuery('#next-page, #prev-page').attr('disabled', false);
		});
	});
});