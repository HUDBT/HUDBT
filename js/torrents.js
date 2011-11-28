$(function() {
    var target = $('#torrents>tbody');
    var targetH = target.height();
    var surfix = '&format=xml';
    var lang = hb.constant.lang;

    var get_func = function(res_x) {
	console.log((new Date()).getTime());
	$("#pagerbottom").after($('<div></div>').addClass('pages')).hide();

	var res = $(res_x);

	var catDict = hb['constant'].cat_class;
	res.find('torrent').each(function() {
	    var torrent = $(this);
	    var id = torrent.attr('id');
	    var tr = $('<tr></tr>');

	    var catid = torrent.find('catid').text();
	    var catProp = catDict[catid];
	    var cat = $('<td></td>');
	    if (catProp) {
		cat.addClass("nowrap category-icon").append($('<a></a>', {
		    href : '?cat=' + catid
		}).append($('<img />', {
		    src : "pic/cattrans.gif",
		    alt : catProp.name,
		    title : catProp.name,
		}).addClass(catProp.class_name)));
	    }
	    tr.append(cat);

	    var title_td = $('<td></td>').addClass('torrent');
	    var title = $('<div></div>');
	    var bookmarked = (torrent.find('bookmarked').length !== 0);
	    var bookmarkClass = bookmarked ? 'bookmark' : 'delbookmark';

	    var mainTitle = $('<h2></h2>').append($('<a></a>', {
		href : 'details.php?id=' + id + '&hit=1',
		title : torrent.find('name').text(),
		text : torrent.find('name').text()
	    }));

	    var desc = $('<h3></h3>', {
		text : torrent.find('desc').text()
	    });

	    title.append($('<div></div>').addClass('limit-width').append(mainTitle)).append($('<div></div>').addClass('limit-width').append(desc));

	    if (torrent.find('oday').length !== 0) {
		var oday = $('<img></img>', {
		    src : 'pic/ico_0day.gif',
		    alt : lang.text_oday,
		    title : lang.text_oday
		});
		mainTitle.after(oday);
	    }

	    if (torrent.find('banned').length !== 0) {
		var oday = $('<span></span>', {
		    text : '('
		}).append($('<span></span>', {
		    text : lang.text_banned,
		    'class' : 'striking'
		})).append(')');
		mainTitle.after(oday);
	    }

	    var pr = torrent.find('pr');
	    if (pr.length !== 0) {
		var state = pr.attr('state');
		var prDict = hb.constant.pr[state - 1];
		var prLabel = $('<img></img>', {
		    'class' : prDict.name,
		    alt : lang[prDict.lang],
		    src : 'pic/trans.gif'
		});
		mainTitle.after(prLabel);

		var expire = pr.find('expire');
		var $expire = $('<span></span>', {text : '['});
		var $time = $('<span></span>', {text : lang.text_will_end_in});
		if (expire.length !== 0) {
		    $time.attr('title', expire.find('raw').text());
		    $time.append(decodeURIComponent(expire.find('canonical').text()));
		    $time.addClass('pr-limit');
		}
		else {
		    $time.append(lang.text_forever);
		    $time.addClass('pr-eternal');
		}
		$expire.append($time).append(']');
		desc.after($expire);
	    }

	    var picktype = torrent.find('picktype');
	    if (picktype.length !== 0) {
		var ptype = picktype.text()
		var $ptype = $('<span></span>', {text : '['}).append($('<span></span>', {
		    text : lang['text_' + ptype],
		    'class' : ptype
		})).append(']');
		mainTitle.after($ptype);
	    }


	    title.append($('<div></div>').addClass('torrent-utilty-icons minor-list-vertical').append($('<ul></ul>').append($('<li></li>').append($('<a></a>', {
		href : 'download.php?id=' + id + '&hit=1',
	    }).append($('<img></img>', {
		src : "pic/trans.gif",
		alt : 'download',
		title : 'title_download_torrent'
	    }).addClass('download')))).append($('<li></li>').append($('<a></a>', {
		id : 'bookmark' + id,
		href : 'javascript: bookmark(' + id + ');'
	    }).append($('<img></img>', {
		src : "pic/trans.gif",
	    }).addClass(bookmarkClass))))));
	    title_td.append(title);
	    tr.append(title_td);

	    var addNumber = function(str, hrefZero, href) {
		var comment = $('<td></td>').addClass('rowfollow');
		var comments_num = parseInt(str);
		var href;
		if (comments_num === 0) {
		    href = hrefZero;
		}

		var $a = $('<a></a>', {
		    text : str
		});
		if (href !== undefined && href !== '') {
		    $a.attr('href', href);
		}

		comment.append($a);

		if (comments_num !== 0) {
		    $a.addClass('important');
		}

		return comment;
	    }

	    tr.append(addNumber(torrent.find('comments').text(), 'comment.php?action=add&pid=' + id + '&type=torrent', 'details.php?id=' + id + '&hit=1&cmtpage=1#startcomments'));

	    var time = $('<td></td>').addClass('rowfollow');
	    time.text(torrent.find('added').text());
	    tr.append(time);

	    var size = $('<td></td>').addClass('rowfollow');
	    size.html(decodeURIComponent(torrent.find('size canonical').text()));
	    tr.append(size);

	    var seeders = addNumber(torrent.find('seeders').text(), '', 'details.php?id=' + id + '&hit=1&dllist=1#seeders');
	    if (torrent.find('seeders').text() === '0') {
		seeders.find('a').addClass('no-seeders');
	    }
	    tr.append(seeders);

	    var leechers = addNumber(torrent.find('leechers').text(), '', 'details.php?id=' + id + '&hit=1&dllist=1#leechers');
	    tr.append(leechers);

	    var completed = addNumber(torrent.find('times_completed').text(), '', 'viewsnatches.php?id=' + id);
	    tr.append(completed);

	    var towner = $('<td></td>').addClass('rowfollow');
	    var owner = torrent.find('owner');
	    if (owner.attr('anonymous') === 'true') {
		towner.append(lang.text_anonymous);
	    }
	    
	    var user_out = function(user) {
		var out = $('<span></span>').addClass('nowrap').append($('<a></a>', {
		    href : 'userdetails.php?id=' + user.attr('id'),
		    text : user.find('username').text()
		}).addClass(user.find('canonicalClass').text() + '_Name').addClass('username'));

		if (user.find('donor').text() === 'true') {
		    out.append($('<img></img>', {
			src : "pic/trans.gif",
			alt : 'Donor'
		    }).addClass('star'));
		}
		return out;
	    };

	    var user = owner.find('user');
	    if (user.length !== 0) {
		if (owner.attr('anonymous') === 'true') {
		    towner.append('<br />(');
		}
		towner.append(user_out(user));
		if (owner.attr('anonymous') === 'true') {
		    towner.append(')');
		}
	    }
	    tr.append(towner);

	    if (hb.config.user['class'] >= hb.constant.torrentmanage_class) {
		var edit = $('<td></td>').addClass('rowfollow');
		edit.append($('<div></div>').addClass('minor-list-vertical').append($('<ul></ul>').append($('<li></li>').append($('<a></a>', {
		    href : 'fastdelete.php?id=' + id
		}).append($('<img></img>', {
		    alt : 'D',
		    title : lang.text_delete,
		    src : 'pic/trans.gif'
		}).addClass('staff_delete')))).append($('<li></li>').append($('<a></a>', {
		    href : 'edit.php?id=' + id + '&returnto=' + encodeURIComponent(document.location.pathname + document.location.search)
		}).append($('<img></img>', {
		    alt : 'E',
		    title : lang.text_edit,
		    src : 'pic/trans.gif'
		}).addClass('staff_edit'))))));
		tr.append(edit);
	    }
	    
	    target.append(tr);
	});



	var cont = res.find('continue');
	if (cont.length !== 0) {
	    var uri = cont.text() + surfix;

	    var targetH = target.height();
	    $(document).scroll(function() {
		var loc = $(document).scrollTop() + $(window).height();
		if(loc > targetH) {
		    $.get(uri, get_func);
		    $(document).unbind('scroll');
		}
	    });
	}
	console.log((new Date()).getTime());
    }

    if (hb.nextpage !== '') {
	$(document).scroll(function(){
            var loc = $(document).scrollTop() + $(window).height();
            if(loc > targetH) {
		var uri = hb.nextpage + surfix;
		uri = 'http://211.69.198.67/hudbt/torrents.php?inclbookmarked=0&incldead=0&spstate=0&page=0&format=xml'

		$.get(uri, get_func);
		$(document).unbind('scroll');
    	    }
	});
    }
});