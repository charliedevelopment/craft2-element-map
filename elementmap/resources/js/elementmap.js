(function() {

	// Make sure multiple maps aren't created as the javascript is reloaded.
	if ($('#elementmap').length) {
		return;
	}

	function getIcon(type) {
		switch(type) {
			case 'entry':
				return 'section';
			case 'globalset':
				return 'globe';
			case 'category':
				return 'categories';
			case 'tag':
				return 'tags';
			case 'asset':
				return 'assets';
			default:
				return 'world';
		}
	}

	function makeElementForItem(item) {
		return $('<li><a target="_blank" href="' + item.url + '" data-id="' + item.id + '" data-locale="' + Craft.locale + '" data-editable>' + item.title + '</a><span data-icon="' + getIcon(item.type) + '"></span></li>');
	}

	var pane = $('#settings.pane.meta').closest('.item').find('.pane').last();
	if (pane.length <= 0) {
		return;
	}
	var newpane = $('<div id="elementmap" class="pane meta"></div>');
	pane.after(newpane);
	newpane.append('<h3>References</h3>');
	var fromentries = $('<ul class="entrycolumn"></ul>').appendTo(newpane);
	fromentries.append('<li class="heading">Incoming:</li>');
	var toentries = $('<ul class="entrycolumn"></ul>').appendTo(newpane);
	toentries.append('<li class="heading">Outgoing:</li>');

	var data = {};
	data[Craft.csrfTokenName] = Craft.csrfTokenValue;
	data.id = $('#main input[name="entryId"]').val() || $('#main input[name="categoryId"]').val();
	$.ajax({
		url: '/actions/elementMap/getElementMap',
		method: 'GET',
		data: data,
		dataType: 'json',
	}).done(function(data) {
		$.each(data.from, function(index, item) {
			fromentries.append(makeElementForItem(item));
		});
		$.each(data.to, function(index, item) {
			toentries.append(makeElementForItem(item));
		});
	}).fail(function() {
		newpane.remove();
	});
})();