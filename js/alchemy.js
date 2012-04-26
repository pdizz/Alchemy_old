$(document).ready(function() {
    
    $app = 'body'; //Parent div for aplication
        
    $form = $('<form/>', {
        id : "filter"
    });

    $starts = $('<input type="checkbox" checked="yes" id="starts"><label for="starts">Starts with (uncheck to search anywhere in string)</label></input>');

    $filter = $('<input/>', {
        type : 'text',
        id : 'ingfilter',
        size : '30px'
    });
    
    $ingredients = $('<ul/>', {
        id : "ingselect",
        'class' : 'selectable',
        html : '<li>Loading</li><li>ingredients</li><li>...</li>'
    });

    
    $effects = $('<ul/>', {
        id : "effselect",
        'class' : 'selectable',
        html : '<li>Select</li><li>effects</li><li>here</li>'
    });

    
    $recipe = $('<dl/>', {
        id : "recipe",
        html : '<h1>Recipe</h1><p>This will show the ingredients in your inventory which have the selected effects</p>'
    });
    
    $form.appendTo($app);
    $starts.appendTo('#filter');
    $filter.appendTo('#filter');    
    $ingredients.appendTo($app);
    $recipe.appendTo($app);
    $effects.appendTo($app);
    
    
    /****************App********************/
    
    //ing filter
    $form.bind('keyup click', function(){
        $filter.focus();        
        var re;
        if($('#starts').is(':checked')) re = new RegExp('^' + $filter.val(), 'i');
        else re = new RegExp($filter.val(), 'i');
        
        $('#ingselect .ui-widget-content').each(function(){            
            if(re.test($(this).text())) $(this).show();
            else $(this).hide();
        });
    });
    
    //Load all ingredients and append DOM elements
    $.ajax({
        url : 'php/alchemy.php',
        data : {
            ing : 'all',
            json : true
        },
        type : 'GET',
        dataType : 'json',
        success : function(json) {


            var opts = '';
            $.each(json, function(val, text) {
                opts += '<li class="ui-widget-content">' + text + '</li>';
                $ingredients.html(opts);
            });
        }
    });
    
    //load all effects
    $.ajax({
        url : 'php/alchemy.php',
        data : {
            eff : 'all',
            json : true
        },
        type : 'GET',
        dataType : 'json',
        success : function(json) {
            var opts = '';
            $.each(json, function(val, text) {
                opts += '<li class="ui-widget-content">' + text + '</li>';
                $effects.html(opts);
            });
        }
    });
    
    //ingredients select event
    $ingredients.selectable({
        stop : function() {
            $effects.html('');
            $recipe.html('<h1>Recipe</h1><p>This will show the ingredients in your inventory which have the selected effects</p>');
            var selected = [];
            
            $('.ui-selected', this).each(function () {
                selected.push($(this).text());
            });

            $.ajax({
                url : 'php/alchemy.php',
                data : {
                    ing : selected.join(','),
                    json : true
                },
                type : 'GET',
                dataType : 'json',
                success : function(json) {
                    //console.log(json);
                    var opts = '';
                    for(var i=0; i<json.length; i++) {
                        if(json[i]['value'] > 1) {
                            opts += '<li class="ui-widget-content">' + json[i]['eff_name'] + '</li>';
                        }
                    }
                    $effects.html(opts); 
                }
            });
        } //END stop
    });
   
    
    $effects.selectable({
        stop : function() {
            $recipe.html('');
            var selected = [];
            $('.ui-selected', this).each(function () {
                selected.push($(this).text());
            });
            $.ajax({
                url : 'php/alchemy.php',
                data : {
                    eff : selected.join(','),
                    json : true
                },
                type : 'GET',
                dataType : 'json',
                success : function(json) {
                    //console.log(json);
                    var opts = '';
                    var selectedIngs = [];
                    $('#ingselect .ui-selected').each(function () {
                        selectedIngs.push($(this).text());
                    });
                    //console.log(selectedIngs);
                    for(var i=0; i<json.length; i++) {
                        if(selectedIngs.indexOf(json[i]['ing_name']) !== -1 || selectedIngs.length < 1) {
                            /************INSERT IMAGE**********************/
                            //opts += '<img src="images/no.jpg"></img>';
                            
                            if(json[i]['link']) {
                                opts += '<dt class="link">' + json[i]['ing_name'] + '<dt>';
                            }
                            else {
                                opts += '<dt>' + json[i]['ing_name'] + '</dt>';
                            }
                            opts += '<dd>Active Effect: <span>' + json[i]['eff_name'] 
                            + '</span></dd>';
                            opts += '<dd>Weight: ' + json[i]['weight'] + '</dd>';
                            opts += '<dd>Value: ' + json[i]['value'] + '</dd>';
                            opts += '<dd>Location: ' + json[i]['location'] + '</dd>';
                        }
                    }
                    $recipe.html(opts); 
                }
            });
        }
    });
});
