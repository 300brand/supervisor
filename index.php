<!DOCTYPE>
<html lang="en" ng-app='supervisorApp'>
<head>
<meta charset="utf-8">
<title>Supervisor Manager</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
<style type="text/css">
/* Move down content because we have a fixed navbar that is 50px tall */
body {
	padding-top: 50px;
}
.main {
	padding: 20px;
}
@media (min-width: 768px) {
	.main {
		padding-right: 40px;
		padding-left: 40px;
	}
}
.main .page-header {
	margin-top: 0;
}
</style>
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.2.14/angular.min.js"></script>
<script>
var supervisorApp = angular.module('supervisorApp', [])

supervisorApp.controller('SupervisorListCtrl', function($scope, $http) {
	$http.get('allStatus.php').success(function(data) {
		$scope.processes = []
		console.debug(data)
		for (s in data) {
			for (p in data[s].processes) {
				console.log("data[%s][%s] = %s", s,p,data[s].processes[p])
				data[s].processes[p].alias = data[s].alias
				$scope.processes.push(data[s].processes[p])
			}
		}
	})
	$scope.isRestartable = function(status) {
		return status == 'RUNNING'
	}
	$scope.isStartable = function(status) {
		return status != 'RUNNING'
	}
})
</script>
</head>
<body ng-controller="SupervisorListCtrl">
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#">Project name</a>
		</div>
		<div class="navbar-collapse collapse">
			<ul class="nav navbar-nav navbar-right">
				<li><a href="#">Dashboard</a></li>
				<li><a href="#">Settings</a></li>
				<li><a href="#">Profile</a></li>
				<li><a href="#">Help</a></li>
			</ul>
			<form class="navbar-form navbar-right">
				<input type="text" class="form-control" placeholder="Search...">
			</form>
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="row">
		<div class="main">
			<h1 class="page-header">Dashboard</h1>
			<h2 class="sub-header">Processes</h2>
			<div class="table-responsive">
				<table class="table table-striped">
					<thead>
						<tr>
							<th>Server</th>
							<th>State</th>
							<th>Description</th>
							<th>Name</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<tr ng-repeat="process in processes">
							<th>{{ process.alias }}</th>
							<td>{{ process.statename }}</td>
							<td>{{ process.description }}</td>
							<td>{{ process.name }}</td>
							<td>
								<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ process.name }}&amp;action=restart" ng-if="isRestartable(process.statename)">Restart</a>
								<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ process.name }}&amp;action=start" ng-if="isStartable(process.statename)">Start</a>
								<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ process.name }}&amp;action=stop" ng-if="isRestartable(process.statename)">Stop</a>
								<a ng-href="/do.php?server={{ supervisor.alias }}&amp;procname={{ process.name }}&amp;action=restart">Clear Log</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

</body>
</html>
