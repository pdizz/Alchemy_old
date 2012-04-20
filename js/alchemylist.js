$(document).ready(function() {
    var ings = ["Bee", "Beehive", "Beeswax", "Honey", "Honeycomb"];
    
    $ingredients = $('<ul/>', { id : "ingredients", 'class' : "selectable" });
    $ingredients.appendTo('body');
    
    $effects = $('<ul/>', { id : "effects", 'class' : "selectable" });
    $effects.appendTo('body');
    
    var o = '';
    for(var i=0; i<ings.length; i++) {
        o += '<li class="ui-widget-content">' + ings[i] + '</li>';
    }
    $ingredients.html(o);
    
    $ingredients.selectable({
        stop : function() {
            var o = '';
            $('.ui-selected').each(function() {
                o += '<li class="ui-widget-content">' + $(this).text() + '</li>';
            });
            $effects.html(o);
        }
    });
    
    $effects.selectable();
    
});