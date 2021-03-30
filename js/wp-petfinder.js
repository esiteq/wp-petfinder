jQuery(document).ready(function($)
{
    $('.animal-gallery-slick-thumb').magnificPopup(
    {
        type: 'image'
    });
    //
    $('img.animal-thumb').click(function(e)
    {
        e.preventDefault();
        var id = $(this).parent().find('.animal-dots-container').attr('id');
        var gallery = new Array();
        $('div#'+id+'>a').each(function(i,v)
        {
            var img = $(this).attr('data-image');
            gallery.push({ src: img });
        });
        if(gallery.length > 0)
        {
            $.magnificPopup.open(
            {
                items: gallery,
                gallery:
                {
                    enabled: true
                },
                type: 'image',
            });
        }
    });
    //
    $('.animal-dots-container').each(function()
    {
        $(this).find('a:first-child').addClass('active');
    });
    //
    $('a.animal-dot').click(function(e)
    {
        e.preventDefault();
        var img = $(this).attr('data-image');
        var target = $(this).attr('data-target');
        var $parent = $(this).parent();
        $parent.find('a').removeClass('active');
        $(this).addClass('active');
        $('#'+target).attr('src', img);
    });
    // gallery carousel
    //$(".carousel").carousel();
});