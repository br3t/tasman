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
			$('.task-group').sortable({
				handle: '.change-task-priority',
				placeholder: 'row list-group-item task-placeholder',
				update: function( event, ui ) {
					var taskIds = [];
					ui.item.closest('.task-group').find('.task').each(function() { 
						taskIds.push($(this).attr('data-id'));
					});
					tasman.reorderTasks(taskIds);
				}
			});
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
tasman.renderTask = function(task) {
	var compiledTpl = _.template($("#task-template").text());
	return compiledTpl(task);
};
tasman.reorderTasks = function(taskIds) {
	console.log(taskIds);
	$.ajax({
		url: tasman.apiPath,
		method: "GET",
		dataType: "json",
		data: {
			action: "reorder",
			entity: "task",
			taskByPriority: taskIds.join(',')
		},
		success: function(respond) {
		}
	});
};


$(document).ready(function() {
	//* static project for demo
	if(location.hash == '#demo') {
		tasman.renderProjects({
			0: {
				id: 0,
				name: 'Static DEMO project',
				tasks: [
					{id: -1, name: 'Task -1', status: true, priority: 10 },
					{id: -2, name: 'Task -2', status: false, priority: 1, deadline: '2017-03-01' }
				]
			}
		});
	}
	

	//* load real projects
	tasman.getAll();


	$('[data-wrapper=newTaskDeadline]').datetimepicker({
		format: "YYYY-MM-DD"
	});

	//* toggle tasks visibility
	$('body').on('click', '.task-visibility:not(.active)', function() {
		var panel = $(this).closest('.panel-body');
		panel.find('.task-visibility').removeClass('active');
		$(this).addClass('active');
		if($(this).attr('data-tasks') == 'undone') {
			panel.addClass('show-undone-only');
		} else {
			panel.removeClass('show-undone-only');
		}
	});

	//* 
	$('body').on('change input keydown', '#newProjectName,#newTaskName', function() {
		$(this).closest(".modal-body").find(".help-info").hide().text("");
	});
	//* save new project
	$('body').on('click', '.create-project', function() {
		var newProjectNameInput = $("#newProjectName");
		var newProjectName = newProjectNameInput.val();
		var helpInfo = newProjectNameInput.closest(".modal-body").find(".help-info");
		if(newProjectName.length <3) {
			helpInfo.show().text("Project name should be at least 3 symbols length");
			newProjectNameInput.focus();
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

	//* add task init
	$('body').on('click', '.add-task-init', function() {
		$('#add-task .help-info').hide().text("");
		$('#add-task-form').trigger("reset");

		var projectId = $(this).closest('.project').attr('data-id');
		$('#projectToAddId').val(projectId);
	});

	//* add task
	$('body').on('click', '.create-task', function() {
		var newTaskNameInput = $("#newTaskName");
		var newTaskName = newTaskNameInput.val();
		var helpInfo = newTaskNameInput.closest(".modal-body").find(".help-info");
		if(newTaskName.length <3) {
			helpInfo.show().text("Task name should be at least 3 symbols length");
			newTaskNameInput.focus();
		} else {
			$.ajax({
				url: tasman.apiPath,
				method: "GET",
				dataType: "json",
				data: {
					action: "create",
					entity: "task",
					name: newTaskName,
					project_id: $('#projectToAddId').val(),
					deadline: $('#newTaskDeadline').val() || 0
				},
				success: function(respond) {
					if(respond.error) {
						helpInfo.show().text(respond.error);
					} else {
						var renderedTask = tasman.renderTask(respond);
						$('.project[data-id=' + respond.project_id + '] .list-group').append(renderedTask);
						$('#add-task').modal('hide');
					}
				}
			});
		}
	});

	//* rm task init
	$('body').on('click', '.remove-task-init', function() {
		var task = $(this).closest('.task');
		var taskId = task.attr('data-id');
		var taskName = task.find('.task-name').text();
		$('#remove-task .task-name').text(taskName);
		$('#rmTaskId').val(taskId);
	});

	//* rm task
	$('body').on('click', '.remove-task', function() {
		$.ajax({
			url: tasman.apiPath,
			method: "GET",
			dataType: "json",
			data: {
				action: "remove",
				entity: "task",
				id: $('#rmTaskId').val()
			},
			success: function(respond) {
				if(respond.error) {
					helpInfo.text(respond.error);
				} else {
					$('.task[data-id=' + respond.id + ']').remove();
					$('#remove-task').modal('hide');
				}
			}
		});
	});

	//* toggle task
	$('body').on('click', '.status-icon', function() {
		var taskId = $(this).closest('.task').attr('data-id');
		var taskStatus = parseInt($(this).attr('data-status'));
		$.ajax({
			url: tasman.apiPath,
			method: "GET",
			dataType: "json",
			data: {
				action: "set_status",
				entity: "task",
				id: taskId,
				status: taskStatus === 0 ? 1 : 0
			},
			success: function(respond) {
				if(respond.error) {
					//helpInfo.text(respond.error);
				} else {
					var taskRow = $('.task[data-id=' + respond.id + ']');
					if(respond.status == 1) {
						taskRow.removeClass('undone').addClass('done');
						taskRow.find('.status-icon').removeClass('glyphicon-remove').addClass('glyphicon-ok').attr('data-status', 1);
					} else {
						taskRow.addClass('undone').removeClass('done');
						taskRow.find('.status-icon').addClass('glyphicon-remove').removeClass('glyphicon-ok').attr('data-status', 0);
					}
				}
			}
		});
	});

});