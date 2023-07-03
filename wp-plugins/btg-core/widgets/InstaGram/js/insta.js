(function(){
    new InstagramFeed({
        'username': insta.username,
        'tag': insta.tag,
        'container': document.getElementById(insta.id),
        'display_profile': insta.display_profile,
        'display_biography': insta.display_biography,
        'display_gallery': true,
        'callback': null,
        'styling': true,
        'items': insta.items,
        'items_per_row': insta.items_per_row,
        'margin': insta.margin,
        'image_size': insta.image_size,
		'host': 'https://images' + ~~(Math.random() * 3333) + '-focus-opensocial.googleusercontent.com/gadgets/proxy?container=none&url=https://www.instagram.com/'
    });
})();