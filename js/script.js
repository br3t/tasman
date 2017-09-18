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
	tasman.makeTasksSortable();
};

tasman.makeTasksSortable = function() {
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
};

tasman.renderTask = function(task) {
	var compiledTpl = _.template($("#task-template").text());
	return compiledTpl(task);
};

tasman.showAlert = function(text) {
	tasman.alert.show(300).text(text);
};

tasman.hideAlert = function() {
	tasman.alert.hide(300).text("");
};

tasman.reorderTasks = function(taskIds) {
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

	tasman.alert = $('.tasman-alert');
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

	$('[data-wrapper=newTaskDeadline], [data-wrapper=taskNewDeadline]').datetimepicker({
		format: "YYYY-MM-DD"
	});

	//* hide tasman alert
	tasman.alert.on('click', function() {
		tasman.hideAlert();
	});
	$('body').on('click', '.modal, [data-dismiss=modal]', function() {
		tasman.hideAlert();
	});
	$('body').on('change input keydown', '#newProjectName,#newTaskName,#editProjectName', function() {
		tasman.hideAlert();
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

	//* save new project
	$('body').on('click', '.create-project', function() {
		var newProjectNameInput = $("#newProjectName");
		var newProjectName = newProjectNameInput.val();
		if(newProjectName.length <3) {
			tasman.showAlert("Project name should be at least 3 symbols length");
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
						tasman.hideAlert();
					}
				}
			});
		}
	});

	//* rm project init
	$('body').on('click', '.remove-project-init', function() {
		var project = $(this).closest('.project');
		var projectId = project.attr('data-id');
		var projectName = $.trim(project.find('.project-name-value').text());
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
					tasman.showAlert(respond.error);
				} else {
					$('.project[data-id=' + respond.project_id + ']').remove();
					$('#remove-project').modal('hide');
					tasman.hideAlert();
				}
			}
		});
	});

	//* edit project init
	$('body').on('click', '.edit-project-init', function() {
		var project = $(this).closest('.project');
		var projectId = project.attr('data-id');
		$('#editProjectId').val(projectId);
		var projectName = $.trim(project.find('.project-name-value').text());
		$('#editProjectName').val(projectName);
	});

	//* edit project
	$('body').on('click', '.edit-project', function() {
		var projectNewNameInput = $('#editProjectName');
		var projectNewName = $.trim(projectNewNameInput.val());
		if(projectNewName.length < 3) {
			projectNewNameInput.focus();
			tasman.showAlert("Project new name should be at least 3 symbols length");
		} else {
			tasman.hideAlert();
			$.ajax({
				url: tasman.apiPath,
				method: "GET",
				dataType: "json",
				data: {
					action: "edit",
					entity: "project",
					project_id: $('#editProjectId').val(),
					name: projectNewName
				},
				success: function(respond) {
					if(respond.error) {
						tasman.showAlert(respond.error);
					} else {
						$('.project[data-id=' + respond.project_id + ']').find('.project-name-value').text(respond.name);
						$('#edit-project').modal('hide');
						tasman.hideAlert();
					}
				}
			});
		}
		
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
			tasman.showAlert("Task name should be at least 3 symbols length");
			newTaskNameInput.focus();
		} else {
			tasman.hideAlert();
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
						tasman.showAlert(respond.error);
					} else {
						var renderedTask = tasman.renderTask(respond);
						$('.project[data-id=' + respond.project_id + '] .list-group').append(renderedTask);
						$('#add-task').modal('hide');
						tasman.hideAlert();
					}
				}
			});
		}
	});

	//* edit task init
	$('body').on('click', '.edit-task-init', function() {
		$('#edit-task-form').trigger("reset");
		var task = $(this).closest('.task');
		var taskId = task.attr('data-id');
		var taskName = task.find('.task-name').text();
		var taskDeadline = task.find('.deadline').text();
		$('#taskToEditId').val(taskId);
		$('#taskNewName').val(taskName);
		$('#taskNewDeadline').val(taskDeadline);
	});
	//* edit task
	$('body').on('click', '.edit-task', function() {
		var taskNewNameInput = $("#taskNewName");
		var taskNewName = taskNewNameInput.val();
		if(taskNewName.length <3) {
			tasman.showAlert("Task new name should be at least 3 symbols length");
			taskNewNameInput.focus();
		} else {
			tasman.hideAlert();
			$.ajax({
				url: tasman.apiPath,
				method: "GET",
				dataType: "json",
				data: {
					action: "edit",
					entity: "task",
					name: taskNewName,
					id: $('#taskToEditId').val(),
					deadline: $('#taskNewDeadline').val() || 0
				},
				success: function(respond) {
					if(respond.error) {
						tasman.showAlert(respond.error);
					} else {
						var task = $('.task[data-id=' + respond.id + ']');
						task.find('.task-name').text(respond.name);
						if(respond.deadline == '0000-00-00' || respond.deadline == '0') {
							task.find('.deadline').remove();
						} else {
							if(task.find('.deadline').length === 0) {
								task.find('.task-name').after(' <span class="label label-default deadline" title="Deadline">' + respond.deadline + '</span>');
							} else {
								task.find('.deadline').text(respond.deadline);
							}
						}
						$('#edit-task').modal('hide');
						tasman.hideAlert();
					}
				}
			});
		}
	});

	//* rm task init
	$('body').on('click', '.remove-task-init', function() {
		var task = $(this).closest('.task');
		var taskId = task.attr('data-id');
		var taskName = $.trim(task.find('.task-name').text());
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
					tasman.showAlert(respond.error);
				} else {
					$('.task[data-id=' + respond.id + ']').remove();
					$('#remove-task').modal('hide');
					tasman.hideAlert();
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
				status: taskStatus === 1 ? 0 : 1
			},
			success: function(respond) {
				if(respond.error) {
					tasman.showAlert(respond.error);
				} else {
					var taskRow = $('.task[data-id=' + respond.id + ']');
					if(respond.status == 1) {
						taskRow.removeClass('undone').addClass('done');
						taskRow.find('.status-icon').removeClass('glyphicon-remove').addClass('glyphicon-ok').attr('data-status', 1);
					} else {
						taskRow.addClass('undone').removeClass('done');
						taskRow.find('.status-icon').addClass('glyphicon-remove').removeClass('glyphicon-ok').attr('data-status', 0);
					}
					tasman.hideAlert();
				}
			}
		});
	});

});