angular.module('stgv2')
		.controller('StoMetadatenCtrl', function ($rootScope, $scope, $http, $filter, $stateParams, errorService, successService, StudiensemesterService, AenderungsvarianteService, StudienordnungStatusService, StoreService) {
			$scope.studienordnung_id = $stateParams.studienordnung_id;
			var ctrl = this;
			ctrl.data = "";
			ctrl.beschluesse = [];
			ctrl.changed = false;
			ctrl.status = "";
			ctrl.studiensemesterList = [];
			ctrl.aenderungsvarianteList = [];
			ctrl.studienordnungStatusList = [];
			ctrl.beschlussList = ["Studiengang","Kollegium","AQ Austria"];

			//enable tooltips
			$(document).ready(function(){
				$('[data-toggle="tooltip"]').tooltip();
			});

			if ($stateParams.studienordnung_id !== undefined && $rootScope.studienordnung === null)
			{
				$rootScope.setStudienordnung($stateParams.studienordnung_id);
			}

			$("#editor").wysiwyg(
			{
				'form':
				{
					'text-field': 'editorForm',
					'seperate-binary': false
				}
			});
			$("#editor").on('paste', function(event){
				setTimeout(function(){
					$("#editor").html($("#editor").html());
				},100);
			});

			ctrl.deleteSelection = function()
			{
				window.getSelection().deleteFromDocument();
			};

			$scope.$watch("ctrl.data.aenderungsvariante_kurzbz", function (newValue, oldValue) {
				var length = 0;
				switch (newValue)
				{
					case "gering":
						length = 1;
						break;
					case "nichtGering":
						length = 2;
						break;
					case "akkreditierungspflichtig":
						length = 3;
						break;
					default:
						break;
				}

				length = length - ctrl.beschluesse.length;

				for (var i = 0; i < length; i++)
				{
					ctrl.beschluesse.push({datum: "", typ: ctrl.beschlussList[ctrl.beschluesse.length]});
				}

				if (length < 0)
				{
					for (; length < 0; length++)
					{
						ctrl.beschluesse.pop();
					}
				}

			});

			//loading Studiensemester list
			StudiensemesterService.getStudiensemesterList()
					.then(function (result) {
						ctrl.studiensemesterList = result;

					}, function (error) {
						errorService.setError(getErrorMsg(error));
					});

			//loading AenderungsvarianteList list
			AenderungsvarianteService.getAenderungsvarianteList()
					.then(function (result) {
						ctrl.aenderungsvarianteList = result;
					}, function (error) {
						errorService.setError(getErrorMsg(error));
					});

			$(".datepicker").datepicker({
				dateFormat: "yy-mm-dd",
				firstDay: 1
			});

			$scope.$watch("ctrl.data.status_kurzbz", function (newValue, oldValue) {
				//loading StudienordnungStatus list
				StudienordnungStatusService.getStudienordnungStatusList()
						.then(function (result) {
							ctrl.studienordnungStatusList = result;
							var filtered = $filter('filter')(ctrl.studienordnungStatusList, {status_kurzbz: newValue},true);
							if (filtered.length === 1)
							{
								ctrl.status = filtered[0];
							}
							else
							{
								ctrl.status = {bezeichnung: 'Error: not found with filter'};
							}

						});
			}, true);

			ctrl.loadData = function()
			{
				$http({
					method: 'GET',
					url: './api/studienordnung/metadaten/metadaten.php?studienordnung_id=' + $scope.studienordnung_id
				}).then(function success(response) {
					if (response.data.erfolg)
					{
						ctrl.data = response.data.info;
						$("#editor").html(response.data.info.begruendung);
						angular.forEach(ctrl.data.beschluesse, function(value, index){
							if(value.datum != null)
								ctrl.data.beschluesse[index].datum = formatDateAsString(formatStringToDate(value.datum));
						});
						ctrl.beschluesse = ctrl.data.beschluesse;
					}
					else
					{
						errorService.setError(getErrorMsg(response));
					}
				}, function error(response) {
					errorService.setError(getErrorMsg(response));
				});
			};

			ctrl.save = function () {
				ctrl.data.beschluesse = angular.copy(ctrl.beschluesse);
				ctrl.data.begruendung = JSON.stringify($("#editor").html());
				if ($scope.form.$valid)
				{
					var saveData = {data: ""};
					saveData.data = ctrl.data;
					$http({
						method: 'POST',
						url: './api/studienordnung/metadaten/save_metadaten.php',
						data: $.param(saveData),
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded'
						}
					}).then(function success(response) {
						if (response.data.erfolg)
						{
							$("#treeGrid").treegrid('reload');
							successService.setMessage(response.data.info);
							StoreService.remove("studienordnung");
							$scope.form.$setPristine();
							ctrl.loadData();
						}
						else
						{
							errorService.setError(getErrorMsg(response));
						}
					}, function error(response) {
						errorService.setError(getErrorMsg(response));
					});
				}
			};

			ctrl.loadData();
		});