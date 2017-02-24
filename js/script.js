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
			tasman.renderProjects(respond);
		}
	});
};

tasman.renderProjects = function(projectList) {
	var compiledTpl = _.template($("#project-template").text());
	for(var i in projectList) {
		var projectHtml = compiledTpl(projectList[i]);
		$("#projects-wrapper").append(projectHtml);
	}
};


$(document).ready(function() {
	//* static project for demo
	tasman.renderProjects({
		0: {
			id: 0,
			name: 'Static DEMO project',
			tasks: [
				{id: -1, name: 'Task -1', status: true },
				{id: -2, name: 'Task -2', status: false }
			]
		}
	});

	//* load real projects
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

	//* 
	$('body').on('change input keydown', '#newProjectName', function() {
		$(this).closest(".modal-body").find(".help-info").text("");
	});
	//* save new project
	$('body').on('click', '.create-project', function() {
		var newProjectNameInput = $("#newProjectName");
		var newProjectName = newProjectNameInput.val();
		var helpInfo = $("#newProjectName").closest(".modal-body").find(".help-info");
		if(newProjectName.length <3) {
			helpInfo.text("Project title should be at least 3 symbols length");
		} else {
			$.ajax({
				url: tasman.apiPath,
				method: "GET",
				dataType: "json",
				data: {
					action: "create",
					entity: "project",
					name: newProjectName
				},
				success: function(respond) {
					if(respond.error) {
						helpInfo.text(respond.error);
					} else {
						tasman.renderProjects(respond);
						$('#add-project').modal('hide');
						newProjectNameInput.val('');
					}
				}
			});
		}
	});

	//* rm project init
	$('body').on('click', '.remove-project-init', function() {
		var project = $(this).closest('.project');
		var projectId = project.attr('data-id');
		var projectName = project.find('.project-name').text();
		$('#remove-project .project-name').text(projectName);
		$('#rmProjectId').val(projectId);
	});

	//* rm project
	$('body').on('click', '.remove-project', function() {
		$.ajax({
			url: tasman.apiPath,
			method: "GET",
			dataType: "json",
			data: {
				action: "remove",
				entity: "project",
				project_id: $('#rmProjectId').val()
			},
			success: function(respond) {
				if(respond.error) {
					helpInfo.text(respond.error);
				} else {
					$('.project[data-id=' + respond.project_id + ']').remove();
					$('#remove-project').modal('hide');
				}
			}
		});
	});
});