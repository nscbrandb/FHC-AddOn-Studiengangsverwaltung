$(document).ready(function () {
	$('#west_tree').tree({
		url: "./api/studiengang/studiengang.php",
		method: "get",
		animate: "true",
		dnd: "true",
		loadFilter: function (data)
		{
			if (data.erfolg)
			{
				return data.info;
			}
			else
			{
				//TODO Fehler ausgeben data.message
			}

		},
		onLoadSuccess: function (rootNode, data)
		{
			data.forEach(function (node, v) {
				writeAttributesFromJson(node);
			});
			$('.tree-title').bind('click', function (event) {
				var ele = $(event.target);
				if (ele.attr('node_type') !== undefined)
					angular.element($("#west_tree")).scope().load(ele);
			});

		},
		onClick: function (node)
		{
			return true;
		},
		onBeforeDrop: function (target, source, point)
		{
			toAppend = $.extend(true, {}, source);
			$(this).tree('append', {
				parent: target,
				data: toAppend,
			});
			return false;
		},
		onDragEnter: function (target, source)
		{
			return false;
		}
	});
	//TODO for testing drag n drop
//	$('#test_tree').tree({
//		onDragEnter: function(target,source){
//			  return false;
//		  }
//	});
//	

	
	function writeAttributesFromJson(node) {
		//console.log(node);
		if (node.attributes)
		{
			node.attributes.forEach(function (attr, value)
			{
				$("#" + node.domId + " span").last().attr(attr.name, attr.value);
			});
		}

		if (node.children)
		{
			node.children.forEach(function (node) {
				writeAttributesFromJson(node);
			});
		}
	}
});

var stgv2 = angular.module("stgv2", ['ui.router','ngSanitize'], function($httpProvider){
	 $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
});

angular.module("stgv2")
		.controller("TabsCtrl", function ($scope, $state, $compile, $stateParams, errorService) {
			var ctrl = this;
			ctrl.stoid = "";
	
			$('#mm1').menu({
				onShow:function(){
					$compile($('#mm1').contents())($scope);
				}
			});
	
	
			ctrl.createStudienordnung = function()
			{
				$state.go('studienordnungNeu');
			};
			ctrl.createStudienplan = function()
			{
				var sto = $("#treeGrid").treegrid('getSelected');
				if((sto != null) && (sto.attributes[0].value == "studienordnung"))
				{
					ctrl.stoid = sto.id;
					$state.go('studienplanNeu', {"stoid": ctrl.stoid});
				}
				else
				{
					errorService.setError("Bitte zuerst eine Studienordnung auswählen.", "info");
				}
			};
		})
		.controller("studienordnungTabCtrl", function ($scope) {
			//TODO tabs from config
			$scope.tabs = [
				{label: 'Metadaten', link: '.metadaten'},
				{label: 'Dokumente', link: '.dokumente'},
				{label: 'Eckdaten', link: '.eckdaten'},
				{label: 'Tätigkeitsfelder', link: '.taetigkeitsfelder'},
				{label: 'Qualifikationsziele', link: '.qualifikationsziele'},
				{label: 'ZGV', link: '.zgv'},
				{label: 'Aufnahmeverfahren', link: '.aufnahmeverfahren'}
			];

			$scope.selectedTab = $scope.tabs[0];
			$scope.setSelectedTab = function (tab)
			{
				$scope.selectedTab = tab;
			}

			$scope.getTabClass = function (tab)
			{
				if ($scope.selectedTab == tab)
				{
					return "active";
				}
				else
				{
					return "";
				}
			}
		})
		.controller("studienplanTabCtrl", function ($scope) {
			//TODO tabs from config
			$scope.tabs = [
				{label: 'Metadaten', link: '.metadaten'},
				{label: 'Eckdaten', link: '.eckdaten'},
				{label: 'Gültigkeit', link: '.gueltigkeit'},
				{label: 'Module', link: '.module'},
				{label: 'LVs', link: '.lehrveranstaltungen'},
				{label: 'Auslandssemeser', link: '.auslandssemester'},
				{label: 'Berufspraktikum', link: '.berufspraktikum'},
				{label: 'Studienjahr', link: '.studienjahr'},
				{label: 'Studienprogramm', link: '.studienprogramm'}
			];

			$scope.selectedTab = $scope.tabs[0];
			$scope.setSelectedTab = function (tab)
			{
				$scope.selectedTab = tab;
			}

			$scope.getTabClass = function (tab)
			{
				if ($scope.selectedTab == tab)
				{
					return "active";
				}
				else
				{
					return "";
				}
			}
		})
		.controller("TreeCtrl", function ($scope, $state) {
			$scope.load = function (ele)
			{
				var target = $(ele).attr("node_type");
				var parent = $(ele).parent();
				var node = $('#west_tree').tree("getNode", parent);
				var params = node.attributes[0].urlParams;
				$state.go(target, params[0]);
			};
		}).controller("TreeGridCtrl", function ($scope, $state) {
			$scope.load = function (row)
			{
				var target = row.attributes[0].value
				var params = row.attributes[0].urlParams;
				$state.go(target, params[0]);
			};
		});