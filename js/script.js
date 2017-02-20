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
});