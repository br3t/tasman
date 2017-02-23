var tasman = {
	apiPath: "api.php"
};

tasman.getAll = function() {
	$.ajax({
		url: tasman.apiPath,
		method: "GET",
		dataType: "json",
		data: {
			action: "get",
			entity: "project"
		},
		success: function(respond) {
			var compiledTpl = _.template($("#project-template").text());
			for(var i in respond) {
				var projectHtml = compiledTpl(respond[i]);
				$("#projects-wrapper").append(projectHtml);
			}
		}
	});
};


$(document).ready(function() {
	tasman.getAll();

	//* toggle tasks visibility
	$('body').on('click', '.task-visibility:not(.active)', function() {
		var panel = $(this).closest('.panel-body');
		panel.find('.task-visibility').removeClass('active');
		$(this).addClass('active');
		if($(this).attr('data-tasks') == 'undone') {
			panel.find('.done').hide();
		} else {
			panel.find('.done').show();
		}
	});

	//* set project id for new task
	$('body').on('click', '.add-task', function() {
		var projectId = $(this).closest('.project').attr('data-id');
		$('#projectToAdd').val(projectId);
	});
});